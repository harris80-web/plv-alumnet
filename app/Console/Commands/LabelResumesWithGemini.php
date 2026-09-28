<?php

namespace App\Console\Commands;

use App\Services\GeminiLineLabeler;
use App\Services\ResumeParser\LineRedactor;
use App\Services\ResumeParser\ResumeLineFeaturizer;
use Illuminate\Console\Command;

/**
 * Drafts a reference set for evaluation: labels every line of each resume in a
 * folder and writes the result beside it for a human to correct.
 *
 * Developer-run only. Personal data is masked before anything is sent, and the
 * sidecar written locally keeps the real text so the draft can actually be
 * reviewed — that file never leaves the machine and belongs in an ignored folder.
 *
 * Nothing produced here is ever trained on. It exists to measure the local
 * parser, and a reference set that had been trained on would measure nothing.
 */
class LabelResumesWithGemini extends Command
{
    protected $signature = 'resume:label-with-gemini
                            {dir : folder of PDFs to label}
                            {--dry-run : show exactly what would be sent, and send nothing}
                            {--limit=0 : only process this many files}
                            {--delay=4 : seconds to wait between files, to stay under the per-minute quota}
                            {--force : relabel files that already have a draft}';

    protected $description = 'Draft line labels for real resumes with Gemini, for evaluating the local parser';

    public function handle(): int
    {
        $dir = rtrim($this->argument('dir'), '/\\');

        if (! is_dir($dir)) {
            $this->error("No such folder: {$dir}");

            return self::FAILURE;
        }

        $files = glob($dir . '/*.pdf') ?: [];
        $limit = (int) $this->option('limit');

        if ($limit > 0) {
            $files = array_slice($files, 0, $limit);
        }

        if ($files === []) {
            $this->error('No PDFs found there.');

            return self::FAILURE;
        }

        $featurizer = new ResumeLineFeaturizer();
        $redactor = new LineRedactor();
        $labeler = new GeminiLineLabeler();

        if (! $this->option('dry-run') && ! $labeler->isConfigured()) {
            $this->error('GEMINI_API_KEY is not set.');

            return self::FAILURE;
        }

        $labelled = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $name = basename($file);
            $target = $this->sidecarPath($file);

            if (! $this->option('force') && is_file($target)) {
                $this->line("  skip (already drafted): {$name}");
                $skipped++;
                continue;
            }

            try {
                $extracted = $featurizer->fromFile($file);
            } catch (\Throwable $e) {
                $this->warn("  unreadable: {$name} — " . $e->getMessage());
                continue;
            }

            $lines = $extracted['lines'];
            $names = $redactor->guessNames($lines, $name);
            $redacted = $redactor->redactLines($lines, $names);

            if ($this->option('dry-run')) {
                $this->showDryRun($name, $extracted, $redacted, count($names));
                continue;
            }

            try {
                $labels = $labeler->label($redacted);
            } catch (\Throwable $e) {
                $this->error("  failed: {$name} — " . $e->getMessage());
                continue;
            }

            $this->writeSidecar($target, $file, $extracted, $lines, $redacted, $labels);

            $assigned = count(array_filter($labels, fn ($l) => $l !== null));
            $this->info(sprintf('  %-38s %d/%d lines labelled', mb_substr($name, 0, 36), $assigned, count($lines)));
            $labelled++;

            // The free tier limits requests per minute as well as per day, and a
            // whole folder sent back to back trips the former immediately.
            $delay = (int) $this->option('delay');
            if ($delay > 0) {
                sleep($delay);
            }
        }

        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('Dry run — nothing was sent.');

            return self::SUCCESS;
        }

        $this->info("Drafted {$labelled} file(s)" . ($skipped ? ", skipped {$skipped}" : '') . '.');
        $this->line('Review the .labels.json sidecars and correct any wrong label, then run resume:eval-parser --real.');

        return self::SUCCESS;
    }

    private function showDryRun(string $name, array $extracted, array $redacted, int $nameCount): void
    {
        $this->newLine();
        $this->line("<comment>{$name}</comment>  ({$extracted['mode']}, " . count($redacted) . " lines, {$nameCount} name words masked)");
        $this->line('  This is exactly what would be sent:');

        foreach (array_slice($redacted, 0, 14, true) as $index => $text) {
            $this->line(sprintf('    %3d | %s', $index, mb_substr($text, 0, 66)));
        }

        if (count($redacted) > 14) {
            $this->line('    ... ' . (count($redacted) - 14) . ' more lines');
        }
    }

    /**
     * @param  array<int,\App\Services\ResumeParser\Line>  $lines
     * @param  array<int,string>  $redacted
     * @param  array<int,?string>  $labels
     */
    private function writeSidecar(string $target, string $file, array $extracted, array $lines, array $redacted, array $labels): void
    {
        $rows = [];

        foreach ($lines as $index => $line) {
            $rows[] = [
                'line' => $index,
                // The real text, for review. Local only, in an ignored folder.
                'text' => $line->text,
                'sent_as' => $redacted[$index] ?? '',
                'label' => $labels[$index] ?? null,
                'reviewed' => false,
            ];
        }

        file_put_contents($target, json_encode([
            'source' => basename($file),
            'extraction_mode' => $extracted['mode'],
            'drafted_by' => 'gemini:' . config('services.gemini.model'),
            'drafted_at' => now()->toDateTimeString(),
            // Stated so the workflow is disclosed wherever these numbers are used.
            'note' => 'Draft labels from an LLM over redacted text. Correct any wrong label, then set reviewed to true. Never used for training.',
            'labels' => $rows,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function sidecarPath(string $file): string
    {
        return dirname($file) . '/' . pathinfo($file, PATHINFO_FILENAME) . '.labels.json';
    }
}
