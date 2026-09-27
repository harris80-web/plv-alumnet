<?php

namespace App\Console\Commands;

use App\Models\ResumeParserExample;
use App\Models\ResumeParserModel;
use App\Services\ResumeParser\Labels;
use App\Services\ResumeParser\ResumeLineFeaturizer;
use App\Services\ResumeParser\Training\ParserEvaluator;
use App\Services\ResumeParser\Training\PerceptronTrainer;
use Illuminate\Console\Command;

/**
 * Trains a new model version from the accumulated corpus and decides whether it
 * is good enough to go live.
 *
 * Always trains from scratch. Applying single updates to an already-averaged
 * weight vector is not "more training" — it corrupts the average — and it cannot
 * unlearn a correction the user later revised. Retraining also makes a version
 * reproducible from the corpus state that produced it, which matters for being
 * able to explain any given model.
 */
class TrainResumeParser extends Command
{
    protected $signature = 'resume:train-parser
                            {--epochs=10}
                            {--seed=20260927}
                            {--sources=synthetic,gemini,correction}
                            {--correction-weight=3 : how many times a real correction counts}
                            {--exclude-features= : ablation, csv of lexical,typography,position,shape,history,section,gazetteer}
                            {--prune=0.01}
                            {--activate=auto : auto|always|never}
                            {--label= : note stored against the version}';

    protected $description = 'Train a new resume line classifier from the labelled corpus';

    /** A candidate may not lose more than this against the live model on the test split. */
    private const MACRO_F1_TOLERANCE = 0.005;

    public function handle(): int
    {
        $excluded = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('exclude-features')))));
        $featurizer = new ResumeLineFeaturizer($excluded);
        $fingerprint = $featurizer->configFingerprint();

        $sources = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('sources')))));
        $correctionWeight = max(1, (int) $this->option('correction-weight'));

        $train = $this->load(ResumeParserExample::SPLIT_TRAIN, $sources, $fingerprint, $correctionWeight);

        if ($train === []) {
            $this->error('No training examples. Run resume:generate-training-data first.');

            return self::FAILURE;
        }

        $test = $this->load(ResumeParserExample::SPLIT_TEST, $sources, $fingerprint, 1);
        $dev = $this->load(ResumeParserExample::SPLIT_DEV, $sources, $fingerprint, 1);

        $this->info(sprintf(
            'Training on %d sequences (dev %d, test %d), %d epochs%s.',
            count($train),
            count($dev),
            count($test),
            (int) $this->option('epochs'),
            $excluded === [] ? '' : ', excluding ' . implode('+', $excluded)
        ));

        $trainer = new PerceptronTrainer($featurizer, (int) $this->option('epochs'), (int) $this->option('seed'));

        $bar = $this->output->createProgressBar((int) $this->option('epochs'));
        $bar->start();

        $result = $trainer->train($train, function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();
        $this->newLine(2);

        $model = $result['model']->pruned((float) $this->option('prune'));
        $stats = $result['stats'];

        $this->line(sprintf(
            'Features %d, weights %d (pruned from %d), %ds.',
            $model->featureCount(),
            $model->weightCount(),
            $stats['raw_weights'],
            $stats['trained_seconds']
        ));

        $evaluator = new ParserEvaluator($featurizer);
        $devMetrics = $dev === [] ? null : $evaluator->evaluate($model, $dev);
        $testMetrics = $test === [] ? null : $evaluator->evaluate($model, $test);

        $this->reportSplits($devMetrics, $testMetrics);

        $version = ResumeParserModel::nextVersion();
        $record = ResumeParserModel::create([
            'version' => $version,
            'label' => $this->option('label'),
            'weights' => $model->toArray(),
            'feature_count' => $model->featureCount(),
            'label_set' => Labels::ALL,
            'hyperparameters' => [
                'epochs' => (int) $this->option('epochs'),
                'seed' => (int) $this->option('seed'),
                'prune' => (float) $this->option('prune'),
                'exclude_features' => $excluded,
                'correction_weight' => $correctionWeight,
                'features_fingerprint' => $fingerprint,
            ],
            'training_example_counts' => $this->countsBySource($sources, $fingerprint),
            'metrics' => ['dev' => $devMetrics, 'test' => $testMetrics, 'training' => $stats],
            'macro_f1' => $testMetrics['macro_f1'] ?? $devMetrics['macro_f1'] ?? null,
            'trained_seconds' => $stats['trained_seconds'],
        ]);

        $this->decideActivation($record, $testMetrics);

        return self::SUCCESS;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function load(string $split, array $sources, string $fingerprint, int $correctionWeight): array
    {
        $rows = [];

        ResumeParserExample::query()
            ->where('split', $split)
            ->whereIn('source', $sources)
            ->orderBy('resume_parser_example_id')
            // Chunked because the corpus is thousands of sequences and each
            // carries its lines plus cached features.
            ->chunk(200, function ($chunk) use (&$rows, $fingerprint, $correctionWeight) {
                foreach ($chunk as $example) {
                    $rows[] = [
                        'lines' => $example->lines ?? [],
                        'gold_labels' => $example->gold_labels ?? [],
                        'extraction_mode' => $example->extraction_mode,
                        'variant_key' => $example->variant_key,
                        'profile_key' => $example->profile_key,
                        // Stale cached features are dropped so the trainer
                        // recomputes them rather than training on the wrong shape.
                        'features' => $example->hasUsableFeatures($fingerprint) ? $example->features : null,
                        'weight' => $example->source === ResumeParserExample::SOURCE_CORRECTION
                            ? $correctionWeight
                            : (int) $example->weight,
                    ];
                }
            });

        return $rows;
    }

    private function countsBySource(array $sources, string $fingerprint): array
    {
        return ResumeParserExample::query()
            ->whereIn('source', $sources)
            ->selectRaw('source, count(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source')
            ->all();
    }

    private function reportSplits(?array $dev, ?array $test): void
    {
        $rows = [];

        foreach (['dev' => $dev, 'test' => $test] as $name => $metrics) {
            if ($metrics === null) {
                continue;
            }
            $rows[] = [$name, $metrics['positions'], $metrics['accuracy'], $metrics['macro_f1'], $metrics['labels_present']];
        }

        if ($rows !== []) {
            $this->table(['split', 'positions', 'accuracy', 'macro F1', 'labels seen'], $rows);
        }

        $metrics = $test ?? $dev;

        if ($metrics === null) {
            return;
        }

        $worst = [];
        foreach ($metrics['per_label'] as $label => $scores) {
            if ($scores['support'] > 0) {
                $worst[$label] = $scores;
            }
        }
        uasort($worst, fn ($a, $b) => $a['f1'] <=> $b['f1']);

        $this->line('Weakest labels:');
        $rows = [];
        foreach (array_slice($worst, 0, 6, true) as $label => $scores) {
            $rows[] = [$label, $scores['support'], $scores['precision'], $scores['recall'], $scores['f1']];
        }
        $this->table(['label', 'support', 'precision', 'recall', 'F1'], $rows);
    }

    /**
     * Activation is automatic but not unconditional: a candidate that scores
     * worse than the live model is stored and marked, and the live model is left
     * alone. Nothing here needs a human, but a bad retrain cannot reach users.
     */
    private function decideActivation(ResumeParserModel $candidate, ?array $testMetrics): void
    {
        $mode = $this->option('activate');

        if ($mode === 'never') {
            $this->warn("Stored version {$candidate->version} without activating (--activate=never).");

            return;
        }

        $live = ResumeParserModel::query()
            ->whereNotNull('activated_at')
            ->where('resume_parser_model_id', '!=', $candidate->resume_parser_model_id)
            ->orderByDesc('version')
            ->first();

        if ($mode === 'always' || $live === null || $candidate->macro_f1 === null || $live->macro_f1 === null) {
            $candidate->activate();
            $this->info("Activated version {$candidate->version}.");

            return;
        }

        $drop = (float) $live->macro_f1 - (float) $candidate->macro_f1;

        if ($drop > self::MACRO_F1_TOLERANCE) {
            $reason = sprintf(
                'macro F1 %.4f is %.4f below live version %d (%.4f)',
                $candidate->macro_f1,
                $drop,
                $live->version,
                $live->macro_f1
            );

            $candidate->forceFill(['rejected_reason' => $reason])->save();

            $this->error("Version {$candidate->version} rejected: {$reason}");
            $this->line("Live version {$live->version} is unchanged.");

            return;
        }

        $candidate->activate();
        $this->info(sprintf(
            'Activated version %d (macro F1 %.4f vs %.4f).',
            $candidate->version,
            $candidate->macro_f1,
            $live->macro_f1
        ));
    }
}
