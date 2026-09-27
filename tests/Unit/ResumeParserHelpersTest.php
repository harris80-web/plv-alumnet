<?php

use App\Services\ResumeParser\IndustryMatcher;
use App\Services\ResumeParser\ResumeDateParser;
use App\Services\ResumeParser\SkillGazetteer;

/**
 * Phase 2 helpers. All pure, all fed explicit data rather than the database, so
 * these are the cheapest place to pin down behaviour the assembler will rely on.
 */
function gazetteer(): SkillGazetteer
{
    return new SkillGazetteer([
        ['name' => 'PHP', 'category' => 'technical'],
        ['name' => 'Laravel', 'category' => 'technical'],
        ['name' => 'JavaScript', 'category' => 'technical'],
        ['name' => 'Git & GitHub', 'category' => 'tool'],
        ['name' => 'Microsoft Excel', 'category' => 'tool'],
        ['name' => 'Communication', 'category' => 'soft'],
    ]);
}

function matcher(): IndustryMatcher
{
    return new IndustryMatcher([
        ['id' => 1, 'name' => 'None'],
        ['id' => 2, 'name' => 'Technology'],
        ['id' => 3, 'name' => 'Business & Finance'],
        ['id' => 4, 'name' => 'Education'],
    ]);
}

// ───────────────────────────── dates ─────────────────────────────

it('reads a month-and-year range into real dates', function () {
    $result = (new ResumeDateParser())->parseRange('Jan 2020 - Mar 2022');

    expect($result['start_date'])->toBe('2020-01-01')
        ->and($result['end_date'])->toBe('2022-03-01')
        ->and($result['is_ongoing'])->toBeFalse()
        ->and($result['duration_months'])->toBe(26);
});

it('reads the date spellings resumes actually use', function () {
    $parser = new ResumeDateParser();

    expect($parser->parseRange('January 2020 – December 2021')['end_date'])->toBe('2021-12-01')
        ->and($parser->parseRange('Sept. 2019 to Aug 2020')['start_date'])->toBe('2019-09-01')
        ->and($parser->parseRange('03/2021 - 06/2022')['start_date'])->toBe('2021-03-01')
        ->and($parser->parseRange('2018 — 2020')['start_date'])->toBe('2018-01-01')
        ->and($parser->parseRange('2018 — 2020')['end_date'])->toBe('2020-01-01');
});

it('treats present, current and ongoing as an open-ended range', function () {
    $parser = new ResumeDateParser();

    foreach (['Jan 2023 - Present', 'Jan 2023 to Current', 'Jan 2023 – Ongoing'] as $text) {
        $result = $parser->parseRange($text);

        expect($result['start_date'])->toBe('2023-01-01')
            ->and($result['end_date'])->toBeNull()
            ->and($result['is_ongoing'])->toBeTrue()
            // Still supplies a duration: both wizards post a hidden
            // duration_months and save() falls back to it.
            ->and($result['duration_months'])->toBeGreaterThan(0);
    }
});

it('drops a reversed range rather than letting the save fail validation', function () {
    // save() validates end_date after_or_equal start_date, so returning this
    // pair would 422 the entire save instead of just losing two fields.
    expect((new ResumeDateParser())->parseRange('Mar 2022 - Jan 2020'))->toBeNull();
});

it('does not invent an end date for a lone date', function () {
    $result = (new ResumeDateParser())->parseRange('Graduated 2023');

    expect($result['start_date'])->toBe('2023-01-01')
        ->and($result['end_date'])->toBeNull()
        ->and($result['is_ongoing'])->toBeFalse()
        ->and($result['duration_months'])->toBeNull();
});

it('returns nothing when there is no date at all', function () {
    expect((new ResumeDateParser())->parseRange('Backend Developer'))->toBeNull()
        ->and((new ResumeDateParser())->parseSingle('Backend Developer'))->toBeNull();
});

it('does not read a year twice when it is part of a fuller date', function () {
    // 'Jan 2020' must not also register as the bare year 2020, which would make
    // a single date look like a range from January to January.
    $result = (new ResumeDateParser())->parseRange('Jan 2020');

    expect($result['end_date'])->toBeNull()->and($result['is_ongoing'])->toBeFalse();
});

it('keeps a derived duration inside the range the form accepts', function () {
    $result = (new ResumeDateParser())->parseRange('Jan 1920 - Jan 2020');

    // 1200 months of experience would be rejected by save()'s max:600.
    expect($result['duration_months'])->toBe(600);
});

it('reads a single certification date, defaulting a bare year to January', function () {
    $parser = new ResumeDateParser();

    expect($parser->parseSingle('AWS Certified — Mar 2026'))->toBe('2026-03-01')
        ->and($parser->parseSingle('TESDA NC II (2021)'))->toBe('2021-01-01');
});

// ───────────────────────────── skills ─────────────────────────────

it('canonicalizes a known skill and supplies its real category', function () {
    $result = gazetteer()->canonicalize('laravel');

    // The category is the whole point: both existing parsers omitted it and the
    // wizard filed every imported skill as Domain Knowledge.
    expect($result['name'])->toBe('Laravel')
        ->and($result['category'])->toBe('technical')
        ->and($result['matched'])->toBeTrue();
});

it('matches across the differences that never matter', function () {
    $gazetteer = gazetteer();

    expect($gazetteer->canonicalize('git and github')['name'])->toBe('Git & GitHub')
        ->and($gazetteer->canonicalize('GIT & GITHUB')['name'])->toBe('Git & GitHub')
        ->and($gazetteer->canonicalize('  microsoft   excel ')['name'])->toBe('Microsoft Excel');
});

it('tolerates a small typo in a long skill name', function () {
    expect(gazetteer()->canonicalize('Javscript')['name'])->toBe('JavaScript');
});

it('keeps an unknown skill but assigns no category', function () {
    $result = gazetteer()->canonicalize('Kubernetes');

    expect($result['name'])->toBe('Kubernetes')
        ->and($result['category'])->toBeNull()
        ->and($result['matched'])->toBeFalse();
});

it('does not fuzzy-match a short name into the wrong skill', function () {
    // 'PHP' is three characters; nudging it must not land on anything.
    expect(gazetteer()->canonicalize('PHX')['matched'])->toBeFalse();
});

it('recovers individual skills glued together during extraction', function () {
    // Observed for real: two skills positioned close enough that no gap is
    // detectable come back as one line.
    $found = array_column(gazetteer()->matchWithin('Laravel JavaScript PHP'), 'name');

    expect($found)->toContain('Laravel')->toContain('JavaScript')->toContain('PHP');
});

it('prefers the longest skill when one name contains another', function () {
    $found = array_column(gazetteer()->matchWithin('Experienced with Git & GitHub daily'), 'name');

    expect($found)->toContain('Git & GitHub');
});

// ───────────────────────────── industries ─────────────────────────────

it('matches an industry by name', function () {
    expect(matcher()->match('Worked in the technology sector'))->toBe(2);
});

it('matches a compound industry name on its distinctive word', function () {
    expect(matcher()->match('Prepared monthly finance reports'))->toBe(3);
});

it('never matches the None industry inside an ordinary word', function () {
    // The bug this class exists to avoid: str_contains('telephone', 'none').
    expect(matcher()->match('Answered the telephone and logged calls'))->toBeNull();
});

it('returns nothing when no industry is mentioned', function () {
    expect(matcher()->match('Assisted with daily tasks'))->toBeNull()
        ->and(matcher()->match(''))->toBeNull();
});
