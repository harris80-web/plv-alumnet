<?php

namespace App\Services;

use App\Models\Alumnus;
use App\Models\JobPosting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Semantic layer on top of JobMatchService's deterministic score — reads
 * the job posting's free-form description and the alumnus's resume summary/
 * experience text (things the structured skills/program/certifications
 * matching in JobMatchService can't see) and asks Gemini for a 0-100 fit
 * assessment plus a short human-readable reason. Same calling convention as
 * GeminiResumeParser/GeminiChatbotService (structured JSON via
 * responseSchema); only ever invoked from the job-matches:recompute command
 * against a shortlist of top deterministic candidates, never inline on a
 * web request, so a slow/failed Gemini call never blocks the alumnus.
 */
class JobMatchAiService
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
     * @return array{score: float, explanation: string}
     *
     * @throws RuntimeException on any failure — caller (the recompute
     *                          command) logs and skips, leaving the
     *                          deterministic score as the only ranking
     *                          signal for that pair.
     */
    public function assess(JobPosting $job, Alumnus $alumnus): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
            ->timeout(30)
            ->post(sprintf(self::ENDPOINT, $this->model), [
                'contents' => [[
                    'parts' => [['text' => $this->buildPrompt($job, $alumnus)]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'match_score' => ['type' => 'INTEGER'],
                            'explanation' => ['type' => 'STRING'],
                        ],
                        'required' => ['match_score', 'explanation'],
                    ],
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

        return [
            'score' => max(0.0, min(100.0, (float) ($decoded['match_score'] ?? 0))),
            'explanation' => mb_substr(trim((string) ($decoded['explanation'] ?? '')), 0, 400),
        ];
    }

    private function buildPrompt(JobPosting $job, Alumnus $alumnus): string
    {
        $jobDescription = trim(strip_tags((string) $job->job_posting_description));
        $requiredSkills = $job->skills->pluck('skill_name')->implode(', ') ?: '(none listed)';
        $targetPrograms = $job->programs->pluck('program_name')->implode(', ') ?: '(any program)';

        $resumeSummary = trim((string) $alumnus->alumnus_resume_summary) ?: '(no summary provided)';
        $alumnusSkills = $alumnus->skills->pluck('skill_name')->implode(', ') ?: '(none listed)';
        $programName = $alumnus->program->program_name ?? '(unspecified)';

        $experienceText = $alumnus->experiences
            ->map(function ($exp) {
                $duration = Alumnus::formatExperienceDuration($exp->experience_duration_months) ?? 'unspecified duration';
                $desc = trim((string) $exp->experience_job_description);

                return "- ({$exp->experience_type}) {$exp->experience_job_title}, {$duration}"
                    . ($desc !== '' ? ": {$desc}" : '');
            })
            ->implode("\n") ?: '(no experience or projects listed)';

        $certificationText = $alumnus->certifications
            ->map(fn ($c) => "- {$c->certification_name} ({$c->certification_type})")
            ->implode("\n") ?: '(none listed)';

        return <<<PROMPT
            You are an expert recruiter assessing how well a job candidate's resume
            fits a job posting. Go beyond exact keyword matching — credit adjacent or
            transferable skills, and judge whether the seniority implied by the
            candidate's experience fits what the job description is actually asking
            for, including requirements only mentioned in free-form text (not in the
            structured skill list below).

            JOB POSTING
            Title: {$job->job_posting_title}
            Company: {$job->job_posting_company}
            Employment type: {$job->job_posting_employment_type}
            Target program(s): {$targetPrograms}
            Listed required skills: {$requiredSkills}
            Full description:
            \"\"\"
            {$jobDescription}
            \"\"\"

            CANDIDATE
            Program: {$programName}
            Resume summary: {$resumeSummary}
            Listed skills: {$alumnusSkills}
            Experience & projects:
            {$experienceText}
            Certifications/seminars/trainings:
            {$certificationText}

            Return match_score as an integer 0-100 rating overall fit, and a 1-2
            sentence explanation addressed directly to the candidate (e.g. "Your
            experience with X lines up well with...") that helps them understand
            why this job was recommended. Do not invent skills or experience the
            candidate doesn't have.
            PROMPT;
    }
}
