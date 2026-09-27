<?php

namespace App\Services\ResumeParser\Training;

use App\Services\ResumeParser\Line;

/**
 * Aligns the strings that were rendered against the lines that came back out of
 * the PDF, so every extracted line gets the label of whatever produced it.
 *
 * The generator knows the label of each rendered string but not of each
 * extracted line, and the two do not correspond one-to-one: a long paragraph
 * wraps across several lines, while two separately rendered cells can come back
 * glued into one. Both directions are handled explicitly, and anything that
 * cannot be accounted for is masked rather than guessed — a wrong label here
 * would be invisible to everything downstream.
 *
 * Because the input text is literally what was rendered, alignment coverage
 * should sit near 1.0. A variant that drops below the threshold is reporting a
 * featurizer bug, which is what makes this class the check on Phase 1.
 */
class ExampleBuilder
{
    /** How far ahead to look when a rendered string appears to have been skipped. */
    private const LOOKAHEAD = 3;

    /** Most rendered cells that can be glued into a single extracted line. */
    private const MAX_GLUE = 3;

    /**
     * @param  array<int,Line>  $lines
     * @param  array<int,array{label:string,text:string}>  $expected
     * @return array{gold_labels: array<int,?string>, coverage: float, unmatched_lines: int, unconsumed_cells: int}
     */
    public function align(array $lines, array $expected): array
    {
        $gold = [];
        $cursor = 0;
        $remaining = $this->tokensAt($expected, $cursor);
        $unmatched = 0;

        foreach ($lines as $index => $line) {
            $lineTokens = $this->tokens($line->text);

            if ($lineTokens === []) {
                $gold[$index] = null;
                continue;
            }

            // The common case: this line is the next slice of the cell being
            // consumed, because the renderer wrapped it.
            if ($remaining !== [] && $this->isPrefix($lineTokens, $remaining)) {
                $gold[$index] = $expected[$cursor]['label'];
                $remaining = array_slice($remaining, count($lineTokens));

                if ($remaining === []) {
                    $cursor++;
                    $remaining = $this->tokensAt($expected, $cursor);
                }

                continue;
            }

            $glued = $this->matchGlued($expected, $cursor, $lineTokens);
            if ($glued !== null) {
                $gold[$index] = $glued['label'];
                $cursor = $glued['next'];
                $remaining = $this->tokensAt($expected, $cursor);

                continue;
            }

            $skipped = $this->matchAfterSkip($expected, $cursor, $lineTokens);
            if ($skipped !== null) {
                $gold[$index] = $skipped['label'];
                $cursor = $skipped['cursor'];
                $remaining = $skipped['remaining'];

                continue;
            }

            // Nothing accounts for this line. Masked, never guessed.
            $gold[$index] = null;
            $unmatched++;
        }

        $labelled = count(array_filter($gold, fn ($label) => $label !== null));

        return [
            'gold_labels' => $gold,
            'coverage' => $lines === [] ? 0.0 : round($labelled / count($lines), 4),
            'unmatched_lines' => $unmatched,
            'unconsumed_cells' => max(0, count($expected) - $cursor),
        ];
    }

    /**
     * Several rendered cells arriving as one extracted line. If they disagree on
     * a label the line is masked: a line that is genuinely half one thing and
     * half another teaches nothing reliable.
     *
     * @return array{label:?string,next:int}|null
     */
    private function matchGlued(array $expected, int $cursor, array $lineTokens): ?array
    {
        for ($span = 2; $span <= self::MAX_GLUE; $span++) {
            if ($cursor + $span > count($expected)) {
                break;
            }

            $slice = array_slice($expected, $cursor, $span);
            $joined = [];
            foreach ($slice as $cell) {
                foreach ($this->tokens($cell['text']) as $token) {
                    $joined[] = $token;
                }
            }

            if ($joined === $lineTokens) {
                $labels = array_unique(array_column($slice, 'label'));

                return ['label' => count($labels) === 1 ? $labels[0] : null, 'next' => $cursor + $span];
            }
        }

        return null;
    }

    /**
     * A rendered cell that never made it into the text at all (an empty chip, a
     * glyph-only prefix) leaves the cursor behind. Looking a little way ahead
     * recovers alignment instead of masking the rest of the document.
     *
     * @return array{label:string,cursor:int,remaining:array<int,string>}|null
     */
    private function matchAfterSkip(array $expected, int $cursor, array $lineTokens): ?array
    {
        for ($ahead = 1; $ahead <= self::LOOKAHEAD; $ahead++) {
            $position = $cursor + $ahead;

            if ($position >= count($expected)) {
                break;
            }

            $candidate = $this->tokens($expected[$position]['text']);

            if ($candidate === [] || ! $this->isPrefix($lineTokens, $candidate)) {
                continue;
            }

            $remaining = array_slice($candidate, count($lineTokens));

            if ($remaining === []) {
                return [
                    'label' => $expected[$position]['label'],
                    'cursor' => $position + 1,
                    'remaining' => $this->tokensAt($expected, $position + 1),
                ];
            }

            return ['label' => $expected[$position]['label'], 'cursor' => $position, 'remaining' => $remaining];
        }

        return null;
    }

    /** @return array<int,string> */
    private function tokensAt(array $expected, int $index): array
    {
        return isset($expected[$index]) ? $this->tokens($expected[$index]['text']) : [];
    }

    private function isPrefix(array $needle, array $haystack): bool
    {
        if ($needle === [] || count($needle) > count($haystack)) {
            return false;
        }

        foreach ($needle as $i => $token) {
            if ($haystack[$i] !== $token) {
                return false;
            }
        }

        return true;
    }

    /**
     * Comparison tokens. Bullet glyphs and punctuation are dropped because the
     * renderer adds them and the extractor may or may not return them.
     *
     * @return array<int,string>
     */
    private function tokens(string $text): array
    {
        $text = mb_strtolower($text);
        $parts = preg_split('/[^\p{L}\p{N}+#]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values($parts);
    }
}
