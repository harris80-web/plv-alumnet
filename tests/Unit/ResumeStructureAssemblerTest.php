<?php

use App\Services\ResumeParser\IndustryMatcher;
use App\Services\ResumeParser\Labels;
use App\Services\ResumeParser\Line;
use App\Services\ResumeParser\ResumeDateParser;
use App\Services\ResumeParser\ResumeStructureAssembler;
use App\Services\ResumeParser\SkillGazetteer;

/**
 * Turning labelled lines into form fields. Fed explicit labels rather than a PDF,
 * so these cover the assembly rules alone and stay fast.
 */
function assembler(): ResumeStructureAssembler
{
    return new ResumeStructureAssembler(
        new ResumeDateParser(),
        new SkillGazetteer([
            ['name' => 'PHP', 'category' => 'technical'],
            ['name' => 'Laravel', 'category' => 'technical'],
            ['name' => 'JavaScript', 'category' => 'technical'],
            ['name' => 'Git & GitHub', 'category' => 'tool'],
        ]),
        new IndustryMatcher([
            ['id' => 1, 'name' => 'None'],
            ['id' => 2, 'name' => 'Technology'],
        ]),
    );
}

/**
 * @param  array<int,array{0:string,1:string}>  $pairs  [label, text]
 * @return array{0:array<int,Line>,1:array<int,string>,2:array<int,float>}
 */
function labelled(array $pairs, array $margins = []): array
{
    $lines = [];
    $labels = [];
    $out = [];

    foreach ($pairs as $i => [$label, $text]) {
        $lines[$i] = new Line(
            text: $text, page: 1, x: 10, y: 500 - ($i * 12), size: 10,
            bold: false, italic: false, gapAbove: 12, rowKey: $i, fragmentIndex: 0, fragmentCount: 1,
        );
        $labels[$i] = $label;
        $out[$i] = $margins[$i] ?? 20.0;
    }

    return [$lines, $labels, $out];
}

it('emits exactly the keys the resume wizard prefills from', function () {
    [$lines, $labels, $margins] = labelled([[Labels::DOC_TITLE, 'MARIA REYES']]);

    $payload = assembler()->assemble($lines, $labels, $margins)['payload'];

    // Must stay in step with Alumnus::toResumeFormArray() minus
    // resume_completeness: the wizard and the job-apply modal both prefill from
    // that same shape, so a rename here breaks page load, not just import.
    expect(array_keys($payload))->toBe([
        'resume_summary', 'linkedin_url', 'skills', 'experiences', 'certifications',
    ]);
});

it('groups a title with the metadata and bullets beneath it', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_EXPERIENCE, 'WORK EXPERIENCE'],
        [Labels::EXP_TITLE, 'Backend Developer'],
        [Labels::EXP_META, 'Acme Digital Inc.'],
        [Labels::EXP_META, 'Jan 2020 - Mar 2022'],
        [Labels::EXP_BULLET, 'Built reporting endpoints'],
        [Labels::EXP_BULLET, 'Tuned slow queries'],
    ]);

    $experiences = assembler()->assemble($lines, $labels, $margins)['payload']['experiences'];

    expect($experiences)->toHaveCount(1)
        ->and($experiences[0]['job_title'])->toBe('Backend Developer')
        ->and($experiences[0]['type'])->toBe('work')
        ->and($experiences[0]['start_date'])->toBe('2020-01-01')
        ->and($experiences[0]['end_date'])->toBe('2022-03-01')
        ->and($experiences[0]['duration_months'])->toBe(26)
        // Newline-joined because the wizard splits on newlines into one input
        // per duty, and Experience::jobDuties() does the same server-side.
        ->and($experiences[0]['job_description'])->toBe("Built reporting endpoints\nTuned slow queries");
});

it('files an entry as a project when the projects section is open', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_PROJECTS, 'PROJECTS'],
        [Labels::EXP_TITLE, 'Alumni Portal'],
        [Labels::EXP_BULLET, 'Built the directory module'],
    ]);

    $experiences = assembler()->assemble($lines, $labels, $margins)['payload']['experiences'];

    expect($experiences[0]['type'])->toBe('project')
        // Industry belongs to employment, not a personal project.
        ->and($experiences[0]['industry_id'])->toBeNull();
});

it('starts a new section when a heading appears, closing the open entry', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_EXPERIENCE, 'EXPERIENCE'],
        [Labels::EXP_TITLE, 'Backend Developer'],
        [Labels::HEADER_PROJECTS, 'PROJECTS'],
        [Labels::EXP_TITLE, 'Alumni Portal'],
    ]);

    $experiences = assembler()->assemble($lines, $labels, $margins)['payload']['experiences'];

    expect($experiences)->toHaveCount(2)
        ->and($experiences[0]['type'])->toBe('work')
        ->and($experiences[1]['type'])->toBe('project');
});

it('reads an open-ended range as ongoing', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_EXPERIENCE, 'EXPERIENCE'],
        [Labels::EXP_TITLE, 'Backend Developer'],
        [Labels::EXP_META, 'Aug 2020 - Present'],
    ]);

    $experience = assembler()->assemble($lines, $labels, $margins)['payload']['experiences'][0];

    expect($experience['start_date'])->toBe('2020-08-01')
        ->and($experience['end_date'])->toBeNull()
        ->and($experience['is_ongoing'])->toBeTrue();
});

it('separates a duration that the app own export glues onto the title', function () {
    // resume-pdf.blade.php uses flexbox for this row, which dompdf ignores, so a
    // re-imported copy of our own export arrives with the two joined.
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_EXPERIENCE, 'WORK EXPERIENCE'],
        [Labels::EXP_TITLE, 'Junior Web Developer 8 mos'],
    ]);

    $experience = assembler()->assemble($lines, $labels, $margins)['payload']['experiences'][0];

    expect($experience['job_title'])->toBe('Junior Web Developer')
        ->and($experience['duration_months'])->toBe(8);
});

it('does not split one job into several on a hesitant title', function () {
    // The failure a user notices instantly. A low-confidence title directly
    // under an entry with no detail yet is treated as more metadata.
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_EXPERIENCE, 'EXPERIENCE'],
        [Labels::EXP_TITLE, 'Backend Developer'],
        [Labels::EXP_TITLE, 'Acme Digital Inc.'],
        [Labels::EXP_BULLET, 'Built reporting endpoints'],
    ], [2 => 0.4]);

    $experiences = assembler()->assemble($lines, $labels, $margins)['payload']['experiences'];

    expect($experiences)->toHaveCount(1)
        ->and($experiences[0]['job_title'])->toBe('Backend Developer');
});

it('rejoins a bullet the renderer wrapped onto a second line', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_EXPERIENCE, 'EXPERIENCE'],
        [Labels::EXP_TITLE, 'Backend Developer'],
        [Labels::EXP_BULLET, '• Built reporting endpoints used by the finance'],
        [Labels::EXP_BULLET, 'team every month'],
        [Labels::EXP_BULLET, '• Tuned slow queries'],
    ]);

    $description = assembler()->assemble($lines, $labels, $margins)['payload']['experiences'][0]['job_description'];

    expect($description)->toBe("Built reporting endpoints used by the finance team every month\nTuned slow queries");
});

it('canonicalizes skills and supplies the category the wizard used to hardcode', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_SKILLS, 'SKILLS'],
        [Labels::SKILL_LINE, 'php, laravel, git and github'],
    ]);

    $skills = assembler()->assemble($lines, $labels, $margins)['payload']['skills'];

    expect($skills)->toBe([
        ['name' => 'PHP', 'category' => 'technical'],
        ['name' => 'Laravel', 'category' => 'technical'],
        ['name' => 'Git & GitHub', 'category' => 'tool'],
    ]);
});

it('leaves an unknown skill without a category rather than guessing', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_SKILLS, 'SKILLS'],
        [Labels::SKILL_LINE, 'Kubernetes'],
    ]);

    $skills = assembler()->assemble($lines, $labels, $margins)['payload']['skills'];

    // save()'s firstOrCreate default and the wizard both supply one.
    expect($skills)->toBe([['name' => 'Kubernetes', 'category' => null]]);
});

it('does not import a skills category heading as a skill', function () {
    // A grouped skills block prints its category label on the same line.
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_SKILLS, 'SKILLS'],
        [Labels::SKILL_LINE, 'Tools & Software'],
        [Labels::SKILL_LINE, 'Git & GitHub'],
    ]);

    $skills = assembler()->assemble($lines, $labels, $margins)['payload']['skills'];

    expect(array_column($skills, 'name'))->toBe(['Git & GitHub']);
});

it('rejects sentence-shaped text that a template page put in the skills list', function () {
    // Downloaded templates ship their own instructions, which are formatted
    // exactly like content and so get labelled the same way.
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_SKILLS, 'SKILLS'],
        [Labels::SKILL_LINE, 'Ready to land your next job offer?'],
        [Labels::SKILL_LINE, 'Cover Letter Generator →'],
        [Labels::SKILL_LINE, 'In addition to this Word template you also get'],
        [Labels::SKILL_LINE, 'and French'],
        [Labels::SKILL_LINE, 'Laravel'],
    ]);

    $skills = assembler()->assemble($lines, $labels, $margins)['payload']['skills'];

    expect(array_column($skills, 'name'))->toBe(['Laravel']);
});

it('recovers skills that extraction glued together', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_SKILLS, 'SKILLS'],
        [Labels::SKILL_LINE, 'Laravel JavaScript'],
    ]);

    $skills = assembler()->assemble($lines, $labels, $margins)['payload']['skills'];

    expect(array_column($skills, 'name'))->toBe(['Laravel', 'JavaScript']);
});

it('splits a certification from its issuer and reads its date', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_CERTIFICATIONS, 'CERTIFICATIONS'],
        [Labels::CERT_NAME, 'AWS Cloud Practitioner — Amazon Web Services (Certification) Mar 2026'],
    ]);

    $certifications = assembler()->assemble($lines, $labels, $margins)['payload']['certifications'];

    expect($certifications)->toBe([[
        'certification_type' => 'certification',
        'certification_name' => 'AWS Cloud Practitioner',
        'certification_from' => 'Amazon Web Services',
        // Only a month and year were printed, so the day is not recoverable.
        'certification_date' => '2026-03-01',
    ]]);
});

it('takes the issuer from a following line when there is one', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_CERTIFICATIONS, 'Trainings'],
        [Labels::CERT_NAME, 'Basic Occupational Safety and Health'],
        [Labels::CERT_META, 'DOLE, October 2025'],
    ]);

    $certification = assembler()->assemble($lines, $labels, $margins)['payload']['certifications'][0];

    expect($certification['certification_type'])->toBe('training')
        ->and($certification['certification_from'])->toBe('DOLE')
        ->and($certification['certification_date'])->toBe('2025-10-01');
});

it('prefers a type printed against the entry over one inferred from the heading', function () {
    // "Certifications & Trainings" covers both, so the entry's own marker wins.
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_CERTIFICATIONS, 'CERTIFICATIONS & TRAININGS'],
        [Labels::CERT_NAME, 'Classroom Management Seminar (Seminar)'],
    ]);

    $certification = assembler()->assemble($lines, $labels, $margins)['payload']['certifications'][0];

    expect($certification['certification_type'])->toBe('seminar');
});

it('reads the section from the heading text when the model names the wrong one', function () {
    // ML for whether a line is a heading; a keyword check for which heading it
    // is, because that part has an exact answer.
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_PROJECTS, 'WORK EXPERIENCE'],
        [Labels::EXP_TITLE, 'Backend Developer'],
    ]);

    $experience = assembler()->assemble($lines, $labels, $margins)['payload']['experiences'][0];

    expect($experience['type'])->toBe('work');
});

it('finds a linkedin address anywhere in the document', function () {
    [$lines, $labels, $margins] = labelled([[Labels::CONTACT, 'maria@example.com']]);

    $payload = assembler()->assemble($lines, $labels, $margins, 'Reach me at linkedin.com/in/maria-reyes or by email.')['payload'];

    expect($payload['linkedin_url'])->toBe('https://linkedin.com/in/maria-reyes');
});

it('ignores lines that have nowhere to go in this schema', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::DOC_TITLE, 'MARIA REYES'],
        [Labels::CONTACT, 'maria@example.com'],
        [Labels::HEADER_EDUCATION, 'EDUCATION'],
        [Labels::EDU_LINE, 'Bachelor of Science in Information Technology'],
        [Labels::OTHER, 'Page 1 of 2'],
    ]);

    $payload = assembler()->assemble($lines, $labels, $margins)['payload'];

    // They are recognised so they stop polluting skills, not so they are stored.
    expect($payload['skills'])->toBe([])
        ->and($payload['experiences'])->toBe([])
        ->and($payload['certifications'])->toBe([])
        ->and($payload['resume_summary'])->toBeNull();
});

it('keeps every value inside the limits the save endpoint enforces', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_SUMMARY, 'SUMMARY'],
        [Labels::SUMMARY_TEXT, str_repeat('long summary text ', 60)],
        [Labels::HEADER_EXPERIENCE, 'EXPERIENCE'],
        [Labels::EXP_TITLE, str_repeat('Very Long Job Title ', 20)],
        [Labels::EXP_BULLET, str_repeat('duty text ', 400)],
    ]);

    $payload = assembler()->assemble($lines, $labels, $margins)['payload'];

    // A validation error straight after import reads as a crash to the user.
    expect(mb_strlen($payload['resume_summary']))->toBeLessThanOrEqual(500)
        ->and(mb_strlen($payload['experiences'][0]['job_title']))->toBeLessThanOrEqual(150)
        ->and(mb_strlen($payload['experiences'][0]['job_description']))->toBeLessThanOrEqual(2000);
});

it('records which lines produced each field', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_EXPERIENCE, 'EXPERIENCE'],
        [Labels::EXP_TITLE, 'Backend Developer'],
        [Labels::EXP_BULLET, 'Built reporting endpoints'],
        [Labels::EXP_BULLET, 'Tuned slow queries'],
    ]);

    $provenance = assembler()->assemble($lines, $labels, $margins)['provenance'];

    // This is what lets a later correction be attributed to specific lines
    // instead of being guessed at by similarity.
    expect($provenance['experiences.0.job_title'])->toBe([1])
        ->and($provenance['experiences.0.job_description'])->toBe([2, 3]);
});

it('drops a bullet that arrives before any title rather than inventing an entry', function () {
    [$lines, $labels, $margins] = labelled([
        [Labels::HEADER_EXPERIENCE, 'EXPERIENCE'],
        [Labels::EXP_BULLET, 'Built reporting endpoints'],
    ]);

    expect(assembler()->assemble($lines, $labels, $margins)['payload']['experiences'])->toBe([]);
});
