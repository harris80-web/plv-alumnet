<?php

namespace App\Services\ResumeParser;

use App\Models\Industry;

/**
 * Picks an industry for an experience from its own text.
 *
 * Replaces ResumeTextParser::guessIndustryId(), which matched industry names as
 * bare substrings against a list that includes a literal "None" row — so
 * str_contains('telephone', 'none') held, and any resume mentioning a phone
 * number was filed under industry "None". Matching here is word-bounded and
 * skips that row entirely.
 */
class IndustryMatcher
{
    /** @var array<int,array{id:int,name:string,words:array<int,string>}>|null */
    private ?array $industries = null;

    /** @param array<int,array{id:int,name:string}>|null $industries injected in tests to avoid the database */
    public function __construct(private ?array $seed = null)
    {
    }

    public function match(string $text): ?int
    {
        $this->load();

        if ($this->industries === [] || trim($text) === '') {
            return null;
        }

        $haystack = ' ' . mb_strtolower(preg_replace('/\s+/u', ' ', $text)) . ' ';

        $best = null;
        $bestScore = 0;

        foreach ($this->industries as $industry) {
            $score = 0;

            // Full name first; it is the strongest signal available.
            if ($this->containsPhrase($haystack, mb_strtolower($industry['name']))) {
                $score = 100 + mb_strlen($industry['name']);
            } else {
                // Otherwise count the distinctive words of a compound name, so
                // 'Business & Finance' still matches text mentioning finance.
                foreach ($industry['words'] as $word) {
                    if ($this->containsPhrase($haystack, $word)) {
                        $score += mb_strlen($word);
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $industry['id'];
            }
        }

        return $best;
    }

    private function containsPhrase(string $paddedHaystack, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }

        return (bool) preg_match(
            '/(?<![\p{L}\p{N}])' . preg_quote($needle, '/') . '(?![\p{L}\p{N}])/u',
            $paddedHaystack
        );
    }

    private function load(): void
    {
        if ($this->industries !== null) {
            return;
        }

        $rows = $this->seed;

        if ($rows === null) {
            try {
                $rows = Industry::query()->get(['industry_id', 'industry_name'])
                    ->map(fn ($industry) => [
                        'id' => (int) $industry->industry_id,
                        'name' => (string) $industry->industry_name,
                    ])
                    ->all();
            } catch (\Throwable) {
                $rows = [];
            }
        }

        $this->industries = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));

            // 'None' is a real row meaning "not specified". It is never a match.
            if ($name === '' || mb_strtolower($name) === 'none') {
                continue;
            }

            $words = [];
            foreach (preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($name), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
                // 'and' / 'the' carry no signal and would match nearly any text.
                if (mb_strlen($word) > 3 && ! in_array($word, ['and', 'the', 'for', 'with'], true)) {
                    $words[] = $word;
                }
            }

            $this->industries[] = ['id' => (int) $row['id'], 'name' => $name, 'words' => $words];
        }
    }
}
