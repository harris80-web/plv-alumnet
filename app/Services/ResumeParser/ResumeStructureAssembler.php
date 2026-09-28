<?php

namespace App\Services\ResumeParser;

use App\Models\Skill;

/**
 * Turns labelled lines into the exact array the resume wizard prefills from.
 *
 * Deliberately deterministic. The model's job is the part that genuinely
 * requires judgement — what role a line plays in a layout nobody has seen
 * before — and everything from there on has an exact answer: grouping a title
 * with the bullets beneath it, reading a date range, matching a skill to the
 * skills table. Spending model capacity on those would add error rather than
 * remove it.
 *
 * The output shape is Alumnus::toResumeFormArray() minus resume_completeness.
 * That contract is also what the wizard and the job-apply modal prefill from, so
 * no key may be renamed here.
 *
 * Every save() validation rule is enforced before returning. A payload that
 * fails validation immediately after import looks like a crash to the user, so
 * it must never leave this class.
 */
class ResumeStructureAssembler
{
    private const MAX_EXPERIENCES = 15;
    private const MAX_CERTIFICATIONS = 15;
    private const MAX_SKILLS = 30;
    private const MAX_SUMMARY = 500;
    private const MAX_TITLE = 150;
    private const MAX_DESCRIPTION = 2000;
    private const MAX_CERT_NAME = 150;
    private const MAX_CERT_FROM = 150;
    private const MAX_SKILL_NAME = 100;
    private const MAX_URL = 255;

    /**
     * Below this the model was close to calling the line something else. Used
     * only to stop one job being split into several, never as a general
     * confidence gate.
     */
    private const LOW_MARGIN = 2.0;

    /** Words that mark a segment as the issuing body rather than the award. */
    private const ORG_KEYWORDS = ['inc', 'corp', 'corporation', 'ltd', 'company', 'academy', 'institute',
        'university', 'college', 'foundation', 'center', 'centre', 'council', 'board', 'commission',
        'authority', 'department', 'bureau', 'agency', 'office', 'prc', 'tesda', 'dict', 'dole',
        'aws', 'amazon', 'microsoft', 'google', 'cisco', 'coursera', 'udemy', 'oracle'];

    public function __construct(
        private ResumeDateParser $dates = new ResumeDateParser(),
        private SkillGazetteer $skills = new SkillGazetteer(),
        private IndustryMatcher $industries = new IndustryMatcher(),
    ) {
    }

    /**
     * @param  array<int,Line>  $lines
     * @param  array<int,string>  $labels
     * @param  array<int,float>  $margins
     * @return array{payload: array<string,mixed>, provenance: array<string,array<int,int>>}
     */
    public function assemble(array $lines, array $labels, array $margins, string $rawText = ''): array
    {
        $state = [
            'section' => null,
            'certType' => 'certification',
            'summary' => [],
            'skills' => [],
            'experiences' => [],
            'certifications' => [],
            'open' => null,
            'openCert' => null,
        ];

        foreach ($lines as $index => $line) {
            $label = $labels[$index] ?? Labels::OTHER;
            $text = trim($line->text);

            if ($text === '') {
                continue;
            }

            if (Labels::isHeader($label)) {
                $state = $this->closeGroups($state);
                $state['section'] = $this->reconcileSection($label, $text);

                if ($state['section'] === 'certifications') {
                    $state['certType'] = $this->certificationTypeFromHeading($text);
                }

                continue;
            }

            $state = match ($label) {
                Labels::SUMMARY_TEXT => $this->addSummary($state, $index, $text),
                Labels::SKILL_LINE => $this->addSkillLine($state, $index, $text),
                Labels::EXP_TITLE => $this->addExperienceTitle($state, $index, $text, $margins[$index] ?? 0.0),
                Labels::EXP_META => $this->addExperienceMeta($state, $index, $text),
                Labels::EXP_BULLET => $this->addExperienceBullet($state, $index, $text),
                Labels::CERT_NAME => $this->addCertificationName($state, $index, $text),
                Labels::CERT_META => $this->addCertificationMeta($state, $index, $text),
                // doc_title, contact and edu_line have nowhere to go in this
                // schema; they exist so the model can recognise them rather
                // than dragging them into skills or experience.
                default => $state,
            };
        }

        $state = $this->closeGroups($state);

        return $this->build($state, $rawText);
    }

    // ───────────────────────────── accumulation ─────────────────────────────

    private function addSummary(array $state, int $index, string $text): array
    {
        $state['summary'][] = ['index' => $index, 'text' => $text];

        return $state;
    }

    private function addSkillLine(array $state, int $index, string $text): array
    {
        $state['skills'][] = ['index' => $index, 'text' => $text];

        return $state;
    }

    private function addExperienceTitle(array $state, int $index, string $text, float $margin): array
    {
        $open = $state['open'];

        // A hesitant title immediately under a group that has no detail yet is
        // far more likely to be a second line of that group's metadata than a
        // new job. This targets the one failure a user notices instantly: a
        // single role exploding into several rows.
        if ($open !== null && $margin < self::LOW_MARGIN && $open['bullets'] === [] && $open['meta'] === []) {
            return $this->addExperienceMeta($state, $index, $text);
        }

        $state = $this->closeExperience($state);

        $state['open'] = [
            'type' => $state['section'] === 'projects' ? 'project' : 'work',
            'title' => ['index' => $index, 'text' => $text],
            'meta' => [],
            'bullets' => [],
        ];

        return $state;
    }

    private function addExperienceMeta(array $state, int $index, string $text): array
    {
        if ($state['open'] === null) {
            return $state;
        }

        $state['open']['meta'][] = ['index' => $index, 'text' => $text];

        return $state;
    }

    private function addExperienceBullet(array $state, int $index, string $text): array
    {
        if ($state['open'] === null) {
            return $state;
        }

        $state['open']['bullets'][] = ['index' => $index, 'text' => $text];

        return $state;
    }

    private function addCertificationName(array $state, int $index, string $text): array
    {
        $state = $this->closeCertification($state);

        $state['openCert'] = [
            'type' => $state['certType'],
            'name' => ['index' => $index, 'text' => $text],
            'meta' => [],
        ];

        return $state;
    }

    private function addCertificationMeta(array $state, int $index, string $text): array
    {
        if ($state['openCert'] === null) {
            return $state;
        }

        $state['openCert']['meta'][] = ['index' => $index, 'text' => $text];

        return $state;
    }

    private function closeGroups(array $state): array
    {
        return $this->closeCertification($this->closeExperience($state));
    }

    private function closeExperience(array $state): array
    {
        if ($state['open'] !== null) {
            $state['experiences'][] = $state['open'];
            $state['open'] = null;
        }

        return $state;
    }

    private function closeCertification(array $state): array
    {
        if ($state['openCert'] !== null) {
            $state['certifications'][] = $state['openCert'];
            $state['openCert'] = null;
        }

        return $state;
    }

    /**
     * The model decides whether a line is a heading; a keyword check decides
     * which heading it is. That split is intentional — the first needs
     * typography and generalisation, the second has an exact answer.
     */
    private function reconcileSection(string $label, string $text): ?string
    {
        $patterns = [
            'experience' => '/\b(work|professional|employment|relevant)?\s*(experience|employment|history)\b/i',
            'projects' => '/\bprojects?\b/i',
            'skills' => '/\b(skills?|competenc|expertise|tools)\b/i',
            'summary' => '/\b(summary|objective|profile|about)\b/i',
            'certifications' => '/\b(certificat|licens|training|seminar|development)\b/i',
            'education' => '/\b(education|academic)\b/i',
            'contact' => '/\b(contact|details|touch)\b/i',
        ];

        foreach ($patterns as $section => $pattern) {
            if (preg_match($pattern, $text)) {
                return $section;
            }
        }

        return Labels::sectionFor($label);
    }

    private function certificationTypeFromHeading(string $text): string
    {
        // Certification is tested first because combined headings are common
        // ("Certifications & Trainings", as this app's own export prints) and
        // the first-named kind is the one the section is really about.
        return match (true) {
            (bool) preg_match('/\bcertificat|\blicens/i', $text) => 'certification',
            (bool) preg_match('/\bseminars?\b/i', $text) => 'seminar',
            (bool) preg_match('/\btrainings?\b/i', $text) => 'training',
            default => 'certification',
        };
    }

    // ───────────────────────────── building ─────────────────────────────

    private function build(array $state, string $rawText): array
    {
        $provenance = [];

        $summary = $this->buildSummary($state['summary'], $provenance);
        $skills = $this->buildSkills($state['skills'], $provenance);
        $experiences = $this->buildExperiences($state['experiences'], $provenance);
        $certifications = $this->buildCertifications($state['certifications'], $provenance);

        return [
            'payload' => [
                'resume_summary' => $summary,
                // A regex answers this exactly, so there is nothing for a model
                // to contribute; run over the whole document rather than any one
                // line, since the address often sits outside the contact block.
                'linkedin_url' => $this->extractLinkedin($rawText),
                'skills' => $skills,
                'experiences' => $experiences,
                'certifications' => $certifications,
            ],
            'provenance' => $provenance,
        ];
    }

    private function buildSummary(array $entries, array &$provenance): ?string
    {
        if ($entries === []) {
            return null;
        }

        $text = trim(preg_replace('/\s+/u', ' ', implode(' ', array_column($entries, 'text'))));

        if ($text === '') {
            return null;
        }

        $provenance['resume_summary'] = array_column($entries, 'index');

        return mb_substr($text, 0, self::MAX_SUMMARY);
    }

    /** @return array<int,array{name:string,category:?string}> */
    private function buildSkills(array $entries, array &$provenance): array
    {
        $categoryLabels = array_map('mb_strtolower', array_values(Skill::CATEGORIES));
        $out = [];
        $seen = [];

        foreach ($entries as $entry) {
            foreach ($this->splitSkillLine($entry['text']) as $candidate) {
                foreach ($this->expandSkill($candidate) as $skill) {
                    $key = mb_strtolower($skill['name']);

                    // A grouped skills block prints its category as a label on
                    // the same line; importing "Tools & Software" as a skill
                    // would be nonsense.
                    if ($key === '' || isset($seen[$key]) || in_array($key, $categoryLabels, true)) {
                        continue;
                    }

                    $seen[$key] = true;
                    $position = count($out);
                    $out[] = [
                        'name' => mb_substr($skill['name'], 0, self::MAX_SKILL_NAME),
                        'category' => $skill['category'],
                    ];
                    $provenance["skills.{$position}.name"] = [$entry['index']];

                    if (count($out) >= self::MAX_SKILLS) {
                        return $out;
                    }
                }
            }
        }

        return $out;
    }

    /** @return array<int,string> */
    private function splitSkillLine(string $text): array
    {
        $parts = preg_split('/[,;|•·∙\/\n]+|\s{2,}|\s+[-–—]\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = [];

        foreach ($parts as $part) {
            $part = trim($part, " \t\n\r\0\x0B.:-–—");

            if ($this->looksLikeSkill($part)) {
                $out[] = $part;
            }
        }

        return $out;
    }

    /**
     * A skill is a short noun phrase, never a sentence.
     *
     * Downloaded resume templates routinely ship a page of the vendor's own
     * instructions and marketing, and nothing about its typography distinguishes
     * it from a skills list, so the classifier has no way to tell. Rejecting
     * anything sentence-shaped keeps "Ready to land your next job offer?" and
     * "Cover Letter Generator →" out of somebody's profile. Residual noise is
     * expected and acceptable: every field is reviewed before it is saved.
     */
    private function looksLikeSkill(string $text): bool
    {
        $length = mb_strlen($text);

        if ($length < 2 || $length > 40) {
            return false;
        }

        // Punctuation that only occurs in prose, calls to action or navigation.
        if (preg_match('/[?!→←…:;]/u', $text)) {
            return false;
        }

        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) > 5) {
            return false;
        }

        // A leading conjunction or preposition means this is a clause fragment
        // left over from splitting a sentence.
        if (preg_match('/^(and|or|but|with|for|to|in|on|at|of|the|a|an|this|that|if|as)$/i', $words[0] ?? '')) {
            return false;
        }

        // Needs at least one letter; a bare number or symbol is not a skill.
        return (bool) preg_match('/\p{L}/u', $text);
    }

    /**
     * One raw token becomes one or more skills. Extraction sometimes glues
     * neighbouring skills together when they sit too close to separate, so an
     * unrecognised multi-word token is checked against the skills table for
     * known names inside it before being accepted as written.
     *
     * @return array<int,array{name:string,category:?string}>
     */
    private function expandSkill(string $raw): array
    {
        $canonical = $this->skills->canonicalize($raw);

        if ($canonical['matched']) {
            return [['name' => $canonical['name'], 'category' => $canonical['category']]];
        }

        if (str_contains(trim($raw), ' ')) {
            $inner = $this->skills->matchWithin($raw);

            if (count($inner) >= 2) {
                return array_map(fn ($skill) => [
                    'name' => $skill['name'],
                    'category' => $skill['category'],
                ], $inner);
            }
        }

        // Unknown skills are kept with no category: save()'s own default and the
        // wizard's fallback both supply one, and guessing here would be worse.
        return [['name' => $canonical['name'], 'category' => null]];
    }

    /** @return array<int,array<string,mixed>> */
    private function buildExperiences(array $groups, array &$provenance): array
    {
        $out = [];

        foreach ($groups as $group) {
            if (count($out) >= self::MAX_EXPERIENCES) {
                break;
            }

            $title = trim($group['title']['text']);
            $metaTexts = array_column($group['meta'], 'text');

            // The app's own export prints a duration after the title, so a
            // re-imported copy of it arrives with both glued together.
            [$title, $durationFromTitle] = $this->stripTrailingDuration($title);

            if ($title === '') {
                continue;
            }

            $dateSource = implode(' | ', array_merge([$group['title']['text']], $metaTexts));
            $range = $this->dates->parseRange($dateSource);

            $bullets = $this->mergeWrappedBullets($group['bullets']);
            $description = $bullets === [] ? null : implode("\n", array_column($bullets, 'text'));

            $position = count($out);

            $months = $range['duration_months']
                ?? $durationFromTitle
                ?? $this->durationFromText($dateSource);

            $entry = [
                'type' => $group['type'],
                'job_title' => mb_substr($title, 0, self::MAX_TITLE),
                'job_description' => $description === null ? null : mb_substr($description, 0, self::MAX_DESCRIPTION),
                // Emitted even when real dates exist: both wizards post a hidden
                // duration_months and save() falls back to it.
                'duration_months' => $months === null ? null : max(0, min(600, $months)),
                'industry_id' => $group['type'] === 'project'
                    ? null
                    : $this->industries->match(implode(' ', array_merge([$title], $metaTexts, array_column($bullets, 'text')))),
                'start_date' => $range['start_date'] ?? null,
                'end_date' => $range['end_date'] ?? null,
                'is_ongoing' => $range['is_ongoing'] ?? false,
            ];

            $out[] = $entry;

            $provenance["experiences.{$position}.job_title"] = [$group['title']['index']];

            if ($bullets !== []) {
                $provenance["experiences.{$position}.job_description"] = array_column($bullets, 'index');
            }

            if ($metaTexts !== []) {
                $provenance["experiences.{$position}.meta"] = array_column($group['meta'], 'index');
            }
        }

        return $out;
    }

    /**
     * Rejoins a bullet that the renderer wrapped onto a second line. One rule
     * replaces a whole extra label: a following bullet that neither starts with
     * a glyph nor begins a new sentence is a continuation.
     *
     * @return array<int,array{index:int,text:string}>
     */
    private function mergeWrappedBullets(array $bullets): array
    {
        $out = [];

        foreach ($bullets as $bullet) {
            $text = $this->stripBulletGlyph($bullet['text']);

            if ($text === '') {
                continue;
            }

            $startsNew = $text !== $bullet['text'] || preg_match('/^[\p{Lu}\d]/u', $text) === 1;

            if ($out !== [] && ! $startsNew) {
                $last = count($out) - 1;
                $out[$last]['text'] = rtrim($out[$last]['text']) . ' ' . $text;

                continue;
            }

            $out[] = ['index' => $bullet['index'], 'text' => $text];
        }

        return $out;
    }

    private function stripBulletGlyph(string $text): string
    {
        return trim(preg_replace('/^\s*[•▪◦‣·∙*\-–—]+\s*/u', '', $text));
    }

    /** @return array{0:string,1:?int} the title without its duration, and the months found */
    private function stripTrailingDuration(string $text): array
    {
        $pattern = '/\s+((?:\d+\s*yrs?)?\s*(?:\d+\s*mos?)?)\s*$/i';

        if (preg_match($pattern, $text, $matches) && trim($matches[1]) !== '') {
            $months = $this->durationFromText($matches[1]);

            if ($months !== null) {
                return [trim(mb_substr($text, 0, mb_strlen($text) - mb_strlen($matches[0]))), $months];
            }
        }

        return [$text, null];
    }

    private function durationFromText(string $text): ?int
    {
        $years = preg_match('/(\d+)\s*yrs?\b/i', $text, $y) ? (int) $y[1] : 0;
        $months = preg_match('/(\d+)\s*mos?\b/i', $text, $m) ? (int) $m[1] : 0;

        if ($years === 0 && $months === 0) {
            return null;
        }

        return ($years * 12) + $months;
    }

    /** @return array<int,array<string,mixed>> */
    private function buildCertifications(array $groups, array &$provenance): array
    {
        $out = [];

        foreach ($groups as $group) {
            if (count($out) >= self::MAX_CERTIFICATIONS) {
                break;
            }

            $metaTexts = array_column($group['meta'], 'text');
            [$name, $issuerFromName, $typeFromName] = $this->splitCertification($group['name']['text']);

            if ($name === '') {
                continue;
            }

            $issuer = $issuerFromName ?? $this->issuerFromMeta($metaTexts);
            $date = $this->dates->parseSingle(implode(' ', array_merge([$group['name']['text']], $metaTexts)));

            $position = count($out);

            $out[] = [
                // A marker printed against the entry itself beats one inferred
                // from the heading, which may cover several kinds at once.
                'certification_type' => $typeFromName ?? $group['type'],
                'certification_name' => mb_substr($name, 0, self::MAX_CERT_NAME),
                'certification_from' => $issuer === null ? null : mb_substr($issuer, 0, self::MAX_CERT_FROM),
                'certification_date' => $date,
            ];

            $provenance["certifications.{$position}.certification_name"] = [$group['name']['index']];

            if ($group['meta'] !== []) {
                $provenance["certifications.{$position}.certification_from"] = array_column($group['meta'], 'index');
            }
        }

        return $out;
    }

    /**
     * Splits "AWS Cloud Practitioner — Amazon Web Services (Certification) Mar 2026"
     * into its award and its issuer. The trailing type and date are added by this
     * app's own export, so a re-imported resume carries them.
     *
     * @return array{0:string,1:?string,2:?string} name, issuer, and the type if
     *                                              the entry declared one itself
     */
    private function splitCertification(string $text): array
    {
        $type = null;

        if (preg_match('/\((certification|seminar|training)\)/i', $text, $marker)) {
            $type = mb_strtolower($marker[1]);
        }

        $text = preg_replace('/\s*\((?:certification|seminar|training)\)\s*/i', ' ', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        // A trailing month-year belongs to the date, not the name.
        $text = trim(preg_replace('/\s+(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*\.?\s*(?:19|20)\d{2}\s*$/i', '', $text));
        $text = trim(preg_replace('/\s+(?:19|20)\d{2}\s*$/', '', $text));

        $parts = preg_split('/\s+[—–]\s+|\s+-\s+/u', $text, 2, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) === 2) {
            return [trim($parts[0]), trim($parts[1]) === '' ? null : trim($parts[1]), $type];
        }

        return [$text, null, $type];
    }

    private function issuerFromMeta(array $metaTexts): ?string
    {
        foreach ($metaTexts as $text) {
            foreach (preg_split('/\s*,\s*/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $segment) {
                $segment = trim($segment);

                // A bare year is a date, never an issuer.
                if ($segment === '' || preg_match('/^(?:19|20)\d{2}$/', $segment)) {
                    continue;
                }

                foreach (self::ORG_KEYWORDS as $keyword) {
                    if (preg_match('/(?<![\p{L}])' . preg_quote($keyword, '/') . '(?![\p{L}])/iu', $segment)) {
                        return $segment;
                    }
                }
            }
        }

        // Nothing named an organisation, so fall back to the first segment that
        // is not a date at all.
        foreach ($metaTexts as $text) {
            $candidate = trim(preg_split('/\s*,\s*/u', $text)[0] ?? '');

            if ($candidate !== '' && ! preg_match('/^(?:19|20)\d{2}$/', $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function extractLinkedin(string $rawText): ?string
    {
        if (! preg_match('~(https?://)?(www\.)?linkedin\.com/in/[A-Za-z0-9\-_%]+~i', $rawText, $matches)) {
            return null;
        }

        $url = $matches[0];

        if (! preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }

        return mb_strlen($url) > self::MAX_URL ? null : $url;
    }
}
