<?php

namespace App\Services\ResumeParser;

use App\Models\ResumeParse;
use App\Models\ResumeParserModel;
use App\Services\ResumeTextParser;
use Illuminate\Support\Facades\Log;

/**
 * Entry point for resume import: an uploaded PDF in, the wizard's prefill array
 * out, plus a stored record of what happened.
 *
 * Runs entirely locally — no API call, no network. The previous implementation
 * called Google Gemini on every import and fell back to a regex parser; this
 * replaces the first and keeps the second, though only as a safety net.
 */
class ResumeParseService
{
    /** Top-level keys that the heuristic parser is allowed to backfill when empty. */
    private const BACKFILLABLE = ['skills', 'experiences', 'certifications'];

    public function __construct(
        private ResumeLineFeaturizer $featurizer = new ResumeLineFeaturizer(),
        private ResumeStructureAssembler $assembler = new ResumeStructureAssembler(),
    ) {
    }

    /**
     * @return array{payload: array<string,mixed>, parse: ?ResumeParse, parsed_with: string}
     *
     * @throws \RuntimeException when the file yields no readable text at all
     */
    public function parse(string $path, int $alumnusId, ?string $filename = null): array
    {
        $extracted = $this->featurizer->fromFile($path);

        if ($extracted['lines'] === []) {
            throw new \RuntimeException('no readable text');
        }

        $record = ResumeParserModel::active();
        $model = $this->loadModel($record);

        if ($model === null) {
            // No usable model: fall back so importing keeps working rather than
            // failing outright. Availability, not accuracy.
            return [
                'payload' => $this->heuristic($extracted['raw_text']),
                'parse' => null,
                'parsed_with' => 'heuristic',
            ];
        }

        $decoded = (new ResumeLineClassifier($model, $this->featurizer))
            ->decode($extracted['lines'], $extracted['mode']);

        $assembled = $this->assembler->assemble(
            $extracted['lines'],
            $decoded['labels'],
            $decoded['margins'],
            $extracted['raw_text'],
        );

        [$payload, $parsedWith] = $this->backfill($assembled['payload'], $extracted['raw_text']);

        $parse = $this->record($record, $extracted, $decoded, $assembled, $payload, $alumnusId, $filename);

        return ['payload' => $payload, 'parse' => $parse, 'parsed_with' => $parsedWith];
    }

    private function loadModel(?ResumeParserModel $record): ?PerceptronModel
    {
        if ($record === null) {
            return null;
        }

        try {
            $model = PerceptronModel::fromArray($record->weights ?? []);
        } catch (\Throwable $e) {
            Log::warning('Resume parser weights could not be decoded: ' . $e->getMessage());

            return null;
        }

        // A model trained against a different label set cannot be scored against
        // today's code, so it is treated as unusable rather than misread.
        if ($model->weightCount() === 0 || $model->labels() !== Labels::ALL) {
            Log::warning('Resume parser model v' . $record->version . ' is incompatible with the current label set.');

            return null;
        }

        return $model;
    }

    /**
     * Fills only the top-level keys the model left completely empty, from the
     * old heuristic parser.
     *
     * Bounded deliberately: it cannot make a populated field worse, and it
     * reports itself, so the effect is measurable. No attempt is made to
     * arbitrate between two whole parser outputs by some confidence score —
     * that would be unmeasurable and would make the same resume behave
     * differently from one run to the next.
     *
     * @return array{0:array<string,mixed>,1:string}
     */
    private function backfill(array $payload, string $rawText): array
    {
        $missing = [];

        foreach (self::BACKFILLABLE as $key) {
            if (($payload[$key] ?? []) === []) {
                $missing[] = $key;
            }
        }

        if (($payload['resume_summary'] ?? null) === null) {
            $missing[] = 'resume_summary';
        }

        if ($missing === []) {
            return [$payload, 'model'];
        }

        try {
            $heuristic = (new ResumeTextParser())->parse($rawText);
        } catch (\Throwable $e) {
            return [$payload, 'model'];
        }

        $filled = false;

        foreach ($missing as $key) {
            $value = $heuristic[$key] ?? null;

            if ($value !== null && $value !== []) {
                $payload[$key] = $value;
                $filled = true;
            }
        }

        return [$payload, $filled ? 'model+heuristic' : 'model'];
    }

    private function heuristic(string $rawText): array
    {
        $parsed = (new ResumeTextParser())->parse($rawText);

        // The heuristic parser predates the date columns and never emits them,
        // so the keys are normalised here rather than leaving the wizard to
        // cope with a different shape depending on which path ran.
        foreach ($parsed['experiences'] ?? [] as $index => $experience) {
            $parsed['experiences'][$index] += [
                'start_date' => null,
                'end_date' => null,
                'is_ongoing' => false,
            ];
        }

        return $parsed;
    }

    private function record(
        ResumeParserModel $model,
        array $extracted,
        array $decoded,
        array $assembled,
        array $payload,
        int $alumnusId,
        ?string $filename,
    ): ?ResumeParse {
        try {
            return ResumeParse::create([
                'alumnus_id' => $alumnusId,
                'resume_parser_model_id' => $model->resume_parser_model_id,
                'source_filename' => $filename,
                'source_page_count' => $extracted['page_count'],
                'extraction_mode' => $extracted['mode'],
                // Personal data. Kept because a correction can only be traced
                // back to specific lines with the source in hand; pruned by
                // retention, and the upload notice says so.
                'raw_text' => $extracted['raw_text'],
                'lines' => array_map(fn (Line $line) => $line->toArray(), $extracted['lines']),
                'predicted_labels' => $decoded['labels'],
                'predicted_margins' => $decoded['margins'],
                'predicted_payload' => $payload,
                'provenance' => $assembled['provenance'],
            ]);
        } catch (\Throwable $e) {
            // The import itself must not fail because the audit row could not
            // be written; the alumnus still gets their fields.
            Log::warning('Could not record resume parse: ' . $e->getMessage());

            return null;
        }
    }
}
