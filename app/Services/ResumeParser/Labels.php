<?php

namespace App\Services\ResumeParser;

/**
 * The label set the classifier predicts, one label per extracted line.
 *
 * Section headings carry which section they open, because "is this a heading?"
 * is a typographic question the model is good at while "which heading is it?"
 * is a keyword lookup the same features already solve — so folding the two
 * together costs almost nothing and avoids a second model.
 *
 * Content labels deliberately do NOT encode their section. Whether a title is
 * work or a project is carried as state by the assembler instead, because that
 * distinction usually is not recoverable from the line's own text.
 */
class Labels
{
    public const HEADER_SUMMARY = 'header_summary';
    public const HEADER_SKILLS = 'header_skills';
    public const HEADER_EXPERIENCE = 'header_experience';
    public const HEADER_PROJECTS = 'header_projects';
    public const HEADER_CERTIFICATIONS = 'header_certifications';
    public const HEADER_EDUCATION = 'header_education';
    public const HEADER_CONTACT = 'header_contact';
    public const HEADER_OTHER = 'header_other';

    public const DOC_TITLE = 'doc_title';
    public const CONTACT = 'contact';
    public const SUMMARY_TEXT = 'summary_text';
    public const SKILL_LINE = 'skill_line';
    public const EXP_TITLE = 'exp_title';
    public const EXP_META = 'exp_meta';
    public const EXP_BULLET = 'exp_bullet';
    public const CERT_NAME = 'cert_name';
    public const CERT_META = 'cert_meta';

    public const EDU_LINE = 'edu_line';
    public const OTHER = 'other';

    /** Ordered, and persisted with every trained model: weights are indexed by position here. */
    public const ALL = [
        self::HEADER_SUMMARY,
        self::HEADER_SKILLS,
        self::HEADER_EXPERIENCE,
        self::HEADER_PROJECTS,
        self::HEADER_CERTIFICATIONS,
        self::HEADER_EDUCATION,
        self::HEADER_CONTACT,
        self::HEADER_OTHER,
        self::DOC_TITLE,
        self::CONTACT,
        self::SUMMARY_TEXT,
        self::SKILL_LINE,
        self::EXP_TITLE,
        self::EXP_META,
        self::EXP_BULLET,
        self::CERT_NAME,
        self::CERT_META,
        self::EDU_LINE,
        self::OTHER,
    ];

    /** Which section each heading label opens, for the assembler's running state. */
    public const HEADER_SECTIONS = [
        self::HEADER_SUMMARY => 'summary',
        self::HEADER_SKILLS => 'skills',
        self::HEADER_EXPERIENCE => 'experience',
        self::HEADER_PROJECTS => 'projects',
        self::HEADER_CERTIFICATIONS => 'certifications',
        self::HEADER_EDUCATION => 'education',
        self::HEADER_CONTACT => 'contact',
        self::HEADER_OTHER => 'other',
    ];

    public static function count(): int
    {
        return count(self::ALL);
    }

    public static function index(string $label): ?int
    {
        $position = array_search($label, self::ALL, true);

        return $position === false ? null : $position;
    }

    public static function isHeader(string $label): bool
    {
        return isset(self::HEADER_SECTIONS[$label]);
    }

    public static function sectionFor(string $label): ?string
    {
        return self::HEADER_SECTIONS[$label] ?? null;
    }

    /** The heading label for a section key, used when generating training data. */
    public static function headerFor(string $section): string
    {
        return array_search($section, self::HEADER_SECTIONS, true) ?: self::HEADER_OTHER;
    }
}
