<?php

namespace App\Services\ResumeParser\Training;

use App\Services\ResumeParser\Labels;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Turns a sampled profile plus a layout variant into an ordered plan of blocks,
 * where every rendered piece of text already carries its label.
 *
 * Both the PDF template and the expected-label list are built from this one
 * structure. That is deliberate: if the labels were derived separately from the
 * rendering, then randomised section order and two-column layouts would make
 * the two drift apart, and the training data would be silently mislabelled —
 * the single worst failure available here, because nothing downstream could
 * detect it.
 *
 * Block shape:
 *   ['role' => string, 'cells' => [['label' => string, 'text' => string], ...], ...hints]
 *
 * A block with two cells is one visual row holding two logically separate
 * pieces, such as a job title and its date range flushed right.
 */
class DocumentPlanner
{
    private const SECTIONS = ['summary', 'education', 'skills', 'experience', 'projects', 'certifications'];

    /**
     * Local to each plan() call rather than the global mt_rand, which this used
     * to reseed. Reseeding globally reached into ProfileSampler's own sequence,
     * so the content of a profile depended on how many documents had been
     * planned before it and a seed stopped reproducing a corpus.
     */
    private Randomizer $random;

    /**
     * @return array{sidebar: bool, left: array<int,array>, main: array<int,array>}
     */
    public function plan(array $profile, array $variant): array
    {
        // Deterministic per profile+variant so a corpus is reproducible.
        $this->random = new Randomizer(
            new Mt19937(crc32($profile['profile_key'] . '|' . $variant['variant_key']))
        );

        $sidebar = (bool) ($variant['sidebar'] ?? false);
        $left = [];
        $main = [];

        $header = $this->headerBlocks($profile, $variant, $sidebar);

        if ($sidebar) {
            // Sidebar holds contact and skills; the name stays with the main
            // column so the document still reads as a resume.
            $left = array_merge($left, $header['contact']);
            $left = array_merge($left, $this->skillBlocks($profile, $variant, true));
            $main = array_merge($main, $header['title']);
        } else {
            $main = array_merge($main, $header['title'], $header['contact']);
        }

        foreach ($this->sectionOrder() as $section) {
            if ($section === 'skills' && $sidebar) {
                continue;
            }

            $main = array_merge($main, match ($section) {
                'summary' => $this->summaryBlocks($profile),
                'education' => $this->educationBlocks($profile, $variant),
                'skills' => $this->skillBlocks($profile, $variant, false),
                'experience' => $this->experienceBlocks($profile, $variant, 'experience'),
                'projects' => $this->experienceBlocks($profile, $variant, 'projects'),
                'certifications' => $this->certificationBlocks($profile, $variant),
                default => [],
            });
        }

        return ['sidebar' => $sidebar, 'left' => $left, 'main' => $main];
    }

    /**
     * Cells in the order the featurizer will emit them: the left column in full,
     * then the main column, matching how it reads a two-column page.
     *
     * @return array<int,array{label:string,text:string}>
     */
    public function expectedCells(array $plan): array
    {
        $cells = [];

        foreach (array_merge($plan['left'], $plan['main']) as $block) {
            foreach ($block['cells'] as $cell) {
                if (trim($cell['text']) !== '') {
                    $cells[] = $cell;
                }
            }
        }

        return $cells;
    }

    /** @return array<int,string> */
    private function sectionOrder(): array
    {
        $sections = self::SECTIONS;
        $sections = $this->random->shuffleArray($sections);

        return $sections;
    }

    /** @return array{title: array<int,array>, contact: array<int,array>} */
    private function headerBlocks(array $profile, array $variant, bool $sidebar): array
    {
        $title = [[
            'role' => 'title',
            'align' => $variant['header_style'] === 'centered' ? 'center' : 'left',
            'cells' => [['label' => Labels::DOC_TITLE, 'text' => mb_strtoupper($profile['name'])]],
        ]];

        $parts = array_values(array_filter([
            $profile['email'],
            $profile['phone'],
            $profile['linkedin'],
            $profile['address'],
        ]));

        $contact = [];

        if ($sidebar) {
            // A sidebar usually labels its contact block and stacks the details.
            $contact[] = $this->heading('contact', $variant);
            foreach ($parts as $part) {
                $contact[] = [
                    'role' => 'contact',
                    'align' => 'left',
                    'cells' => [['label' => Labels::CONTACT, 'text' => $part]],
                ];
            }
        } else {
            // One joined line, the way most single-column resumes print it.
            $contact[] = [
                'role' => 'contact',
                'align' => $variant['header_style'] === 'centered' ? 'center' : 'left',
                'cells' => [['label' => Labels::CONTACT, 'text' => implode('  •  ', $parts)]],
            ];
        }

        return ['title' => $title, 'contact' => $contact];
    }

    private function heading(string $section, array $variant): array
    {
        $options = SyntheticCorpus::HEADINGS[$section] ?? [mb_strtoupper($section)];

        return [
            'role' => 'heading',
            'section' => $section,
            'cells' => [[
                'label' => Labels::headerFor($section),
                'text' => $options[$this->random->getInt(0, count($options) - 1)],
            ]],
        ];
    }

    /** @return array<int,array> */
    private function summaryBlocks(array $profile): array
    {
        if (empty($profile['summary'])) {
            return [];
        }

        return [
            $this->heading('summary', ['variant_key' => '']),
            [
                'role' => 'paragraph',
                'cells' => [['label' => Labels::SUMMARY_TEXT, 'text' => $profile['summary']]],
            ],
        ];
    }

    /** @return array<int,array> */
    private function educationBlocks(array $profile, array $variant): array
    {
        $education = $profile['education'] ?? null;

        if ($education === null) {
            return [];
        }

        $blocks = [$this->heading('education', $variant)];

        $degreeCells = [['label' => Labels::EDU_LINE, 'text' => $education['degree']]];

        if ($variant['date_placement'] === 'right') {
            $degreeCells[] = ['label' => Labels::EDU_LINE, 'text' => 'Batch ' . $education['batch']];
        }

        $blocks[] = ['role' => 'item', 'cells' => $degreeCells];

        if ($variant['date_placement'] !== 'right') {
            $blocks[] = [
                'role' => 'sub',
                'cells' => [['label' => Labels::EDU_LINE, 'text' => 'Batch ' . $education['batch']]],
            ];
        }

        foreach ([$education['college'], $education['school']] as $line) {
            $blocks[] = ['role' => 'sub', 'cells' => [['label' => Labels::EDU_LINE, 'text' => $line]]];
        }

        return $blocks;
    }

    /** @return array<int,array> */
    private function skillBlocks(array $profile, array $variant, bool $inSidebar): array
    {
        $groups = $profile['skill_groups'] ?? [];

        if ($groups === []) {
            return [];
        }

        $blocks = [$this->heading('skills', $variant)];
        $style = $inSidebar ? 'stacked' : ($variant['skills_style'] ?? 'flat');

        foreach ($groups as $group) {
            if ($style === 'labelled' && $group['label'] !== null) {
                $blocks[] = [
                    'role' => 'skills',
                    'style' => 'labelled',
                    'cells' => [
                        ['label' => Labels::SKILL_LINE, 'text' => $group['label']],
                        ['label' => Labels::SKILL_LINE, 'text' => implode(', ', $group['names'])],
                    ],
                ];

                continue;
            }

            if ($style === 'chips' || $style === 'stacked') {
                // One cell per skill: rendered as separate chips or stacked
                // lines, so each is independently extractable.
                $blocks[] = [
                    'role' => 'skills',
                    'style' => $style,
                    'cells' => array_map(
                        fn ($name) => ['label' => Labels::SKILL_LINE, 'text' => $name],
                        $group['names']
                    ),
                ];

                continue;
            }

            $blocks[] = [
                'role' => 'skills',
                'style' => 'flat',
                'cells' => [['label' => Labels::SKILL_LINE, 'text' => implode(', ', $group['names'])]],
            ];
        }

        return $blocks;
    }

    /** @return array<int,array> */
    private function experienceBlocks(array $profile, array $variant, string $section): array
    {
        $entries = $section === 'projects' ? ($profile['projects'] ?? []) : ($profile['work'] ?? []);

        if ($entries === []) {
            return [];
        }

        $blocks = [$this->heading($section, $variant)];

        foreach ($entries as $entry) {
            $dateText = $entry['dates']['text'] ?? null;
            $placement = $variant['date_placement'];

            $titleCells = [['label' => Labels::EXP_TITLE, 'text' => $entry['title']]];

            if ($dateText !== null && $placement === 'inline') {
                // Glued into the title line, which is how the app's own export
                // ends up looking because dompdf ignores its flex rules.
                $titleCells[0]['text'] .= ', ' . $dateText;
            } elseif ($dateText !== null && $placement === 'right') {
                $titleCells[] = ['label' => Labels::EXP_META, 'text' => $dateText];
            }

            $blocks[] = ['role' => 'item', 'cells' => $titleCells];

            if ($dateText !== null && $placement === 'below') {
                $blocks[] = ['role' => 'sub', 'cells' => [['label' => Labels::EXP_META, 'text' => $dateText]]];
            }

            if (! empty($entry['organisation']) && ($variant['show_organisation'] ?? true)) {
                $blocks[] = [
                    'role' => 'sub',
                    'cells' => [['label' => Labels::EXP_META, 'text' => $entry['organisation']]],
                ];
            }

            $duties = $entry['duties'] ?? [];

            if ($duties === []) {
                continue;
            }

            if (($variant['duties_markup'] ?? 'ul') === 'p-joined') {
                $blocks[] = [
                    'role' => 'duty',
                    'markup' => 'p-joined',
                    'cells' => [['label' => Labels::EXP_BULLET, 'text' => implode('. ', $duties) . '.']],
                ];

                continue;
            }

            $blocks[] = [
                'role' => 'duty',
                'markup' => $variant['duties_markup'] ?? 'ul',
                'bullet' => $variant['bullet_glyph'] ?? '•',
                'cells' => array_map(
                    fn ($duty) => ['label' => Labels::EXP_BULLET, 'text' => $duty],
                    $duties
                ),
            ];
        }

        return $blocks;
    }

    /** @return array<int,array> */
    private function certificationBlocks(array $profile, array $variant): array
    {
        $certifications = $profile['certifications'] ?? [];

        if ($certifications === []) {
            return [];
        }

        $blocks = [$this->heading('certifications', $variant)];

        foreach ($certifications as $certification) {
            $dateText = $certification['date']['text'] ?? null;
            $placement = $variant['date_placement'];

            $name = $certification['name'];
            $issuer = $certification['issuer'];

            if ($issuer !== null && $placement === 'inline') {
                // Name and issuer on one line, em-dash separated, matching the
                // app's own certification export.
                $cells = [['label' => Labels::CERT_NAME, 'text' => $name . ' — ' . $issuer]];
                if ($dateText !== null) {
                    $cells[] = ['label' => Labels::CERT_META, 'text' => $dateText];
                }
                $blocks[] = ['role' => 'item', 'cells' => $cells];

                continue;
            }

            $cells = [['label' => Labels::CERT_NAME, 'text' => $name]];

            if ($dateText !== null && $placement === 'right') {
                $cells[] = ['label' => Labels::CERT_META, 'text' => $dateText];
            }

            $blocks[] = ['role' => 'item', 'cells' => $cells];

            $metaParts = array_values(array_filter([
                $issuer,
                $placement !== 'right' ? $dateText : null,
            ]));

            if ($metaParts !== []) {
                $blocks[] = [
                    'role' => 'sub',
                    'cells' => [['label' => Labels::CERT_META, 'text' => implode(', ', $metaParts)]],
                ];
            }
        }

        return $blocks;
    }
}
