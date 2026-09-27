<?php

namespace App\Services\ResumeParser\Training;

use App\Models\Skill;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Builds a random but internally consistent resume profile.
 *
 * Seeded, so a corpus is reproducible from its seed — a model version has to be
 * reproducible from the corpus state that produced it.
 *
 * Sections are present or absent and appear in a random order. That is the
 * highest-value axis of variation available: a parser that only ever sees
 * summary-then-education-then-skills learns the order rather than the content.
 */
class ProfileSampler
{
    /** @var array<int,array{name:string,category:?string}> */
    private array $knownSkills;

    /**
     * Its own generator rather than the global mt_rand, because other classes in
     * the generation pipeline also need randomness. Sharing global state made
     * the content of profile N depend on how many documents had been planned
     * before it, so a seed no longer reproduced a corpus.
     */
    private Randomizer $random;

    public function __construct(private int $seed = 20260927)
    {
        $this->random = new Randomizer(new Mt19937($seed));
        $this->knownSkills = $this->loadKnownSkills();
    }

    /** @return array<string,mixed> */
    public function sample(int $index): array
    {
        $field = $this->pick(SyntheticCorpus::FIELDS);
        $first = $this->pick(SyntheticCorpus::FIRST_NAMES);
        $last = $this->pick(SyntheticCorpus::LAST_NAMES);
        $slug = strtolower(preg_replace('/[^a-z]+/i', '', $first . $last));

        $workCount = $this->weighted([0 => 8, 1 => 30, 2 => 30, 3 => 20, 4 => 12]);
        $projectCount = $this->weighted([0 => 55, 1 => 20, 2 => 15, 3 => 10]);
        $certCount = $this->weighted([0 => 25, 1 => 25, 2 => 25, 3 => 15, 4 => 10]);
        $skillCount = $this->weighted([0 => 6, 3 => 18, 5 => 24, 8 => 26, 12 => 16, 15 => 10]);

        return [
            'profile_key' => sprintf('p%03d-%s', $index, $field),
            'field' => $field,
            'name' => trim("{$first} {$last}"),
            'email' => $slug . '@example.com',
            'phone' => $this->phone(),
            'address' => $this->chance(45)
                ? $this->pick(SyntheticCorpus::STREETS) . ', ' . $this->pick(SyntheticCorpus::CITIES)
                : null,
            'linkedin' => $this->chance(60) ? 'linkedin.com/in/' . $slug : null,
            'summary' => $this->chance(70) ? $this->summary() : null,
            'education' => $this->chance(90) ? [
                'degree' => SyntheticCorpus::PROGRAMS[$field],
                'college' => SyntheticCorpus::COLLEGES[$field],
                'school' => $this->pick(SyntheticCorpus::SCHOOLS),
                'batch' => (string) $this->random->getInt(2015, 2025),
            ] : null,
            'skill_groups' => $this->skillGroups($skillCount),
            'work' => $this->experiences($field, $workCount),
            'projects' => $this->projects($field, $projectCount),
            'certifications' => $this->certifications($certCount),
        ];
    }

    /** @return array<int,array{label:?string,names:array<int,string>}> */
    private function skillGroups(int $count): array
    {
        if ($count === 0) {
            return [];
        }

        $pool = array_merge(
            array_column($this->knownSkills, 'name'),
            // Mixed in deliberately: a model trained only on seeded skills
            // learns to recognise the seed list, not a skills line.
            SyntheticCorpus::OFF_GAZETTEER_SKILLS
        );

        $pool = $this->random->shuffleArray($pool);
        $chosen = array_slice($pool, 0, $count);

        // Sometimes grouped under category labels the way the app's own export
        // does it, sometimes a flat list the way most real resumes do.
        if (! $this->chance(45)) {
            return [['label' => null, 'names' => $chosen]];
        }

        $labels = array_values(Skill::CATEGORIES);
        $labels = $this->random->shuffleArray($labels);
        $groups = [];
        $perGroup = max(1, (int) ceil(count($chosen) / min(3, count($chosen))));

        foreach (array_chunk($chosen, $perGroup) as $i => $chunk) {
            $groups[] = ['label' => $labels[$i % count($labels)], 'names' => $chunk];
        }

        return $groups;
    }

    /** @return array<int,array<string,mixed>> */
    private function experiences(string $field, int $count): array
    {
        $titles = SyntheticCorpus::JOB_TITLES[$field];
        $out = [];

        for ($i = 0; $i < $count; $i++) {
            $hasDates = $this->chance(80);
            $ongoing = $i === 0 && $hasDates && $this->chance(30);

            $out[] = [
                'title' => $this->pick($titles),
                'organisation' => $this->chance(80) ? $this->pick(SyntheticCorpus::ORGANISATIONS) : null,
                'dates' => $hasDates ? $this->dateRange($ongoing) : null,
                'duties' => $this->duties($field, $this->random->getInt(1, 4)),
            ];
        }

        return $out;
    }

    /** @return array<int,array<string,mixed>> */
    private function projects(string $field, int $count): array
    {
        $out = [];

        for ($i = 0; $i < $count; $i++) {
            $out[] = [
                'title' => $this->projectTitle($field),
                'organisation' => $this->chance(25) ? $this->pick(SyntheticCorpus::ORGANISATIONS) : null,
                'dates' => $this->chance(50) ? $this->dateRange(false) : null,
                'duties' => $this->duties($field, $this->random->getInt(1, 3)),
            ];
        }

        return $out;
    }

    private function projectTitle(string $field): string
    {
        $subjects = [
            'it' => ['Online Enrolment System', 'Inventory Management System', 'Alumni Portal',
                'Attendance Tracker', 'E-Commerce Website', 'Barangay Records System'],
            'education' => ['Reading Intervention Programme', 'Science Fair Module', 'Learning Kit for Grade 5'],
            'accountancy' => ['Cash Flow Analysis Project', 'Small Business Bookkeeping Study'],
            'business' => ['Market Feasibility Study', 'Customer Satisfaction Survey', 'Business Plan Proposal'],
            'engineering' => ['Two-Storey Residential Design', 'Drainage System Study', 'Structural Load Analysis'],
            'social' => ['Community Needs Assessment', 'Youth Development Programme', 'Mental Health Awareness Campaign'],
        ];

        return $this->pick($subjects[$field] ?? $subjects['it']);
    }

    /** @return array<int,string> */
    private function duties(string $field, int $count): array
    {
        $templates = SyntheticCorpus::DUTY_TEMPLATES[$field] ?? SyntheticCorpus::DUTY_TEMPLATES['it'];
        $templates = $this->random->shuffleArray($templates);

        $out = [];
        foreach (array_slice($templates, 0, $count) as $template) {
            $out[] = $this->fill($template, SyntheticCorpus::DUTY_SLOTS);
        }

        return $out;
    }

    /** @return array<int,array<string,mixed>> */
    private function certifications(int $count): array
    {
        $pool = SyntheticCorpus::CERTIFICATIONS;
        $pool = $this->random->shuffleArray($pool);

        $out = [];
        foreach (array_slice($pool, 0, $count) as $cert) {
            $out[] = [
                'name' => $cert['name'],
                'issuer' => $this->chance(80) ? $cert['issuer'] : null,
                'type' => $cert['type'],
                'date' => $this->chance(75) ? $this->singleDate() : null,
            ];
        }

        return $out;
    }

    private function summary(): string
    {
        return $this->fill($this->pick(SyntheticCorpus::SUMMARY_TEMPLATES), SyntheticCorpus::SUMMARY_SLOTS);
    }

    /**
     * A rendered date range plus the truth behind it, so the generator can check
     * the date parser against known values rather than its own output.
     *
     * @return array{text:string,start:string,end:?string,ongoing:bool}
     */
    private function dateRange(bool $ongoing): array
    {
        $startYear = $this->random->getInt(2015, 2024);
        $startMonth = $this->random->getInt(1, 12);
        $months = $this->random->getInt(3, 48);
        $endTotal = ($startYear * 12) + ($startMonth - 1) + $months;
        $endYear = intdiv($endTotal, 12);
        $endMonth = ($endTotal % 12) + 1;

        $separator = $this->pick([' - ', ' – ', ' — ', ' to ']);
        $start = $this->renderDate($startYear, $startMonth);
        $end = $ongoing
            ? $this->pick(['Present', 'Current', 'present', 'Ongoing'])
            : $this->renderDate($endYear, $endMonth);

        return [
            'text' => $start . $separator . $end,
            'start' => sprintf('%04d-%02d-01', $startYear, $startMonth),
            'end' => $ongoing ? null : sprintf('%04d-%02d-01', $endYear, $endMonth),
            'ongoing' => $ongoing,
        ];
    }

    private function singleDate(): array
    {
        $year = $this->random->getInt(2016, 2026);
        $month = $this->random->getInt(1, 12);

        return [
            'text' => $this->renderDate($year, $month),
            'date' => sprintf('%04d-%02d-01', $year, $month),
        ];
    }

    /** The spellings real resumes use, including year-only. */
    private function renderDate(int $year, int $month): string
    {
        $short = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][$month - 1];
        $long = ['January', 'February', 'March', 'April', 'May', 'June', 'July',
            'August', 'September', 'October', 'November', 'December'][$month - 1];

        return match ($this->random->getInt(1, 6)) {
            1 => "{$short} {$year}",
            2 => "{$long} {$year}",
            3 => sprintf('%02d/%d', $month, $year),
            4 => "{$short}. {$year}",
            5 => (string) $year,
            default => "{$short} {$year}",
        };
    }

    private function phone(): string
    {
        return match ($this->random->getInt(1, 3)) {
            1 => sprintf('09%02d %03d %04d', $this->random->getInt(0, 99), $this->random->getInt(0, 999), $this->random->getInt(0, 9999)),
            2 => sprintf('+639%02d%03d%04d', $this->random->getInt(0, 99), $this->random->getInt(0, 999), $this->random->getInt(0, 9999)),
            default => sprintf('(02) %04d %04d', $this->random->getInt(1000, 9999), $this->random->getInt(1000, 9999)),
        };
    }

    private function fill(string $template, array $slots): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($match) use ($slots) {
            $options = $slots[$match[1]] ?? null;

            return $options === null ? $match[0] : $this->pick($options);
        }, $template);
    }

    /** @return array<int,array{name:string,category:?string}> */
    private function loadKnownSkills(): array
    {
        try {
            return Skill::query()->get(['skill_name', 'skill_category'])
                ->map(fn ($skill) => [
                    'name' => (string) $skill->skill_name,
                    'category' => $skill->skill_category ? (string) $skill->skill_category : null,
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function pick(array $options)
    {
        return $options[$this->random->getInt(0, count($options) - 1)];
    }

    private function chance(int $percent): bool
    {
        return $this->random->getInt(1, 100) <= $percent;
    }

    /** @param array<int,int> $weights value => relative weight */
    private function weighted(array $weights): int
    {
        $total = array_sum($weights);
        $roll = $this->random->getInt(1, max(1, $total));

        foreach ($weights as $value => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $value;
            }
        }

        return array_key_first($weights);
    }
}
