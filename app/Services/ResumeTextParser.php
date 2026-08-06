<?php

namespace App\Services;

use App\Models\Industry;

/**
 * Best-effort heuristic extraction from raw PDF text into the same shape
 * Alumnus::toResumeFormArray() produces, so the wizard can prefill itself
 * from an import exactly like it prefills from saved data.
 *
 * This is intentionally not a "real" resume parser (no ML, no layout
 * awareness) — it scans for common section headers and splits blank-line
 * blocks within each. It will misfire on heavily designed or multi-column
 * resumes. That's an accepted trade-off: the alumnus reviews and edits
 * every field in the wizard before anything is saved, so an imperfect
 * first pass still saves them from typing everything from scratch.
 */
class ResumeTextParser
{
    private const SECTION_PATTERNS = [
        'summary' => '/^(professional\s+)?(summary|objective|profile|about\s*me)\b/i',
        'skills' => '/^(technical\s+|core\s+|key\s+)?skills?\b/i',
        'projects' => '/^projects?\b/i',
        'experience' => '/^(work\s+|professional\s+)?(experience|employment(\s+history)?)\b/i',
        'trainings' => '/^trainings?\b/i',
        'seminars' => '/^seminars?\b/i',
        'certifications' => '/^(certifications?|licenses?)\b/i',
        'education' => '/^education\b/i',
    ];

    private const CERT_TYPE_BY_SECTION = [
        'certifications' => 'certification',
        'seminars' => 'seminar',
        'trainings' => 'training',
    ];

    /** @var array<int,string>|null cached industry_id => industry_name */
    private ?array $industryNames = null;

    public function parse(string $rawText): array
    {
        $lines = $this->normalizeLines($rawText);
        $sections = $this->splitIntoSections($lines);

        $certifications = [];
        foreach (self::CERT_TYPE_BY_SECTION as $sectionKey => $certType) {
            $certifications = array_merge(
                $certifications,
                $this->extractCertifications($sections[$sectionKey] ?? [], $certType)
            );
        }

        return [
            'resume_summary' => $this->extractSummary($sections['summary'] ?? []),
            'linkedin_url' => $this->extractLinkedin($rawText),
            'skills' => $this->extractSkills($sections['skills'] ?? []),
            'experiences' => array_slice(array_merge(
                $this->extractExperienceBlocks($sections['experience'] ?? [], 'work'),
                $this->extractExperienceBlocks($sections['projects'] ?? [], 'project')
            ), 0, 15),
            'certifications' => array_slice($certifications, 0, 15),
        ];
    }

    private function normalizeLines(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return array_map(
            fn ($line) => trim(preg_replace('/\s+/', ' ', $line)),
            explode("\n", $text)
        );
    }

    private function matchSectionHeader(string $line): ?string
    {
        // Section headers are short lines — this also keeps a random
        // sentence that happens to contain "experience" from matching.
        if ($line === '' || mb_strlen($line) > 45) {
            return null;
        }

        foreach (self::SECTION_PATTERNS as $key => $pattern) {
            if (preg_match($pattern, $line)) {
                return $key;
            }
        }

        return null;
    }

    private function splitIntoSections(array $lines): array
    {
        $sections = [];
        $current = null;

        foreach ($lines as $line) {
            $matched = $this->matchSectionHeader($line);
            if ($matched) {
                $current = $matched;
                $sections[$current] ??= [];
                continue;
            }
            if ($current !== null) {
                $sections[$current][] = $line;
            }
        }

        return $sections;
    }

    /** Blank lines are the only block separator — simple and predictable. */
    private function splitIntoBlocks(array $lines): array
    {
        $blocks = [];
        $current = [];

        foreach ($lines as $line) {
            if ($line === '') {
                if (!empty($current)) {
                    $blocks[] = $current;
                    $current = [];
                }
                continue;
            }
            $current[] = $line;
        }
        if (!empty($current)) {
            $blocks[] = $current;
        }

        return $blocks;
    }

    private function extractSummary(array $lines): ?string
    {
        $text = trim(implode(' ', array_filter($lines, fn ($l) => $l !== '')));

        return $text !== '' ? mb_substr($text, 0, 500) : null;
    }

    private function extractLinkedin(string $text): ?string
    {
        if (!preg_match('~(https?://)?(www\.)?linkedin\.com/in/[A-Za-z0-9\-_%]+~i', $text, $m)) {
            return null;
        }

        return preg_match('~^https?://~i', $m[0]) ? $m[0] : 'https://' . $m[0];
    }

    private function extractSkills(array $lines): array
    {
        $joined = implode(', ', array_filter($lines, fn ($l) => $l !== ''));
        $tokens = preg_split('/[,•|\/;]|\s{2,}/u', $joined) ?: [];

        $skills = [];
        foreach ($tokens as $token) {
            $token = trim($token, " \t\n\r\0\x0B-·•");
            if ($token === '' || mb_strlen($token) < 2 || mb_strlen($token) > 40) {
                continue;
            }
            $skills[mb_strtolower($token)] = $token;
        }

        return array_map(
            fn ($name) => ['name' => $name],
            array_slice(array_values($skills), 0, 30)
        );
    }

    private function extractExperienceBlocks(array $lines, string $type): array
    {
        $results = [];

        foreach ($this->splitIntoBlocks($lines) as $block) {
            $titleLine = $block[0] ?? null;
            if (!$titleLine) {
                continue;
            }

            // A title and its date range are usually laid out side by side
            // (ours included — see resume-pdf.blade.php's ".row"), and PDF
            // text extraction often runs same-line content together with no
            // separator, e.g. "Junior Developer 6 mos". Strip that off the
            // title before it's mistaken for part of the job title.
            [$title, $titleDuration] = $this->stripTrailingDuration($titleLine);

            $description = trim(implode(' ', array_slice($block, 1)));
            $blockText = implode(' ', $block);

            $results[] = [
                'type' => $type,
                'job_title' => mb_substr($title, 0, 150),
                'job_description' => $description !== '' ? mb_substr($description, 0, 2000) : null,
                'duration_months' => $titleDuration ?: $this->extractDurationMonths($blockText),
                'industry_id' => $type === 'work' ? $this->guessIndustryId($blockText) : null,
            ];
        }

        return $results;
    }

    /**
     * Splits a trailing duration off a title line and converts it to months.
     * Recognizes our own on-screen/PDF format ("6 mos", "1 yr 2 mos") as well
     * as a plain trailing year range ("2020 - 2022", "2020 - Present"), since
     * either can end up glued onto the title by PDF text extraction.
     *
     * @return array{0: string, 1: int}
     */
    private function stripTrailingDuration(string $line): array
    {
        if (preg_match('/^(.*\S)\s+(\d+\s*yrs?\s*\d+\s*mos?|\d+\s*yrs?|\d+\s*mos?)$/i', $line, $m)) {
            $years = $months = 0;
            preg_match('/(\d+)\s*yrs?/i', $m[2], $ym) && $years = (int) $ym[1];
            preg_match('/(\d+)\s*mos?/i', $m[2], $mm) && $months = (int) $mm[1];

            return [trim($m[1]), min(600, $years * 12 + $months)];
        }

        if (preg_match('/^(.*\S)\s+((?:19|20)\d{2})\s*[-–—]\s*((?:19|20)\d{2}|present|current|now)$/i', $line, $m)) {
            $startYear = (int) $m[2];
            $endYear = in_array(mb_strtolower($m[3]), ['present', 'current', 'now'], true) ? (int) date('Y') : (int) $m[3];

            return $endYear >= $startYear
                ? [trim($m[1]), max(0, min(600, ($endYear - $startYear) * 12))]
                : [$line, 0];
        }

        return [$line, 0];
    }

    private function extractCertifications(array $lines, string $certType): array
    {
        $results = [];

        foreach ($this->splitIntoBlocks($lines) as $block) {
            $nameLine = $block[0] ?? null;
            if (!$nameLine) {
                continue;
            }

            // Strip a trailing "(Type)  <date>" the PDF/on-screen view renders
            // inline (see resume-pdf.blade.php), then split "Name — Issuer"
            // if that's on the same line — both are things our own export
            // can hand right back to us on re-import.
            $nameLine = preg_replace('/\s*\((?:Certification|Seminar|Training)\)\s*.*$/i', '', $nameLine);

            $name = $nameLine;
            $from = $block[1] ?? null;
            if (str_contains($nameLine, '—')) {
                [$name, $fromInline] = array_map('trim', explode('—', $nameLine, 2));
                $from = $from ?: $fromInline;
            }

            // Don't let a bare date line become the "issuing organization".
            if ($from && preg_match('/^(19|20)\d{2}$|present|current/i', $from)) {
                $from = null;
            }

            $results[] = [
                'certification_type' => $certType,
                'certification_name' => mb_substr(trim($name), 0, 150),
                'certification_from' => $from ? mb_substr(trim($from), 0, 150) : null,
                'certification_date' => $this->extractYearAsDate(implode(' ', $block)),
            ];
        }

        return $results;
    }

    /**
     * Recognizes "2020 - 2022", "2020 to Present", "Jan 2020 - Mar 2022".
     * Approximate on purpose — the alumnus can correct it in the wizard.
     */
    private function extractDurationMonths(string $text): int
    {
        if (!preg_match('/\b(19|20)\d{2}\b.{0,15}?(present|current|now|\b(19|20)\d{2}\b)/i', $text, $m)) {
            return 0;
        }

        preg_match('/\b((19|20)\d{2})\b/', $m[0], $startMatch);
        $startYear = (int) ($startMatch[1] ?? 0);
        $endToken = mb_strtolower($m[2]);
        $endYear = in_array($endToken, ['present', 'current', 'now'], true)
            ? (int) date('Y')
            : (int) $endToken;

        if (!$startYear || !$endYear || $endYear < $startYear) {
            return 0;
        }

        return max(0, min(600, ($endYear - $startYear) * 12));
    }

    private function extractYearAsDate(string $text): ?string
    {
        return preg_match('/\b(19|20)\d{2}\b/', $text, $m) ? $m[0] . '-01-01' : null;
    }

    private function guessIndustryId(string $text): ?int
    {
        $this->industryNames ??= Industry::pluck('industry_name', 'industry_id')->toArray();

        $needle = mb_strtolower($text);
        foreach ($this->industryNames as $id => $name) {
            if ($name && mb_strlen($name) > 3 && str_contains($needle, mb_strtolower($name))) {
                return $id;
            }
        }

        return null;
    }
}
