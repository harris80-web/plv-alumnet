<?php

namespace App\Services\ResumeParser\Training;

/**
 * The layout parameter sets synthetic resumes are rendered through.
 *
 * Parameterising a couple of templates covers far more of the real variation
 * space than hand-writing ten fixed ones, because the variation is
 * combinatorial: font, heading style, date placement, bullet glyph, column
 * count and section order multiply out.
 *
 * Only dompdf core fonts are used, so no font files are needed. Layout is done
 * with tables and text-align, never flexbox — dompdf silently ignores
 * display:flex, which is why the live resume template's own .row rules do
 * nothing and its dates end up glued to job titles.
 */
class LayoutVariantFactory
{
    public const TEMPLATE_DESIGNED = 'alumni.resume-synthetic';

    /** Deliberately unlike the app's own export, so it can be held out entirely. */
    public const TEMPLATE_PLAIN = 'alumni.resume-synthetic-plain';

    /** @return array<int,array<string,mixed>> */
    public function all(): array
    {
        $variants = [];

        foreach ($this->designed() as $variant) {
            $variants[] = $variant;
        }

        foreach ($this->plain() as $variant) {
            $variants[] = $variant;
        }

        return $variants;
    }

    /** @return array<int,array<string,mixed>> */
    private function designed(): array
    {
        $specs = [
            ['v01', 11, 'Helvetica', 'centered', 'caps-rule', 'right', 'ul', '•', 'chips', false],
            ['v02', 10, 'Helvetica', 'left', 'bold', 'inline', 'p-each', '-', 'flat', false],
            ['v03', 12, 'Times', 'centered', 'caps-plain', 'below', 'ul', '▪', 'labelled', false],
            ['v04', 9, 'Helvetica', 'left', 'caps-bar', 'right', 'ul', '•', 'chips', false],
            ['v05', 11, 'Times', 'left', 'bold', 'right', 'p-joined', 'none', 'flat', false],
            ['v06', 10, 'Courier', 'centered', 'caps-plain', 'below', 'p-each', '-', 'flat', false],
            ['v07', 12, 'Helvetica', 'left', 'caps-rule', 'inline', 'ul', '•', 'labelled', false],
            ['v08', 9, 'Times', 'centered', 'bold', 'right', 'ul', '▪', 'chips', false],
            ['v09', 11, 'Helvetica', 'left', 'caps-bar', 'below', 'p-each', '•', 'flat', false],
            ['v10', 10, 'Helvetica', 'centered', 'caps-rule', 'right', 'ul', '-', 'two-col-table', false],
            ['v11', 11, 'Times', 'left', 'caps-plain', 'inline', 'ul', '•', 'flat', false],
            ['v12', 10, 'Helvetica', 'left', 'bold', 'below', 'p-joined', 'none', 'labelled', false],
            // Genuine two-column pages: the sidebar holds contact plus skills.
            // These are what exercise gutter detection.
            ['v13', 10, 'Helvetica', 'sidebar', 'caps-rule', 'right', 'ul', '•', 'flat', true],
            ['v14', 11, 'Times', 'sidebar', 'bold', 'below', 'ul', '▪', 'flat', true],
            ['v15', 9, 'Helvetica', 'sidebar', 'caps-bar', 'inline', 'p-each', '-', 'flat', true],
            ['v16', 10, 'Times', 'sidebar', 'caps-plain', 'right', 'ul', '•', 'labelled', true],
        ];

        $variants = [];

        foreach ($specs as [$key, $size, $font, $header, $headingStyle, $datePlacement, $duties, $bullet, $skills, $sidebar]) {
            $variants[] = [
                'variant_key' => $key,
                'template' => self::TEMPLATE_DESIGNED,
                'font_size' => $size,
                'font_family' => $font,
                'header_style' => $header,
                'heading_style' => $headingStyle,
                'date_placement' => $datePlacement,
                'duties_markup' => $duties,
                'bullet_glyph' => $bullet,
                'skills_style' => $skills,
                'sidebar' => $sidebar,
                'margin' => [36, 40, 48][crc32($key) % 3],
                'line_height' => [1.25, 1.4, 1.55][crc32($key) % 3],
                'show_organisation' => crc32($key . 'org') % 4 !== 0,
            ];
        }

        return $variants;
    }

    /** @return array<int,array<string,mixed>> */
    private function plain(): array
    {
        $specs = [
            ['w01', 11, 'Times', 'right', 'ul', '-'],
            ['w02', 12, 'Times', 'below', 'p-each', 'none'],
            ['w03', 10, 'Courier', 'right', 'p-joined', '-'],
            ['w04', 11, 'Times', 'inline', 'ul', '•'],
            ['w05', 12, 'Courier', 'below', 'ul', '-'],
            ['w06', 10, 'Times', 'right', 'p-each', 'none'],
            ['w07', 11, 'Courier', 'inline', 'p-joined', '-'],
            ['w08', 12, 'Times', 'right', 'ul', '▪'],
        ];

        $variants = [];

        foreach ($specs as [$key, $size, $font, $datePlacement, $duties, $bullet]) {
            $variants[] = [
                'variant_key' => $key,
                'template' => self::TEMPLATE_PLAIN,
                'font_size' => $size,
                'font_family' => $font,
                'header_style' => 'left',
                'heading_style' => 'caps-plain',
                'date_placement' => $datePlacement,
                'duties_markup' => $duties,
                'bullet_glyph' => $bullet,
                'skills_style' => 'flat',
                'sidebar' => false,
                'margin' => [54, 62][crc32($key) % 2],
                'line_height' => [1.3, 1.5][crc32($key) % 2],
                'show_organisation' => true,
            ];
        }

        return $variants;
    }
}
