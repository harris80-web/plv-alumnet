<?php

namespace App\Console\Commands;

use App\Models\ResumeParserModel;
use App\Services\ResumeParser\PerceptronModel;
use App\Services\ResumeParser\ResumeLineClassifier;
use App\Services\ResumeParser\ResumeLineFeaturizer;
use App\Services\ResumeParser\ResumeStructureAssembler;
use Illuminate\Console\Command;

/**
 * Runs a PDF through the whole parser and prints the fields the resume wizard
 * would be filled with.
 *
 * Exists so the pipeline can be judged on real resumes before anything is wired
 * into the import screen: this writes nothing and touches no account.
 */
class TryResumeImport extends Command
{
    protected $signature = 'resume:try-import
                            {file : path to a PDF}
                            {--labels : also print the label assigned to every line}
                            {--json : print the raw payload instead}';

    protected $description = 'Parse a resume PDF and show the fields that would be imported';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! is_file($path)) {
            $this->error("No such file: {$path}");

            return self::FAILURE;
        }

        $record = ResumeParserModel::active();

        if ($record === null) {
            $this->error('No active model. Run resume:generate-training-data then resume:train-parser.');

            return self::FAILURE;
        }

        $featurizer = new ResumeLineFeaturizer();

        try {
            $extracted = $featurizer->fromFile($path);
        } catch (\Throwable $e) {
            $this->error('Could not read that PDF: ' . $e->getMessage());

            return self::FAILURE;
        }

        $classifier = new ResumeLineClassifier(PerceptronModel::fromArray($record->weights), $featurizer);
        $decoded = $classifier->decode($extracted['lines'], $extracted['mode']);

        $assembled = (new ResumeStructureAssembler())->assemble(
            $extracted['lines'],
            $decoded['labels'],
            $decoded['margins'],
            $extracted['raw_text'],
        );

        if ($this->option('json')) {
            $this->line(json_encode($assembled['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->line("model v{$record->version}   extraction: {$extracted['mode']}   lines: " . count($extracted['lines']));

        if ($this->option('labels')) {
            $this->newLine();
            foreach ($extracted['lines'] as $index => $line) {
                $this->line(sprintf('  %-22s %-6.1f | %s',
                    $decoded['labels'][$index] ?? '?',
                    $decoded['margins'][$index] ?? 0,
                    mb_substr($line->text, 0, 62)
                ));
            }
        }

        $this->report($assembled['payload']);

        return self::SUCCESS;
    }

    private function report(array $payload): void
    {
        $this->newLine();
        $this->line('<comment>SUMMARY</comment>');
        $this->line('  ' . ($payload['resume_summary'] ?? '(none found)'));

        $this->newLine();
        $this->line('<comment>LINKEDIN</comment>');
        $this->line('  ' . ($payload['linkedin_url'] ?? '(none found)'));

        $this->newLine();
        $this->line('<comment>SKILLS (' . count($payload['skills']) . ')</comment>');
        if ($payload['skills'] === []) {
            $this->line('  (none found)');
        } else {
            $rows = array_map(
                // A null category is deliberate: it means the skill is not in the
                // skills table, and save() supplies the default.
                fn ($skill) => [$skill['name'], $skill['category'] ?? '— (new skill)'],
                $payload['skills']
            );
            $this->table(['skill', 'category'], $rows);
        }

        $this->newLine();
        $this->line('<comment>EXPERIENCES (' . count($payload['experiences']) . ')</comment>');
        if ($payload['experiences'] === []) {
            $this->line('  (none found)');
        } else {
            foreach ($payload['experiences'] as $i => $experience) {
                $dates = $experience['start_date'] === null
                    ? '(no dates)'
                    : $experience['start_date'] . ' to ' . ($experience['is_ongoing'] ? 'present' : ($experience['end_date'] ?? '?'));

                $this->line(sprintf('  %d. [%s] %s', $i + 1, $experience['type'], $experience['job_title']));
                $this->line(sprintf('     %s   duration: %s   industry_id: %s',
                    $dates,
                    $experience['duration_months'] === null ? '—' : $experience['duration_months'] . ' mo',
                    $experience['industry_id'] ?? '—'
                ));

                foreach (explode("\n", (string) $experience['job_description']) as $duty) {
                    if (trim($duty) !== '') {
                        $this->line('     - ' . mb_substr($duty, 0, 70));
                    }
                }
            }
        }

        $this->newLine();
        $this->line('<comment>CERTIFICATIONS (' . count($payload['certifications']) . ')</comment>');
        if ($payload['certifications'] === []) {
            $this->line('  (none found)');
        } else {
            $this->table(
                ['type', 'name', 'from', 'date'],
                array_map(fn ($c) => [
                    $c['certification_type'],
                    mb_substr($c['certification_name'], 0, 42),
                    mb_substr((string) $c['certification_from'], 0, 30) ?: '—',
                    $c['certification_date'] ?? '—',
                ], $payload['certifications'])
            );
        }
    }
}
