<?php

namespace App\Services\ResumeParser;

/**
 * One visual line of text lifted out of a PDF, with the typographic and
 * positional detail the classifier learns from.
 *
 * A "line" here is not a line of the raw text dump — it is a run of text
 * chunks that share a baseline (same Y, within a tolerance), which is why
 * this carries geometry at all. Recognising a heading by it being larger,
 * bolder and set apart is what lets the parser cope with a layout it has
 * never seen; matching section-title regexes, which is what
 * App\Services\ResumeTextParser does, cannot.
 *
 * A single baseline can hold several logically separate pieces — a job
 * title on the left and its date range flushed right. Those are split into
 * separate Line objects sharing a rowKey, tagged via fragmentIndex /
 * fragmentCount, so the classifier can learn that the right-hand fragment
 * of a two-fragment row is usually the date/company metadata.
 */
class Line
{
    public function __construct(
        public readonly string $text,
        public readonly int $page,
        public readonly float $x,
        public readonly float $y,
        /** Font size already multiplied by the text matrix's vertical scale. */
        public readonly float $size,
        public readonly bool $bold,
        public readonly bool $italic,
        /** Blank vertical space above this line, in the same units as $size. */
        public readonly float $gapAbove,
        /** Groups fragments that came off the same baseline. */
        public readonly int $rowKey,
        public readonly int $fragmentIndex,
        public readonly int $fragmentCount,
    ) {
    }

    public function isRowFragment(): bool
    {
        return $this->fragmentCount > 1;
    }

    /** Persisted on resume_parses.lines and re-featurized from there without re-reading the PDF. */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'page' => $this->page,
            'x' => round($this->x, 2),
            'y' => round($this->y, 2),
            'size' => round($this->size, 2),
            'bold' => $this->bold,
            'italic' => $this->italic,
            'gap_above' => round($this->gapAbove, 2),
            'row_key' => $this->rowKey,
            'fragment_index' => $this->fragmentIndex,
            'fragment_count' => $this->fragmentCount,
        ];
    }

    public static function fromArray(array $a): self
    {
        return new self(
            text: (string) ($a['text'] ?? ''),
            page: (int) ($a['page'] ?? 1),
            x: (float) ($a['x'] ?? 0),
            y: (float) ($a['y'] ?? 0),
            size: (float) ($a['size'] ?? 0),
            bold: (bool) ($a['bold'] ?? false),
            italic: (bool) ($a['italic'] ?? false),
            gapAbove: (float) ($a['gap_above'] ?? 0),
            rowKey: (int) ($a['row_key'] ?? 0),
            fragmentIndex: (int) ($a['fragment_index'] ?? 0),
            fragmentCount: (int) ($a['fragment_count'] ?? 1),
        );
    }
}
