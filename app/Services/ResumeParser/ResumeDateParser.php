<?php

namespace App\Services\ResumeParser;

/**
 * Reads date ranges out of resume text.
 *
 * The existing heuristic parser only ever produced a month count, which is why
 * imported experiences lost their actual dates even though the form and the
 * database have had columns for them all along. Emitting real dates also lets
 * the duration be derived rather than guessed.
 */
class ResumeDateParser
{
    private const MONTHS = [
        'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
        'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
    ];

    /** Matches 'present', 'current', 'to date' and friends. */
    private const OPEN_ENDED = '/\b(present|current(?:ly)?|now|ongoing|to\s*date|till\s*date)\b/i';

    /** save() validates duration_months between 0 and 600. */
    private const MAX_MONTHS = 600;

    /**
     * @return array{start_date: ?string, end_date: ?string, is_ongoing: bool, duration_months: ?int}|null
     */
    public function parseRange(string $text): ?array
    {
        $dates = $this->findDates($text);
        $openEnded = (bool) preg_match(self::OPEN_ENDED, $text);

        if ($dates === []) {
            return null;
        }

        $start = $dates[0];
        $end = null;

        if (count($dates) > 1) {
            $end = $dates[1];
        } elseif (! $openEnded) {
            // A lone date with nothing marking it as open-ended is not a range.
            // Treated as a start with an unknown end rather than invented.
            return [
                'start_date' => $this->toDateString($start),
                'end_date' => null,
                'is_ongoing' => false,
                'duration_months' => null,
            ];
        }

        $isOngoing = $end === null && $openEnded;

        if ($end !== null && $this->compare($end, $start) < 0) {
            // save() enforces end_date >= start_date, so a reversed pair would
            // 422 the whole save. Dropping both is the only safe outcome; the
            // alumnus can retype what we could not read.
            return null;
        }

        $until = $end ?? $this->today();

        return [
            'start_date' => $this->toDateString($start),
            'end_date' => $end === null ? null : $this->toDateString($end),
            'is_ongoing' => $isOngoing,
            'duration_months' => $this->monthsBetween($start, $until),
        ];
    }

    /** A single date, for a certification. Year-only becomes January of that year. */
    public function parseSingle(string $text): ?string
    {
        $dates = $this->findDates($text);

        return $dates === [] ? null : $this->toDateString($dates[0]);
    }

    public function monthsBetween(array $from, array $to): int
    {
        $months = (($to['year'] - $from['year']) * 12) + ($to['month'] - $from['month']);

        return max(0, min(self::MAX_MONTHS, $months));
    }

    /**
     * Dates in the order they appear, each as ['year' => int, 'month' => int].
     *
     * @return array<int,array{year:int,month:int,explicit_month:bool}>
     */
    private function findDates(string $text): array
    {
        $month = implode('|', array_keys(self::MONTHS));
        $found = [];

        // Month-name forms first ('Jan 2020', 'September 2020', 'Sept. 2020'),
        // so their year is not also picked up as a bare year below.
        $pattern = '/\b(' . $month . ')[a-z]*\.?\s*,?\s*(?:\d{1,2}\s*,?\s*)?((?:19|20)\d{2})\b/i';
        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        $consumed = [];
        foreach ($matches as $match) {
            $found[$match[0][1]] = [
                'year' => (int) $match[2][0],
                'month' => self::MONTHS[strtolower(substr($match[1][0], 0, 3))],
                'explicit_month' => true,
            ];
            $consumed[] = [$match[0][1], $match[0][1] + strlen($match[0][0])];
        }

        // Numeric forms: 03/2022, 3-2022, 2022/03.
        preg_match_all('~\b(\d{1,2})[/\-]((?:19|20)\d{2})\b~', $text, $numeric, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($numeric as $match) {
            $monthNumber = (int) $match[1][0];
            if ($monthNumber < 1 || $monthNumber > 12 || $this->overlaps($match[0][1], $consumed)) {
                continue;
            }
            $found[$match[0][1]] = [
                'year' => (int) $match[2][0],
                'month' => $monthNumber,
                'explicit_month' => true,
            ];
            $consumed[] = [$match[0][1], $match[0][1] + strlen($match[0][0])];
        }

        // Bare years last, and only where not already part of a fuller date.
        preg_match_all('/\b((?:19|20)\d{2})\b/', $text, $years, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($years as $match) {
            if ($this->overlaps($match[0][1], $consumed)) {
                continue;
            }
            $found[$match[0][1]] = [
                'year' => (int) $match[1][0],
                'month' => 1,
                'explicit_month' => false,
            ];
        }

        ksort($found);

        return array_values($found);
    }

    private function overlaps(int $offset, array $ranges): bool
    {
        foreach ($ranges as [$start, $end]) {
            if ($offset >= $start && $offset < $end) {
                return true;
            }
        }

        return false;
    }

    private function compare(array $a, array $b): int
    {
        return [$a['year'], $a['month']] <=> [$b['year'], $b['month']];
    }

    private function toDateString(array $date): string
    {
        return sprintf('%04d-%02d-01', $date['year'], $date['month']);
    }

    private function today(): array
    {
        return ['year' => (int) date('Y'), 'month' => (int) date('n'), 'explicit_month' => true];
    }
}
