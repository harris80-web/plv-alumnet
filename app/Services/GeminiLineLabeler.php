<?php

namespace App\Services;

use App\Services\ResumeParser\Labels;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Developer-run tool that labels resume lines using Gemini, to draft ground
 * truth for evaluating the local parser.
 *
 * Never called when a user imports a resume — that path is entirely local. This
 * exists so a batch of real resumes can be turned into a reference set without
 * hand-tagging every line, and so the LLM can be reported as the accuracy
 * ceiling the local model is measured against.
 *
 * Deliberately labels *lines* rather than producing a finished resume, unlike
 * GeminiResumeParser. Labels are what the local model predicts, so a comparison
 * needs a reference in that same form; asking for structured fields instead
 * would force a lossy mapping back onto lines.
 *
 * Text handed to this class must already be redacted — see LineRedactor. Nothing
 * here checks that, because redaction is the caller's decision to make
 * deliberately rather than something to be silently assumed.
 */
class GeminiLineLabeler
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    /** Lines per request. Enough for a whole resume, small enough to stay reliable. */
    private const BATCH_SIZE = 60;

    /** Transient server-side conditions worth waiting out rather than failing on. */
    private const RETRYABLE_STATUSES = [429, 500, 502, 503, 504];

    private const MAX_ATTEMPTS = 5;

    /** Doubles each attempt, so waits run 2s, 4s, 8s, 16s. */
    private const RETRY_BASE_SECONDS = 2;

    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.key');
        $this->model = (string) config('services.gemini.model', 'gemini-flash-latest');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @param  array<int,string>  $redactedLines  keyed by line index
     * @return array<int,?string> label per line index; null where unlabelled
     *
     * @throws RuntimeException
     */
    public function label(array $redactedLines): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $labels = [];

        // Batched with the original indexes preserved, so a long resume does not
        // degrade into guesswork and every answer still maps to its own line.
        foreach (array_chunk($redactedLines, self::BATCH_SIZE, true) as $batch) {
            foreach ($this->labelBatch($batch) as $index => $label) {
                $labels[$index] = $label;
            }
        }

        foreach (array_keys($redactedLines) as $index) {
            $labels[$index] ??= null;
        }

        ksort($labels);

        return $labels;
    }

    /**
     * @param  array<int,string>  $batch
     * @return array<int,?string>
     */
    private function labelBatch(array $batch): array
    {
        $payload = [
            'contents' => [[
                'parts' => [['text' => $this->buildPrompt($batch)]],
            ]],
            'generationConfig' => [
                // Low temperature: this is a classification task with one
                // defensible answer per line, not a creative one.
                'temperature' => 0.0,
                'responseMimeType' => 'application/json',
                'responseSchema' => $this->responseSchema(),
            ],
        ];

        $response = null;

        // The free tier returns 503 under load often enough that a single attempt
        // fails most of a batch run. Backing off turns that into a slow success
        // rather than a lost file.
        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
                ->timeout(60)
                ->post(sprintf(self::ENDPOINT, $this->model), $payload);

            if ($response->successful() || ! in_array($response->status(), self::RETRYABLE_STATUSES, true)) {
                break;
            }

            if ($attempt < self::MAX_ATTEMPTS) {
                sleep(self::RETRY_BASE_SECONDS * (2 ** ($attempt - 1)));
            }
        }

        if ($response === null || ! $response->successful()) {
            throw new RuntimeException(
                'Gemini returned HTTP ' . ($response?->status() ?? 0) . ' after ' . self::MAX_ATTEMPTS
                . ' attempts: ' . mb_substr($response?->body() ?? '', 0, 200)
            );
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini returned no content.');
        }

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Gemini returned unparseable JSON.');
        }

        return $this->sanitize($decoded, array_keys($batch));
    }

    /**
     * The response is untrusted: a label outside the known set, or an index for a
     * line that was not sent, is discarded rather than stored.
     *
     * @param  array<int,int>  $validIndexes
     * @return array<int,?string>
     */
    private function sanitize(array $decoded, array $validIndexes): array
    {
        $allowed = array_flip(Labels::ALL);
        $valid = array_flip($validIndexes);
        $out = [];

        foreach ($decoded as $row) {
            if (! is_array($row)) {
                continue;
            }

            $index = $row['line'] ?? null;
            $label = $row['label'] ?? null;

            if (! is_numeric($index) || ! isset($valid[(int) $index])) {
                continue;
            }

            $out[(int) $index] = (is_string($label) && isset($allowed[$label])) ? $label : null;
        }

        return $out;
    }

    /** @param array<int,string> $batch */
    private function buildPrompt(array $batch): string
    {
        $numbered = [];

        foreach ($batch as $index => $text) {
            $numbered[] = $index . ': ' . $text;
        }

        // Written to match the assembler's own reading of these labels, so the
        // reference set and the local model are judged against one definition.
        return <<<PROMPT
        You are labelling the lines of a resume that has been converted to text. Assign exactly one label to every line.

        Labels for section headings (the heading itself, not its contents):
        header_summary, header_skills, header_experience, header_projects,
        header_certifications, header_education, header_contact, header_other

        Labels for content:
        doc_title     the person's name at the top of the resume
        contact       an email, phone, address, website or profile link
        summary_text  prose from a summary, objective or profile section
        skill_line    a skill, a list of skills, or a skills category label
        exp_title     a job title or a project name
        exp_meta      the employer, location, or the dates of a job
        exp_bullet    a duty, responsibility or achievement under a job
        cert_name     the name of a certification, licence, training or seminar
        cert_meta     the issuing body or the date of a certification
        edu_line      a degree, school, or anything else in an education section
        other         page numbers, headers, footers, template boilerplate,
                      or anything that is not part of the person's resume

        Rules:
        - Use exp_title for the role and exp_meta for the company. If one line holds both, choose exp_title.
        - A line listing several skills separated by commas is one skill_line.
        - Text belonging to the template rather than the person (instructions,
          adverts, font notes, "Good luck on the job hunt") is other.
        - Placeholders such as [EMAIL], [PHONE], [NAME] and [ADDRESS] are redacted
          personal data. Label the line by the role it plays; usually contact.
        - Return every line number you were given, exactly once.

        Lines:
        {$this->join($numbered)}
        PROMPT;
    }

    private function join(array $lines): string
    {
        return implode("\n", $lines);
    }

    private function responseSchema(): array
    {
        return [
            'type' => 'ARRAY',
            'items' => [
                'type' => 'OBJECT',
                'properties' => [
                    'line' => ['type' => 'INTEGER'],
                    'label' => ['type' => 'STRING', 'enum' => Labels::ALL],
                ],
                'required' => ['line', 'label'],
            ],
        ];
    }
}
