<?php

use App\Services\ResumeParser\Labels;
use App\Services\ResumeParser\Line;
use App\Services\ResumeParser\Training\ExampleBuilder;

/**
 * Alignment between rendered strings and extracted lines. A wrong label here is
 * the worst failure in the project, because nothing downstream can detect it —
 * the model simply learns the wrong thing. So the behaviour under every
 * mismatch is pinned down explicitly, especially the refusal to guess.
 */
function line(string $text): Line
{
    return new Line(
        text: $text, page: 1, x: 10, y: 100, size: 10,
        bold: false, italic: false, gapAbove: 5, rowKey: 0, fragmentIndex: 0, fragmentCount: 1,
    );
}

function cell(string $label, string $text): array
{
    return ['label' => $label, 'text' => $text];
}

it('labels lines that came back exactly as rendered', function () {
    $result = (new ExampleBuilder())->align(
        [line('WORK EXPERIENCE'), line('Backend Developer'), line('Acme Digital Inc.')],
        [
            cell(Labels::HEADER_EXPERIENCE, 'WORK EXPERIENCE'),
            cell(Labels::EXP_TITLE, 'Backend Developer'),
            cell(Labels::EXP_META, 'Acme Digital Inc.'),
        ]
    );

    expect($result['gold_labels'])->toBe([
        Labels::HEADER_EXPERIENCE, Labels::EXP_TITLE, Labels::EXP_META,
    ])->and($result['coverage'])->toBe(1.0);
});

it('labels every line a wrapped paragraph was split across', function () {
    // A summary is one rendered string but comes out as several lines.
    $result = (new ExampleBuilder())->align(
        [
            line('Backend developer focused on Laravel APIs'),
            line('and database design for internal teams.'),
        ],
        [cell(Labels::SUMMARY_TEXT, 'Backend developer focused on Laravel APIs and database design for internal teams.')]
    );

    expect($result['gold_labels'])->toBe([Labels::SUMMARY_TEXT, Labels::SUMMARY_TEXT])
        ->and($result['coverage'])->toBe(1.0)
        ->and($result['unconsumed_cells'])->toBe(0);
});

it('labels separate cells that came back glued into one line', function () {
    // Skills positioned close together extract as a single line; this happens
    // for real and must not mask the whole line.
    $result = (new ExampleBuilder())->align(
        [line('Laravel JavaScript')],
        [cell(Labels::SKILL_LINE, 'Laravel'), cell(Labels::SKILL_LINE, 'JavaScript')]
    );

    expect($result['gold_labels'])->toBe([Labels::SKILL_LINE])
        ->and($result['coverage'])->toBe(1.0);
});

it('masks a glued line whose parts disagree about the label', function () {
    // 'Backend Developer' and its date range arriving as one line is genuinely
    // half one label and half another, so it teaches nothing reliable.
    $result = (new ExampleBuilder())->align(
        [line('Backend Developer Jan 2020')],
        [cell(Labels::EXP_TITLE, 'Backend Developer'), cell(Labels::EXP_META, 'Jan 2020')]
    );

    expect($result['gold_labels'])->toBe([null]);
});

it('recovers alignment when a rendered cell never reached the text', function () {
    $result = (new ExampleBuilder())->align(
        [line('SKILLS'), line('Laravel')],
        [
            cell(Labels::HEADER_SKILLS, 'SKILLS'),
            // A chip that rendered as a glyph only, so no text came back.
            cell(Labels::SKILL_LINE, '•'),
            cell(Labels::SKILL_LINE, 'Laravel'),
        ]
    );

    expect($result['gold_labels'])->toBe([Labels::HEADER_SKILLS, Labels::SKILL_LINE]);
});

it('masks a line nothing rendered can account for', function () {
    $result = (new ExampleBuilder())->align(
        [line('Backend Developer'), line('Page 1 of 2')],
        [cell(Labels::EXP_TITLE, 'Backend Developer')]
    );

    // Never labelled 'other' on a hunch: the generator knows what it rendered,
    // and a page footer it did not render is simply unknown.
    expect($result['gold_labels'])->toBe([Labels::EXP_TITLE, null])
        ->and($result['unmatched_lines'])->toBe(1)
        ->and($result['coverage'])->toBe(0.5);
});

it('ignores bullet glyphs and punctuation when comparing', function () {
    $result = (new ExampleBuilder())->align(
        [line('• Built reporting endpoints for the finance team.')],
        [cell(Labels::EXP_BULLET, 'Built reporting endpoints for the finance team')]
    );

    expect($result['gold_labels'])->toBe([Labels::EXP_BULLET]);
});

it('reports zero coverage for an empty document rather than dividing by zero', function () {
    $result = (new ExampleBuilder())->align([], [cell(Labels::EXP_TITLE, 'Backend Developer')]);

    expect($result['coverage'])->toBe(0.0)
        ->and($result['gold_labels'])->toBe([])
        ->and($result['unconsumed_cells'])->toBe(1);
});

it('masks a blank line without consuming a rendered cell', function () {
    $result = (new ExampleBuilder())->align(
        [line('   '), line('Backend Developer')],
        [cell(Labels::EXP_TITLE, 'Backend Developer')]
    );

    expect($result['gold_labels'])->toBe([null, Labels::EXP_TITLE]);
});
