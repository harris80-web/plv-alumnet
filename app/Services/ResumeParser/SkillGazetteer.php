<?php

namespace App\Services\ResumeParser;

use App\Models\Skill;

/**
 * Matches extracted skill text against the skills table.
 *
 * Two things come out of this. Canonical spelling, so an import does not create
 * a near-duplicate row for "git and github". And the real skill_category, which
 * neither existing parser ever supplied — both left the wizard to hardcode
 * every imported skill as "Domain Knowledge".
 */
class SkillGazetteer
{
    /** Only attempt fuzzy matching on reasonably long names. */
    private const FUZZY_MIN_LENGTH = 6;

    private const FUZZY_MAX_DISTANCE = 2;

    /** @var array<string,array{name:string,category:?string}>|null exact lookup, keyed lowercase */
    private ?array $byName = null;

    /** @var array<string,array{name:string,category:?string}>|null keyed by normalized name */
    private ?array $byNormalized = null;

    /** @param array<int,array{name:string,category:?string}>|null $skills injected in tests to avoid the database */
    public function __construct(private ?array $skills = null)
    {
    }

    /**
     * @return array{name: string, category: ?string, matched: bool}
     */
    public function canonicalize(string $raw): array
    {
        $name = trim(preg_replace('/\s+/u', ' ', $raw));

        if ($name === '') {
            return ['name' => '', 'category' => null, 'matched' => false];
        }

        $this->load();

        $exact = $this->byName[mb_strtolower($name)] ?? null;
        if ($exact !== null) {
            return $exact + ['matched' => true];
        }

        $normalizedMatch = $this->byNormalized[$this->normalize($name)] ?? null;
        if ($normalizedMatch !== null) {
            return $normalizedMatch + ['matched' => true];
        }

        $fuzzy = $this->fuzzyMatch($name);
        if ($fuzzy !== null) {
            return $fuzzy + ['matched' => true];
        }

        // Unknown skills are kept, with no category: save()'s firstOrCreate
        // default and the wizard's own fallback both apply 'domain' themselves,
        // and guessing a category here would be worse than either.
        return ['name' => $name, 'category' => null, 'matched' => false];
    }

    /**
     * Known skills appearing inside a longer string, longest match first.
     *
     * PDF extraction regularly glues adjacent skills together ("Laravel
     * JavaScript") when they sit close enough that no gap is detectable, and
     * splitting on punctuation alone cannot recover them.
     *
     * @return array<int,array{name:string,category:?string}>
     */
    public function matchWithin(string $text): array
    {
        $this->load();

        $haystack = ' ' . mb_strtolower(preg_replace('/\s+/u', ' ', $text)) . ' ';
        $matches = [];

        $names = array_keys($this->byName);
        usort($names, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($names as $candidate) {
            $needle = ' ' . $candidate . ' ';
            $position = mb_strpos($haystack, $needle);

            if ($position === false) {
                continue;
            }

            $matches[] = $this->byName[$candidate];
            // Blanked out so a shorter skill cannot match inside ground already
            // claimed by a longer one.
            $haystack = mb_substr($haystack, 0, $position + 1)
                . str_repeat("\x00", mb_strlen($candidate))
                . mb_substr($haystack, $position + 1 + mb_strlen($candidate));
        }

        return $matches;
    }

    private function fuzzyMatch(string $name): ?array
    {
        if (mb_strlen($name) < self::FUZZY_MIN_LENGTH) {
            return null;
        }

        $needle = $this->normalize($name);
        // levenshtein() is byte-based and caps at 255, so a long value is not a
        // fuzzy-match candidate at all.
        if ($needle === '' || strlen($needle) > 200) {
            return null;
        }

        $best = null;
        $bestDistance = PHP_INT_MAX;
        $tied = false;

        foreach ($this->byNormalized as $candidate => $skill) {
            if (abs(strlen($candidate) - strlen($needle)) > self::FUZZY_MAX_DISTANCE) {
                continue;
            }

            $distance = levenshtein($needle, $candidate);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $best = $skill;
                $tied = false;
            } elseif ($distance === $bestDistance) {
                $tied = true;
            }
        }

        // An ambiguous near-match is worse than no match: silently picking one
        // of two equally close skills is how wrong data gets saved.
        if ($best === null || $bestDistance > self::FUZZY_MAX_DISTANCE || $tied) {
            return null;
        }

        return $best;
    }

    /** Strips the differences that are never meaningful: case, punctuation, and 'and' versus '&'. */
    private function normalize(string $name): string
    {
        $value = mb_strtolower($name);
        $value = str_replace('&', ' and ', $value);
        $value = preg_replace('/[^a-z0-9+#]+/u', '', $value);

        return (string) $value;
    }

    private function load(): void
    {
        if ($this->byName !== null) {
            return;
        }

        $rows = $this->skills;

        if ($rows === null) {
            try {
                $rows = Skill::query()
                    ->get(['skill_name', 'skill_category'])
                    ->map(fn ($skill) => [
                        'name' => (string) $skill->skill_name,
                        'category' => $skill->skill_category ? (string) $skill->skill_category : null,
                    ])
                    ->all();
            } catch (\Throwable) {
                $rows = [];
            }
        }

        $this->byName = [];
        $this->byNormalized = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $entry = [
                'name' => $name,
                'category' => isset(Skill::CATEGORIES[$row['category'] ?? '']) ? $row['category'] : null,
            ];

            $this->byName[mb_strtolower($name)] = $entry;
            // First spelling wins, so the seeded canonical form is preferred
            // over a later near-duplicate.
            $this->byNormalized[$this->normalize($name)] ??= $entry;
        }
    }
}
