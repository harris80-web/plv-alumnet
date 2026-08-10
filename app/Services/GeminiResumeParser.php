<?php

namespace App\Services;

use App\Models\Industry;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * AI-based counterpart to ResumeTextParser — same input (raw PDF text), same
 * output shape (matches Alumnus::toResumeFormArray()'s relevant keys), so
 * ResumeBuilderController::import() can use this when a Gemini API key is
 * configured and fall back to the heuristic parser otherwise/on failure.
 *
 * Uses Gemini's structured-output mode (responseSchema) so the model is
 * constrained to the shape we need rather than hoping it formats JSON
 * correctly on its own — but the output is still untrusted: everything is
 * capped/sanitized the same way ResumeTextParser's output is, and the same
 * validation in ResumeBuilderController::save() runs regardless of which
 * parser produced the data.
 */
class GeminiResumeParser
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.key');
        $this->model = (string) config('services.gemini.model', 'gemini-2.0-flash');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @throws RuntimeException on any failure — caller decides whether to
     *                          fall back to the heuristic parser.
     */
    public function parse(string $rawText): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $industryNames = Industry::orderBy('industry_name')->pluck('industry_name')->all();

        $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
            ->timeout(30)
            ->post(sprintf(self::ENDPOINT, $this->model), [
                'contents' => [[
                    'parts' => [['text' => $this->buildPrompt($rawText, $industryNames)]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $this->responseSchema(),
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gemini API request failed: ' . $response->status() . ' ' . $response->body());
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        if (!is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini returned an empty or unexpected response.');
        }

        $decoded = json_decode($text, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Gemini did not return valid JSON: ' . json_last_error_msg());
        }

        return $this->sanitize($decoded);
    }

    private function buildPrompt(string $rawText, array $industryNames): string
    {
        $industryList = implode(', ', $industryNames) ?: '(none available)';

        return <<<PROMPT
            You are an expert resume parser. Read the resume text below and extract
            the requested fields as JSON matching the provided schema exactly.

            Rules:
            - resume_summary: the professional summary/objective if present, otherwise null. Under 500 characters.
            - linkedin_url: a LinkedIn profile URL if mentioned, otherwise null.
            - skills: individual skill names, not sentences. Split comma/bullet lists into separate items.
            - experiences: both jobs/internships/freelance work (type "work") and personal/school projects (type "project").
              For each, estimate duration_months as a whole number from any dates mentioned (0 if unknown), and pick the
              single closest industry from this exact list, or null if none fit: {$industryList}
            - certifications: certifications, licenses, seminars, and trainings. certification_type must be exactly one
              of "certification", "seminar", or "training". certification_date as an ISO date (YYYY-MM-DD) if a
              year/date is found, else null.
            - If a field or section isn't present in the resume, use null or an empty array — do not invent data.

            Resume text:
            \"\"\"
            {$rawText}
            \"\"\"
            PROMPT;
    }

    private function responseSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'resume_summary' => ['type' => 'STRING', 'nullable' => true],
                'linkedin_url' => ['type' => 'STRING', 'nullable' => true],
                'skills' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => ['name' => ['type' => 'STRING']],
                        'required' => ['name'],
                    ],
                ],
                'experiences' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'type' => ['type' => 'STRING', 'enum' => ['work', 'project']],
                            'job_title' => ['type' => 'STRING'],
                            'job_description' => ['type' => 'STRING', 'nullable' => true],
                            'duration_months' => ['type' => 'INTEGER'],
                            'industry' => ['type' => 'STRING', 'nullable' => true],
                        ],
                        'required' => ['type', 'job_title', 'duration_months'],
                    ],
                ],
                'certifications' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'certification_type' => ['type' => 'STRING', 'enum' => ['certification', 'seminar', 'training']],
                            'certification_name' => ['type' => 'STRING'],
                            'certification_from' => ['type' => 'STRING', 'nullable' => true],
                            'certification_date' => ['type' => 'STRING', 'nullable' => true],
                        ],
                        'required' => ['certification_type', 'certification_name'],
                    ],
                ],
            ],
            'required' => ['skills', 'experiences', 'certifications'],
        ];
    }

    /** Same caps as the validation in ResumeBuilderController::save() — treat AI output as untrusted input. */
    private function sanitize(array $data): array
    {
        $industryIdsByLowerName = Industry::pluck('industry_id', 'industry_name')
            ->mapWithKeys(fn ($id, $name) => [mb_strtolower($name) => $id])
            ->all();

        $summary = $data['resume_summary'] ?? null;
        $linkedin = $data['linkedin_url'] ?? null;

        return [
            'resume_summary' => $summary ? mb_substr(trim((string) $summary), 0, 500) : null,
            'linkedin_url' => $linkedin ? mb_substr(trim((string) $linkedin), 0, 255) : null,

            'skills' => collect($data['skills'] ?? [])
                ->map(fn ($s) => trim((string) ($s['name'] ?? '')))
                ->filter(fn ($name) => $name !== '' && mb_strlen($name) <= 100)
                ->unique(fn ($name) => mb_strtolower($name))
                ->values()
                ->take(30)
                ->map(fn ($name) => ['name' => $name])
                ->all(),

            'experiences' => collect($data['experiences'] ?? [])
                ->filter(fn ($e) => !empty($e['job_title']))
                ->take(15)
                ->map(function ($e) use ($industryIdsByLowerName) {
                    $industryName = mb_strtolower(trim((string) ($e['industry'] ?? '')));

                    return [
                        'type' => in_array($e['type'] ?? null, ['work', 'project'], true) ? $e['type'] : 'work',
                        'job_title' => mb_substr(trim((string) $e['job_title']), 0, 150),
                        'job_description' => !empty($e['job_description'])
                            ? mb_substr(trim((string) $e['job_description']), 0, 2000)
                            : null,
                        'duration_months' => max(0, min(600, (int) ($e['duration_months'] ?? 0))),
                        'industry_id' => $industryIdsByLowerName[$industryName] ?? null,
                    ];
                })
                ->values()
                ->all(),

            'certifications' => collect($data['certifications'] ?? [])
                ->filter(fn ($c) => !empty($c['certification_name']))
                ->take(15)
                ->map(fn ($c) => [
                    'certification_type' => in_array($c['certification_type'] ?? null, ['certification', 'seminar', 'training'], true)
                        ? $c['certification_type']
                        : 'certification',
                    'certification_name' => mb_substr(trim((string) $c['certification_name']), 0, 150),
                    'certification_from' => !empty($c['certification_from'])
                        ? mb_substr(trim((string) $c['certification_from']), 0, 150)
                        : null,
                    'certification_date' => $this->normalizeDate($c['certification_date'] ?? null),
                ])
                ->values()
                ->all(),
        ];
    }

    private function normalizeDate(?string $date): ?string
    {
        if (!$date || !preg_match('/^(19|20)\d{2}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        return $date;
    }
}
