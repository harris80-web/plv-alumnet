<?php

namespace App\Services\ResumeParser;

/**
 * Masks personal data out of resume lines before they are sent anywhere.
 *
 * The labeller's task is to decide what role a line plays — a job title, a date
 * range, a duty — and none of that needs the person's actual email address or
 * phone number. So those values are replaced with placeholders that preserve the
 * shape of the line while carrying no information about who wrote it.
 *
 * Applies to anything leaving this machine, and to what is kept in the training
 * corpus. It is not applied to the operational parse log, which has to hold the
 * real text for a correction to be traceable, and which is pruned on a retention
 * schedule instead.
 *
 * Ordering matters: emails are masked before phone numbers and bare digits, or a
 * numeric local-part would be mangled first and the address would survive.
 */
class LineRedactor
{
    /** Philippine mobile and landline shapes, plus generic international forms. */
    private const PHONE_PATTERNS = [
        '/(?:\+?63|0)9\d{2}[\s.-]?\d{3}[\s.-]?\d{4}/',
        '/\+\d{1,3}[\s.-]?\(?\d{2,4}\)?[\s.-]?\d{3,4}[\s.-]?\d{3,4}/',
        '/\(?\b0?\d{2,4}\)?[\s.-]\d{3,4}[\s.-]\d{4}\b/',
        '/\b\d{3}-\d{3}-\d{4}\b/',
    ];

    /**
     * Street-address shapes. A house number is optional, because Philippine
     * resumes commonly write "Tongco St. Maysan, Valenzuela City" with none.
     */
    private const ADDRESS_PATTERNS = [
        '/(?:\#?\s*\d{1,5}[A-Za-z]?\s+)?[A-Z][\w.\'-]*(?:\s+[\w.\'-]+){0,3}\s+(?:St\.?|Street|Ave\.?|Avenue|Rd\.?|Road|Blvd\.?|Boulevard|Subd\.?|Subdivision|Phase|Block|Blk\.?|Lot|Purok|Sitio|Compound|Village|Heights)\b[^,\n]*(?:,\s*[A-Z][\w.\'-]*(?:\s+[\w.\'-]+){0,2})*/i',
        '/\b(?:Brgy\.?|Barangay)\s+[\w.\'-]+(?:\s+[\w.\'-]+){0,2}/i',
        '/\b\d{4}\s+(?:Philippines|Metro Manila)\b/i',
    ];

    /**
     * Labelled personal details that Philippine resumes routinely print and that
     * carry no information a line labeller needs. The label is kept so the line
     * still reads as personal information; only the value is removed.
     */
    private const DETAIL_LABELS = 'Birth\s*date|Date\s*of\s*Birth|Birthday|Born|Age|Gender|Sex|'
        . 'Civil\s*Status|Marital\s*Status|Nationality|Citizenship|Religion|Height|Weight|'
        . 'Place\s*of\s*Birth|SSS|TIN|PhilHealth|Pag-?IBIG|Passport|Driver\'?s\s*Licen[cs]e';

    /**
     * Replaces personal data in one line.
     *
     * @param  array<int,string>  $names  extra literals to mask, such as the
     *                                    person's own name, which no pattern can
     *                                    recognise on its own
     */
    public function redact(string $text, array $names = []): string
    {
        $text = preg_replace('/[\w.+-]+@[\w-]+\.[\w.]{2,}/', '[EMAIL]', $text) ?? $text;

        $text = preg_replace(
            '~(?:https?://)?(?:www\.)?(?:linkedin\.com/in/|github\.com/)[\w\-_%./]+~i',
            '[PROFILE_URL]',
            $text
        ) ?? $text;

        foreach (self::PHONE_PATTERNS as $pattern) {
            $text = preg_replace($pattern, '[PHONE]', $text) ?? $text;
        }

        foreach (self::ADDRESS_PATTERNS as $pattern) {
            $text = preg_replace($pattern, '[ADDRESS]', $text) ?? $text;
        }

        // "Birthdate: January 22, 2005" becomes "Birthdate: [DETAIL]".
        $text = preg_replace(
            '/\b(' . self::DETAIL_LABELS . ')\b(\s*[:\-]\s*)[^\n,;|]{1,40}/i',
            '$1$2[DETAIL]',
            $text
        ) ?? $text;

        // The value match stops at a comma, which would otherwise leave the year
        // of a birth date behind.
        $text = preg_replace('/\[DETAIL\],?\s*(?:19|20)\d{2}\b/', '[DETAIL]', $text) ?? $text;

        foreach ($this->sortedByLength($names) as $name) {
            $text = preg_replace(
                '/(?<![\p{L}])' . preg_quote($name, '/') . '(?![\p{L}])/iu',
                '[NAME]',
                $text
            ) ?? $text;
        }

        return trim(preg_replace('/\s{2,}/u', ' ', $text) ?? $text);
    }

    /**
     * @param  array<int,Line>  $lines
     * @param  array<int,string>  $names
     * @return array<int,string>
     */
    public function redactLines(array $lines, array $names = []): array
    {
        $out = [];

        foreach ($lines as $index => $line) {
            $out[$index] = $this->redact($line->text, $names);
        }

        return $out;
    }

    /**
     * A name taken from the document's own text is only masked if it appears no
     * more often than this. Words from the filename are always masked, since
     * those are known to be the person's name.
     */
    private const MAX_NAME_OCCURRENCES = 4;

    /** Ordinary resume vocabulary, never treated as somebody's name. */
    private const VOCABULARY = ['resume', 'resumé', 'curriculum', 'vitae', 'cv', 'profile', 'contact',
        'summary', 'objective', 'experience', 'employment', 'education', 'skills', 'certifications',
        'certificates', 'references', 'personal', 'information', 'details', 'about', 'portfolio',
        'career', 'professional', 'qualifications', 'achievements', 'awards', 'seminars', 'trainings',
        'projects', 'work', 'history', 'and', 'the'];

    /**
     * Name parts to mask.
     *
     * A name cannot be recognised by pattern the way an email can, so three
     * weaker signals are combined and the union is masked. Over-masking is the
     * right bias here: a labeller needs to know that a line *is* a person's name,
     * never what the name is, so removing a word too many costs nothing while
     * leaving one behind defeats the point.
     *
     * @param  array<int,Line>  $lines
     * @param  string|null  $filename  resumes are very often named after the person
     * @return array<int,string>
     */
    public function guessNames(array $lines, ?string $filename = null): array
    {
        $names = [];

        foreach ($this->namesFromFilename($filename) as $word) {
            $names[mb_strtolower($word)] = $word;
        }

        // The name is usually the largest text on the first page. Only the single
        // most prominent line is considered: on a document whose reading order
        // came out shuffled, "the first few lines" are arbitrary content, and
        // masking words from them removes job titles and school names that the
        // labeller needs in order to label anything correctly.
        $largest = 0.0;
        $prominent = null;

        foreach ($lines as $line) {
            if ($line->page === 1 && $line->size > $largest) {
                $largest = $line->size;
                $prominent = $line->text;
            }
        }

        $candidates = $prominent === null ? [] : $this->nameWordsIn($prominent);

        // Text-only extraction carries no font sizes, so there is no prominent
        // line to find and the search above comes back empty. Resumes open with
        // the person's name, so the first few name-shaped lines stand in for it —
        // without this, only the surname taken from the filename was masked and
        // first names went out in full.
        if ($candidates === []) {
            foreach (array_slice($lines, 0, 6) as $line) {
                foreach ($this->nameWordsIn($line->text) as $word) {
                    $candidates[] = $word;
                }
            }
        }

        // Wherever a name already known from the filename appears, the rest of
        // that line is almost certainly the remainder of the same full name.
        // This is what catches a first name on a document that does not open
        // with one, which the positional guesses above would miss entirely.
        if ($names !== []) {
            foreach ($lines as $line) {
                foreach ($names as $known) {
                    if (preg_match('/(?<![\p{L}])' . preg_quote($known, '/') . '(?![\p{L}])/iu', $line->text)) {
                        foreach ($this->nameWordsIn($line->text) as $word) {
                            $candidates[] = $word;
                        }
                        break;
                    }
                }
            }
        }

        // A real name occurs once or twice in a resume. A word that recurs is
        // ordinary vocabulary — "School", "Manager", "Senior" — and masking it
        // would strip meaning out of every line that uses it.
        $document = mb_strtolower(implode("\n", array_map(fn (Line $l) => $l->text, $lines)));

        foreach ($candidates as $word) {
            $occurrences = preg_match_all(
                '/(?<![\p{L}])' . preg_quote(mb_strtolower($word), '/') . '(?![\p{L}])/u',
                $document
            );

            if ($occurrences !== false && $occurrences <= self::MAX_NAME_OCCURRENCES) {
                $names[mb_strtolower($word)] = $word;
            }
        }

        return array_values($names);
    }

    /** @return array<int,string> */
    private function nameWordsIn(string $text): array
    {
        $text = trim($text);

        // A name line is short and carries no digits or contact punctuation.
        if ($text === '' || mb_strlen($text) > 48 || preg_match('/[\d@|•·]/u', $text)) {
            return [];
        }

        $words = preg_split('/[\s,.]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) < 2 || count($words) > 5) {
            return [];
        }

        $out = [];

        foreach ($words as $word) {
            $word = trim($word, " \t.,'\"-");

            if (mb_strlen($word) >= 3 && ! in_array(mb_strtolower($word), self::VOCABULARY, true)) {
                $out[] = $word;
            }
        }

        return $out;
    }

    /** @return array<int,string> */
    private function namesFromFilename(?string $filename): array
    {
        if ($filename === null) {
            return [];
        }

        $base = pathinfo($filename, PATHINFO_FILENAME);
        $words = preg_split('/[^\p{L}]+/u', $base, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $out = [];

        foreach ($words as $word) {
            if (mb_strlen($word) >= 3 && ! in_array(mb_strtolower($word), self::VOCABULARY, true)) {
                $out[] = $word;
            }
        }

        return $out;
    }

    /** Longest first, so a surname is not masked inside a full name it belongs to. */
    private function sortedByLength(array $names): array
    {
        $names = array_values(array_filter($names, fn ($n) => mb_strlen(trim((string) $n)) >= 3));
        usort($names, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        return $names;
    }
}
