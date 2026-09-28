<?php

namespace App\Services\ResumeParser;

use App\Models\Industry;
use App\Models\Program;
use App\Models\Skill;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

/**
 * Turns a PDF into ordered Line objects and then into the feature strings the
 * perceptron scores.
 *
 * Why geometry at all: Page::getDataTm() exposes each text chunk's position
 * and font size, so a section heading can be identified by being larger,
 * bolder and set apart from what precedes it. That generalises to layouts
 * never seen in training, which regex section-matching (ResumeTextParser)
 * cannot. When a PDF's text matrices are unusable we fall back to plain text
 * and set the no_layout feature, so the model can learn a distinct decision
 * profile for that degraded regime rather than silently scoring garbage.
 *
 * Features are split into static (a function of the document alone, cached on
 * resume_parser_examples.features) and dynamic (conjunctions with the
 * previous label and the running section, which cannot be cached because they
 * depend on decode-time predictions).
 */
class ResumeLineFeaturizer
{
    /** Bump when feature generation changes; invalidates cached features via configFingerprint(). */
    public const VERSION = 1;

    public const MODE_LAYOUT = 'layout';
    public const MODE_TEXT_ONLY = 'text_only';

    /** Ablation groups for resume:train-parser --exclude-features. */
    public const GROUPS = ['lexical', 'typography', 'position', 'shape', 'history', 'section', 'gazetteer'];

    private const MAX_PAGES = 5;
    private const MAX_CHUNKS = 20000;
    private const MAX_LINES = 500;

    /**
     * getDataTm() gives each chunk's start x but never its width, so width is
     * estimated as length * size * this. Only ever used for relative
     * comparisons (gap detection, gutter crossing), never reported.
     */
    private const CHAR_WIDTH_RATIO = 0.5;

    /** A horizontal gap wider than this * font size starts a new row fragment. */
    private const FRAGMENT_GAP_RATIO = 2.5;

    /** Below this * font size two chunks are glued with no space at all. */
    private const TIGHT_GAP_RATIO = 0.3;

    /**
     * Fraction of a processed page's plain text that positioned chunks must
     * account for before the layout pass is trusted. Below it we fall back to
     * text-only rather than parse a near-empty document.
     */
    private const MIN_LAYOUT_COVERAGE = 0.6;



    private const MONTHS = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

    private const DEGREE_KEYWORDS = ['bachelor', 'master', 'doctor', 'associate', 'diploma', 'undergraduate',
        'bs', 'ba', 'bsit', 'bsba', 'bscs', 'bsed', 'bsa', 'mba', 'ms', 'phd', 'magna', 'cum laude'];

    private const ORG_SUFFIXES = ['inc', 'corp', 'corporation', 'ltd', 'llc', 'company', 'co',
        'university', 'college', 'institute', 'academy', 'foundation', 'center', 'centre',
        'council', 'board', 'commission', 'agency', 'bureau', 'department', 'office', 'school'];

    private const BULLET_GLYPHS = '•▪◦‣·∙*-–—';

    /** @var array<string,array<int,string>>|null lazily loaded, reused across documents while training */
    private static ?array $gazetteers = null;

    /** @param array<int,string> $excludeGroups one or more of self::GROUPS */
    public function __construct(private array $excludeGroups = [])
    {
    }

    public function configFingerprint(): string
    {
        $excluded = array_values(array_intersect(self::GROUPS, $this->excludeGroups));
        sort($excluded);

        return sha1(json_encode(['v' => self::VERSION, 'exclude' => $excluded]));
    }

    private function enabled(string $group): bool
    {
        return ! in_array($group, $this->excludeGroups, true);
    }

    // ───────────────────────────── extraction ─────────────────────────────

    /**
     * @return array{lines: array<int,Line>, mode: string, raw_text: string, page_count: int}
     *
     * @throws \RuntimeException when the PDF cannot be read at all
     */
    public function fromFile(string $path): array
    {
        $rawText = '';
        $pageCount = 0;

        try {
            $config = new Config();
            // Off by default (vendor Config.php); without it getDataTm() rows
            // carry no font id or size and every typography feature is dead.
            $config->setDataTmFontInfoHasToBeIncluded(true);

            $pdf = (new Parser([], $config))->parseFile($path);
            $pages = $pdf->getPages();
            $pageCount = count($pages);
            $rawText = $pdf->getText();

            [$chunks, $comparableChars] = $this->extractChunks($pages);
        } catch (\Throwable $e) {
            // Any layout failure degrades rather than 500s. If getText() also
            // failed there is genuinely nothing to work with.
            if (trim($rawText) === '') {
                throw new \RuntimeException('Unreadable PDF: ' . $e->getMessage(), 0, $e);
            }
            $chunks = [];
            $comparableChars = 0;
        }

        // Some PDFs yield plenty of positioned chunks that between them hold
        // almost no text (text drawn through operators getDataTm() does not
        // read through). Those parse "successfully" into a handful of lines
        // while getText() has the whole document, so sparse coverage has to
        // degrade the same way a hard failure does. Compared per processed
        // page, since layout extraction is page-capped and getText() is not.
        $layoutChars = 0;
        foreach ($chunks as $chunk) {
            $layoutChars += mb_strlen($chunk['text']);
        }

        $tooSparse = $comparableChars > 200 && $layoutChars < $comparableChars * self::MIN_LAYOUT_COVERAGE;

        $lines = $chunks === [] ? [] : $this->buildLines($chunks);

        if ($lines === [] || $tooSparse) {
            return [
                'lines' => $this->linesFromRawText($rawText),
                'mode' => self::MODE_TEXT_ONLY,
                'raw_text' => $rawText,
                'page_count' => $pageCount,
            ];
        }

        return [
            'lines' => $lines,
            'mode' => self::MODE_LAYOUT,
            'raw_text' => $rawText,
            'page_count' => $pageCount,
        ];
    }

    /**
     * @param  array<int,\Smalot\PdfParser\Page>  $pages
     * @return array{0:array<int,array>,1:int} chunks, and the plain-text length
     *                                         of just the pages processed
     */
    private function extractChunks(array $pages): array
    {
        $chunks = [];
        $comparableChars = 0;

        foreach (array_slice($pages, 0, self::MAX_PAGES) as $pageIndex => $page) {
            $pageText = 0;

            try {
                $pageText = mb_strlen($this->collapse($page->getText()));
            } catch (\Throwable) {
                // A page whose text cannot be read contributes no benchmark.
            }

            $comparableChars += $pageText;

            $pageChunks = $this->chunksFromTextMatrices($page->getDataTm(), $page->getFonts(), $pageIndex + 1);

            foreach ($pageChunks as $chunk) {
                if (count($chunks) >= self::MAX_CHUNKS) {
                    return [$chunks, $comparableChars];
                }

                $chunks[] = $chunk;
            }
        }

        return [$chunks, $comparableChars];
    }


    /**
     * @param  array<int,array>  $rows  getDataTm() output
     * @return array<int,array>
     */
    private function chunksFromTextMatrices(array $rows, array $fonts, int $page): array
    {
        $chunks = [];

        foreach ($rows as $row) {
            // Rows are 2 OR 4 elements: the ' and " operators never carry font
            // data, and Tj/TJ only do with the config flag on.
            $matrix = $row[0] ?? null;
            $text = $row[1] ?? '';

            if (! is_array($matrix) || trim((string) $text) === '') {
                continue;
            }

            $verticalScale = (float) ($matrix[3] ?? 1) ?: 1.0;
            $fontSize = isset($row[3]) ? (float) $row[3] : 0.0;

            [$bold, $italic] = $this->fontStyle($fonts, $row[2] ?? null);

            $chunks[] = [
                'text' => (string) $text,
                'page' => $page,
                'x' => (float) ($matrix[4] ?? 0),
                'y' => (float) ($matrix[5] ?? 0),
                // Scaled by the text matrix, so a document drawn in a magnified
                // coordinate space still yields sizes comparable within itself.
                // Absolute because design tools often export a flipped matrix
                // with a negative vertical scale: a negative size would invert
                // every threshold derived from it, and the glue-versus-space
                // test in particular would then split each glyph into its own
                // fragment, turning a word into "w o r d".
                'size' => abs($fontSize * $verticalScale),
                'bold' => $bold,
                'italic' => $italic,
            ];
        }

        return $chunks;
    }


    /** @return array{0:bool,1:bool} */
    private function fontStyle(array $fonts, ?string $fontId): array
    {
        if ($fontId === null) {
            return [false, false];
        }

        // getDataTm() yields '/F2' but getFonts() keys on 'F2'.
        $key = ltrim(trim($fontId), '/');
        $font = $fonts[$key] ?? null;

        if ($font === null) {
            return [false, false];
        }

        // e.g. 'BCDFEE+Calibri-Bold'
        $name = strtolower((string) $font->getName());

        return [
            str_contains($name, 'bold') || str_contains($name, 'black') || str_contains($name, 'heavy'),
            str_contains($name, 'italic') || str_contains($name, 'oblique'),
        ];
    }

    // ───────────────────────────── line assembly ─────────────────────────────

    /** @return array<int,Line> */
    private function buildLines(array $chunks): array
    {
        $sizes = array_values(array_filter(array_column($chunks, 'size'), fn ($s) => $s > 0));
        $medianSize = $this->median($sizes) ?: 10.0;

        // Half a line of leading, bounded so a deck of 54pt titles and a dense
        // 8pt CV both group sanely.
        $tolerance = max(1.5, min(6.0, $medianSize * 0.5));

        $lines = [];
        $rowCounter = 0;

        foreach ($this->groupByPage($chunks) as $page => $pageChunks) {
            $fragments = [];

            foreach ($this->groupIntoRows($pageChunks, $tolerance) as $row) {
                $rowY = $row[0]['y'];
                foreach ($this->splitRowIntoFragments($row) as $fragment) {
                    $fragment['y'] = $rowY;
                    $fragments[] = $fragment;
                }
            }

            if ($fragments === []) {
                continue;
            }

            // Columns are separated at fragment level, never at row level: in a
            // real two-column page a single baseline routinely holds unrelated
            // content from both columns, so a row-level span covers the full
            // width and would hide every gutter.
            foreach ($this->splitIntoColumns($fragments) as $columnFragments) {
                foreach ($this->emitColumn($columnFragments, $page, $rowCounter) as $line) {
                    $lines[] = $line;

                    if (count($lines) >= self::MAX_LINES) {
                        return $lines;
                    }
                }
            }
        }

        return $lines;
    }

    /**
     * Fragments of one column, in reading order, as Line objects. Fragment
     * index/count are recomputed per column so they mean "the nth part of this
     * baseline within this column" — which is the distinction that makes the
     * feature useful for spotting a title/date pair.
     *
     * @return array<int,Line>
     */
    private function emitColumn(array $fragments, int $page, int &$rowCounter): array
    {
        $baselines = [];
        foreach ($fragments as $fragment) {
            $baselines[(string) $fragment['y']][] = $fragment;
        }

        uksort($baselines, fn ($a, $b) => (float) $b <=> (float) $a);

        $lines = [];
        $previousY = null;

        foreach ($baselines as $key => $group) {
            usort($group, fn ($a, $b) => $a['x'] <=> $b['x']);

            $y = (float) $key;
            // Y descends down the page, so the gap above is previous minus current.
            $gapAbove = $previousY === null ? 0.0 : max(0.0, $previousY - $y);
            $rowKey = $rowCounter++;

            foreach ($group as $index => $fragment) {
                $lines[] = new Line(
                    text: $fragment['text'],
                    page: $page,
                    x: $fragment['x'],
                    y: $y,
                    size: $fragment['size'],
                    bold: $fragment['bold'],
                    italic: $fragment['italic'],
                    gapAbove: $gapAbove,
                    rowKey: $rowKey,
                    fragmentIndex: $index,
                    fragmentCount: count($group),
                );
            }

            $previousY = $y;
        }

        return $lines;
    }

    private function groupByPage(array $chunks): array
    {
        $byPage = [];
        foreach ($chunks as $chunk) {
            $byPage[$chunk['page']][] = $chunk;
        }
        ksort($byPage);

        return $byPage;
    }

    /** Chunks sharing a baseline become one row, ordered left to right. */
    private function groupIntoRows(array $chunks, float $tolerance): array
    {
        usort($chunks, fn ($a, $b) => $b['y'] <=> $a['y'] ?: $a['x'] <=> $b['x']);

        $rows = [];
        $current = [];
        $anchorY = null;

        foreach ($chunks as $chunk) {
            if ($anchorY === null || abs($chunk['y'] - $anchorY) <= $tolerance) {
                $anchorY ??= $chunk['y'];
                $current[] = $chunk;
                continue;
            }

            $rows[] = $this->sortRow($current);
            $current = [$chunk];
            $anchorY = $chunk['y'];
        }

        if ($current !== []) {
            $rows[] = $this->sortRow($current);
        }

        return $rows;
    }

    private function sortRow(array $row): array
    {
        usort($row, fn ($a, $b) => $a['x'] <=> $b['x']);

        return $row;
    }

    /**
     * Splits a page's fragments into reading-order columns.
     *
     * A genuine gutter is a vertical band that no fragment crosses, with a real
     * share of the page's fragments and baselines on each side. The hard part
     * is not finding such a band but rejecting the common false positive: a
     * single-column resume with dates flushed right also leaves an empty band.
     * That is why the right-hand group is additionally required to look like
     * prose rather than a column of dates.
     *
     * @return array<int,array<int,array>> one entry per column, left first
     */
    private function splitIntoColumns(array $fragments): array
    {
        if (count($fragments) < 10) {
            return [$fragments];
        }

        $minX = INF;
        $maxX = -INF;
        foreach ($fragments as $fragment) {
            $minX = min($minX, $fragment['x']);
            $maxX = max($maxX, $fragment['end']);
        }

        if (! is_finite($minX) || $maxX - $minX <= 0) {
            return [$fragments];
        }

        $total = count($fragments);
        $best = null;
        $steps = 40;

        for ($step = 1; $step < $steps; $step++) {
            $x = $minX + ($maxX - $minX) * ($step / $steps);
            $left = [];
            $right = [];
            $crossing = false;

            foreach ($fragments as $fragment) {
                if ($fragment['end'] <= $x) {
                    $left[] = $fragment;
                } elseif ($fragment['x'] >= $x) {
                    $right[] = $fragment;
                } else {
                    $crossing = true;
                    break;
                }
            }

            if ($crossing || count($left) / $total < 0.15 || count($right) / $total < 0.15) {
                continue;
            }

            if (count(array_unique(array_column($left, 'y'))) < 3
                || count(array_unique(array_column($right, 'y'))) < 3) {
                continue;
            }

            if ($this->looksLikeDateColumn($right)) {
                continue;
            }

            // A sidebar is the narrower column and sits on the left, so a real
            // gutter falls in the left part of the page and leaves more
            // horizontal room on its right. Without this, a centred name
            // combined with right-aligned dates reads as a gutter: the body
            // text all ends left of it and the name and dates all begin right
            // of it. That reorders an ordinary single-column resume and puts
            // its name two thirds of the way down, which is far more damaging
            // than failing to split a genuine two-column page.
            if ($x > $minX + ($maxX - $minX) * 0.55) {
                continue;
            }

            if ($this->extent($right) <= $this->extent($left)) {
                continue;
            }

            // A real column owns its own section headings. A pseudo-column made
            // of right-aligned dates, or of skill chips that happen to sit past
            // the midpoint, contains none — and chips are the case that defeats
            // every geometric test, because a row of many short fragments
            // leaves no fragment crossing the gap.
            if (! $this->containsHeadingLike($left) || ! $this->containsHeadingLike($right)) {
                continue;
            }

            // Prefer the most balanced split, so a stray narrow band near the
            // margin does not win over the real gutter.
            $balance = abs(0.5 - count($left) / $total);
            if ($best === null || $balance < $best['balance']) {
                $best = ['balance' => $balance, 'left' => $left, 'right' => $right];
            }
        }

        return $best === null ? [$fragments] : [$best['left'], $best['right']];
    }

    /**
     * True when a candidate right-hand column is mostly short date-ish
     * fragments, i.e. a right-aligned date column in a single-column layout
     * rather than a second column of content.
     */
    private function looksLikeDateColumn(array $fragments): bool
    {
        $dateLike = 0;
        $totalLength = 0;

        foreach ($fragments as $fragment) {
            $text = $fragment['text'];
            $totalLength += mb_strlen($text);

            if ($this->hasDateRange($text)
                || preg_match('/^\W*(?:' . $this->monthAlternation() . '|(?:19|20)\d{2})/i', $text)
                || preg_match('/^\W*\d+\s*(?:yrs?|mos?|years?|months?)\b/i', $text)) {
                $dateLike++;
            }
        }

        $count = max(1, count($fragments));

        return $dateLike / $count >= 0.5 || $totalLength / $count < 12;
    }

    /**
     * Whether a group holds at least one fragment that reads like a section
     * heading: short, and set apart by being bold or upper-case. Deliberately
     * typographic rather than keyword-based, so it holds for headings this code
     * has never seen.
     */
    private function containsHeadingLike(array $fragments): bool
    {
        foreach ($fragments as $fragment) {
            $text = trim($fragment['text']);

            if (mb_strlen($text) < 3) {
                continue;
            }

            $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            if (count($words) > 6) {
                continue;
            }

            $letters = preg_replace('/[^\p{L}]/u', '', $text);

            if ($letters === '') {
                continue;
            }

            $isUpper = mb_strtoupper($letters, 'UTF-8') === $letters && mb_strlen($letters) > 1;

            if (($fragment['bold'] ?? false) || $isUpper) {
                return true;
            }
        }

        return false;
    }


    /** Horizontal span covered by a group of fragments. */
    private function extent(array $fragments): float
    {
        if ($fragments === []) {
            return 0.0;
        }

        return max(array_column($fragments, 'end')) - min(array_column($fragments, 'x'));
    }

    private function estimateWidth(array $chunk): float
    {
        $size = $chunk['size'] > 0 ? $chunk['size'] : 10.0;
        $text = $chunk['text'];
        $units = 0.0;

        // A flat per-character ratio mis-sizes lowercase-heavy and all-caps
        // text in opposite directions, which shows up directly as wrong
        // fragment splits. Per-class widths are still an approximation but a
        // much closer one, and cost nothing.
        for ($i = 0, $length = mb_strlen($text); $i < $length; $i++) {
            $char = mb_substr($text, $i, 1);
            $units += match (true) {
                $char === ' ' => 0.26,
                str_contains('iljtfrI.,;:\'"|!()[]{}', $char) => 0.28,
                str_contains('mwMW@', $char) => 0.85,
                preg_match('/[\p{Lu}\p{N}]/u', $char) === 1 => 0.60,
                default => 0.50,
            };
        }

        $width = $units * $size;

        return $width > 0 ? $width : mb_strlen($text) * $size * self::CHAR_WIDTH_RATIO;
    }

    /**
     * Splits one baseline where a wide horizontal gap separates logically
     * distinct content — "Junior Developer        Jan 2020 - Mar 2022". This is
     * what replaces ResumeTextParser::stripTrailingDuration()'s guesswork.
     */
    private function splitRowIntoFragments(array $row): array
    {
        $fragments = [];
        $buffer = null;
        $cursorX = null;

        foreach ($row as $chunk) {
            $size = $chunk['size'] > 0 ? $chunk['size'] : 10.0;

            if ($buffer === null) {
                $buffer = $this->newFragment($chunk);
                $cursorX = $chunk['x'] + $this->estimateWidth($chunk);
                $buffer['end'] = $cursorX;
                continue;
            }

            $gap = $chunk['x'] - $cursorX;

            if ($gap > $size * self::FRAGMENT_GAP_RATIO) {
                $fragments[] = $buffer;
                $buffer = $this->newFragment($chunk);
            } else {
                $separator = $gap < $size * self::TIGHT_GAP_RATIO ? '' : ' ';
                $buffer['text'] .= $separator . $chunk['text'];
                $buffer['size'] = max($buffer['size'], $chunk['size']);
                $buffer['bold'] = $buffer['bold'] || $chunk['bold'];
                $buffer['italic'] = $buffer['italic'] || $chunk['italic'];
            }

            $cursorX = $chunk['x'] + $this->estimateWidth($chunk);
            $buffer['end'] = $cursorX;
        }

        if ($buffer !== null) {
            $fragments[] = $buffer;
        }

        foreach ($fragments as $i => $fragment) {
            $fragments[$i]['text'] = $this->collapse($fragment['text']);
        }

        return array_values(array_filter($fragments, fn ($f) => $f['text'] !== ''));
    }

    private function newFragment(array $chunk): array
    {
        return [
            'text' => $chunk['text'],
            'x' => $chunk['x'],
            'end' => $chunk['x'],
            'size' => $chunk['size'],
            'bold' => $chunk['bold'],
            'italic' => $chunk['italic'],
        ];
    }

    /** @return array<int,Line> */
    public function linesFromRawText(string $rawText): array
    {
        $lines = [];
        $y = 1000.0;

        foreach (preg_split('/\R/u', str_replace(["\r\n", "\r"], "\n", $rawText)) as $raw) {
            $text = $this->collapse($raw);
            if ($text === '') {
                continue;
            }

            $lines[] = new Line(
                text: $text,
                page: 1,
                x: 0.0,
                y: $y,
                size: 0.0,
                bold: false,
                italic: false,
                gapAbove: 0.0,
                rowKey: count($lines),
                fragmentIndex: 0,
                fragmentCount: 1,
            );

            $y -= 12.0;

            if (count($lines) >= self::MAX_LINES) {
                break;
            }
        }

        return $lines;
    }

    private function collapse(string $text): string
    {
        // preg_replace returns null if the subject is not valid UTF-8, which
        // happens with oddly encoded PDF text; fall back to the input rather
        // than passing null on to trim().
        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    private function median(array $values): float
    {
        if ($values === []) {
            return 0.0;
        }

        sort($values);
        $mid = intdiv(count($values), 2);

        return count($values) % 2 ? (float) $values[$mid] : ((float) $values[$mid - 1] + (float) $values[$mid]) / 2;
    }

    // ───────────────────────────── features ─────────────────────────────

    /**
     * Features that depend only on the document, so they can be cached.
     *
     * @param  array<int,Line>  $lines
     * @return array<int,array<int,string>>
     */
    public function staticFeatures(array $lines, string $mode): array
    {
        $sizes = array_values(array_filter(array_map(fn (Line $l) => $l->size, $lines), fn ($s) => $s > 0));
        $medianSize = $this->median($sizes);
        $total = max(1, count($lines));

        $out = [];

        foreach ($lines as $i => $line) {
            $features = ['bias'];

            if ($mode === self::MODE_TEXT_ONLY) {
                $features[] = 'no_layout';
            }

            $tokens = $this->tokens($line->text);

            if ($this->enabled('lexical')) {
                foreach (array_unique($tokens) as $token) {
                    $features[] = 'uni:' . $token;
                }
                // Restricted to the edges: full bigrams over a 40-token bullet
                // would flood the vocabulary with features seen once.
                foreach ($this->edgeBigrams($tokens) as $bigram) {
                    $features[] = 'bg:' . $bigram;
                }
                if ($tokens !== []) {
                    $features[] = 'first:' . $tokens[0];
                    $features[] = 'last:' . $tokens[count($tokens) - 1];
                }
            }

            if ($this->enabled('typography')) {
                if ($line->bold) {
                    $features[] = 'bold';
                }
                if ($line->italic) {
                    $features[] = 'italic';
                }
                if ($line->bold && $tokens !== []) {
                    $features[] = 'bold|first:' . $tokens[0];
                }
                // Relative, never absolute: a 54pt slide title and an 11pt
                // heading must produce the same feature.
                $features[] = 'sz:' . $this->sizeRank($line->size, $medianSize);
            }

            if ($this->enabled('position')) {
                $features[] = 'x:' . $this->bucket($line->x, [5, 40, 80, 140, 220, 320]);
                $features[] = 'gap:' . $this->gapBucket($line->gapAbove, $medianSize ?: 10.0);
                $features[] = 'pg:' . (int) floor(($i / $total) * 10);
                if ($i < 3) {
                    $features[] = 'line_is_' . $i;
                }
                if ($line->isRowFragment()) {
                    $features[] = 'isfrag';
                    $features[] = 'frag:' . $line->fragmentIndex . '/' . $line->fragmentCount;
                }
            }

            if ($this->enabled('shape')) {
                foreach ($this->shapeFeatures($line, $tokens) as $feature) {
                    $features[] = $feature;
                }
            }

            if ($this->enabled('gazetteer')) {
                foreach ($this->gazetteerFeatures($line->text, $tokens) as $feature) {
                    $features[] = $feature;
                }
            }

            $out[$i] = array_values(array_unique($features));
        }

        return $out;
    }

    /**
     * Conjunctions with decode-time state. Plain prev_label barely sequences
     * anything; these conjunctions are where the sequence model earns its name.
     *
     * @param  array<int,Line>  $lines
     * @return array<int,string>
     */
    public function dynamicFeatures(array $lines, int $index, ?string $previousLabel, ?string $section): array
    {
        $line = $lines[$index] ?? null;
        if ($line === null) {
            return [];
        }

        $features = [];
        $tokens = $this->tokens($line->text);
        $hasDate = $this->hasDateRange($line->text);
        $bulleted = $this->startsWithBullet($line->text);
        $indented = $line->x > 40;

        if ($this->enabled('history')) {
            $previous = $previousLabel ?? '<start>';
            $features[] = 'prev:' . $previous;
            if ($line->bold) {
                $features[] = 'prev:' . $previous . '|bold';
            }
            if ($tokens !== []) {
                $features[] = 'prev:' . $previous . '|first:' . $tokens[0];
            }
            if ($hasDate) {
                $features[] = 'prev:' . $previous . '|date';
            }
            if ($indented) {
                $features[] = 'prev:' . $previous . '|indent';
            }
        }

        if ($this->enabled('section')) {
            $current = $section ?? '<none>';
            $features[] = 'sect:' . $current;
            if ($line->bold) {
                $features[] = 'sect:' . $current . '|bold';
            }
            if ($bulleted) {
                $features[] = 'sect:' . $current . '|bullet';
            }
            if ($hasDate) {
                $features[] = 'sect:' . $current . '|date';
            }
        }

        return $features;
    }

    private function sizeRank(float $size, float $medianSize): string
    {
        if ($size <= 0 || $medianSize <= 0) {
            return 'na';
        }

        $ratio = $size / $medianSize;

        return match (true) {
            $ratio >= 1.6 => 'xxl',
            $ratio >= 1.3 => 'xl',
            $ratio >= 1.1 => 'l',
            $ratio >= 0.95 => 'm',
            $ratio >= 0.85 => 's',
            default => 'xs',
        };
    }

    private function gapBucket(float $gap, float $medianSize): string
    {
        if ($gap <= 0) {
            return '0';
        }

        $ratio = $gap / max(1.0, $medianSize);

        return match (true) {
            $ratio >= 3.0 => 'huge',
            $ratio >= 2.0 => 'big',
            $ratio >= 1.4 => 'med',
            default => 'tight',
        };
    }

    private function bucket(float $value, array $edges): string
    {
        foreach ($edges as $i => $edge) {
            if ($value < $edge) {
                return (string) $i;
            }
        }

        return (string) count($edges);
    }

    /** @return array<int,string> */
    private function shapeFeatures(Line $line, array $tokens): array
    {
        $text = $line->text;
        $features = [];

        $letters = preg_replace('/[^\p{L}]/u', '', $text);
        if ($letters !== '' && mb_strtoupper($letters, 'UTF-8') === $letters && mb_strlen($letters) > 1) {
            $features[] = 'caps';
        }

        $features[] = 'ntok:' . $this->bucket(count($tokens), [1, 2, 4, 7, 12, 20]);
        $features[] = 'len:' . $this->bucket(mb_strlen($text), [10, 20, 40, 70, 120, 200]);

        if ($this->startsWithBullet($text)) {
            $features[] = 'bullet';
        }
        if (str_ends_with(rtrim($text), ':')) {
            $features[] = 'colon';
        }
        if (preg_match('/\b(19|20)\d{2}\b/', $text)) {
            $features[] = 'year4';
        }
        if ($this->hasDateRange($text)) {
            $features[] = 'daterange';
        }
        if (preg_match('/[\w.+-]+@[\w-]+\.[\w.]+/', $text)) {
            $features[] = 'email';
        }
        if (preg_match('~(https?://|www\.|linkedin\.com|github\.com)~i', $text)) {
            $features[] = 'url';
        }
        if (preg_match('/(\+?63|0)9\d{2}[\s-]?\d{3}[\s-]?\d{4}/', $text) || preg_match('/\(\d{2,4}\)\s*\d{3,4}[\s-]?\d{4}/', $text)) {
            $features[] = 'phone';
        }
        if (preg_match('/^' . $this->monthAlternation() . '/i', $text)) {
            $features[] = 'starts_month';
        }

        $features[] = 'commas:' . $this->bucket(substr_count($text, ','), [1, 2, 4, 7]);

        $punctuation = preg_match_all('/[^\p{L}\p{N}\s]/u', $text);
        $features[] = 'punct:' . $this->bucket(mb_strlen($text) > 0 ? $punctuation / mb_strlen($text) * 100 : 0, [3, 8, 15, 30]);

        $capitalized = 0;
        foreach ($tokens as $token) {
            if (preg_match('/^\p{Lu}/u', $token)) {
                $capitalized++;
            }
        }
        $features[] = 'capratio:' . $this->bucket($tokens === [] ? 0 : $capitalized / count($tokens) * 100, [20, 50, 80, 99]);

        $features[] = 'shape:' . $this->shapeSignature($text);

        return $features;
    }

    private function shapeSignature(string $text): string
    {
        $signature = preg_replace(['/\p{Lu}/u', '/\p{Ll}/u', '/\p{N}/u'], ['X', 'x', 'd'], mb_substr($text, 0, 12));
        $signature = preg_replace('/[^Xxd]/u', '_', $signature);

        return preg_replace('/(.)\1{2,}/u', '$1$1', $signature);
    }

    private function startsWithBullet(string $text): bool
    {
        $first = mb_substr(ltrim($text), 0, 1);

        return $first !== '' && mb_strpos(self::BULLET_GLYPHS, $first) !== false;
    }

    private function monthAlternation(): string
    {
        return '(?:' . implode('|', self::MONTHS) . ')[a-z]*\.?';
    }

    private function hasDateRange(string $text): bool
    {
        $month = $this->monthAlternation();
        $dateish = '(?:(?:19|20)\d{2}|' . $month . '(?:\s*\d{1,2})?(?:,?\s*(?:19|20)\d{2})?|\d{1,2}\/(?:19|20)?\d{2})';
        $openEnded = '(?:present|current|now|ongoing|to\s*date)';

        return (bool) preg_match(
            '/' . $dateish . '\s*(?:[-–—]|to|until|through)\s*(?:' . $dateish . '|' . $openEnded . ')/i',
            $text
        );
    }

    /** @return array<int,string> */
    private function gazetteerFeatures(string $text, array $tokens): array
    {
        $gazetteers = $this->gazetteers();
        $haystack = ' ' . mb_strtolower($text) . ' ';
        $features = [];

        $skillHits = 0;
        foreach ($gazetteers['skills'] as $skill) {
            if ($this->containsPhrase($haystack, $skill)) {
                $skillHits++;
            }
        }
        if ($skillHits > 0) {
            $features[] = 'gazskill:' . $this->bucket($skillHits, [1, 2, 4, 7]);
            $features[] = 'gazskillratio:' . $this->bucket(
                $tokens === [] ? 0 : $skillHits / count($tokens) * 100,
                [10, 25, 50]
            );
        }

        foreach (['industries' => 'hasindustry', 'programs' => 'hasprogram'] as $key => $feature) {
            foreach ($gazetteers[$key] as $phrase) {
                if ($this->containsPhrase($haystack, $phrase)) {
                    $features[] = $feature;
                    break;
                }
            }
        }

        if (preg_match('/\b' . $this->monthAlternation() . '\b/i', $text)) {
            $features[] = 'hasmonth';
        }

        foreach (self::DEGREE_KEYWORDS as $keyword) {
            if ($this->containsPhrase($haystack, $keyword)) {
                $features[] = 'hasdegree';
                break;
            }
        }

        foreach (self::ORG_SUFFIXES as $suffix) {
            if ($this->containsPhrase($haystack, $suffix)) {
                $features[] = 'hasorg';
                break;
            }
        }

        return $features;
    }

    /** Word-boundary containment, so 'none' never matches inside 'telephone'. */
    private function containsPhrase(string $paddedHaystack, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }

        return str_contains($paddedHaystack, ' ' . $needle . ' ')
            || (bool) preg_match('/(?<![\p{L}\p{N}])' . preg_quote($needle, '/') . '(?![\p{L}\p{N}])/u', $paddedHaystack);
    }

    /** @return array<string,array<int,string>> */
    private function gazetteers(): array
    {
        if (self::$gazetteers !== null) {
            return self::$gazetteers;
        }

        $normalize = static function ($values): array {
            $out = [];
            foreach ($values as $value) {
                $value = mb_strtolower(trim((string) $value));
                // 'None' is a real industries row and matching it as a
                // substring is what makes ResumeTextParser tag any resume
                // mentioning a telephone with industry 'None'.
                if ($value === '' || $value === 'none' || mb_strlen($value) < 3) {
                    continue;
                }
                $out[] = $value;
            }

            return array_values(array_unique($out));
        };

        try {
            self::$gazetteers = [
                'skills' => $normalize(Skill::pluck('skill_name')->all()),
                'industries' => $normalize(Industry::pluck('industry_name')->all()),
                'programs' => $normalize(Program::pluck('program_name')->all()),
            ];
        } catch (\Throwable) {
            // Featurizing must work without a database (unit tests, tinkering).
            self::$gazetteers = ['skills' => [], 'industries' => [], 'programs' => []];
        }

        return self::$gazetteers;
    }

    /** Test seam so a unit test need not touch the database. */
    public static function setGazetteers(?array $gazetteers): void
    {
        self::$gazetteers = $gazetteers;
    }

    /** @return array<int,string> */
    private function tokens(string $text): array
    {
        $text = mb_strtolower($text);
        $parts = preg_split('/[^\p{L}\p{N}+#.]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = [];
        foreach ($parts as $part) {
            $part = trim($part, '.');
            if ($part === '') {
                continue;
            }
            // Numbers are collapsed so every year is one feature, not 80.
            if (preg_match('/^\d{4}$/', $part)) {
                $part = '<year>';
            } elseif (preg_match('/^\d+$/', $part)) {
                $part = '<num>';
            }
            $tokens[] = $part;
        }

        return $tokens;
    }

    /** @return array<int,string> */
    private function edgeBigrams(array $tokens): array
    {
        $bigrams = [];
        $count = count($tokens);

        for ($i = 0; $i < min(3, max(0, $count - 1)); $i++) {
            $bigrams[] = 'L' . $i . ':' . $tokens[$i] . '_' . $tokens[$i + 1];
        }

        for ($i = 0; $i < min(3, max(0, $count - 1)); $i++) {
            $left = $count - 2 - $i;
            if ($left < 3) {
                break;
            }
            $bigrams[] = 'R' . $i . ':' . $tokens[$left] . '_' . $tokens[$left + 1];
        }

        return array_values(array_unique($bigrams));
    }
}
