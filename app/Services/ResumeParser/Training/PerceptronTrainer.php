<?php

namespace App\Services\ResumeParser\Training;

use App\Services\ResumeParser\Labels;
use App\Services\ResumeParser\Line;
use App\Services\ResumeParser\PerceptronModel;
use App\Services\ResumeParser\ResumeLineFeaturizer;

/**
 * Trains the line classifier: an averaged perceptron over sequences.
 *
 * Averaging is what makes a perceptron usable — the final raw weight vector
 * swings with whatever it saw last, while the average over every update is
 * stable. It is computed lazily: the textbook version adds the whole weight
 * vector to a running total at every single position, which is O(weights) per
 * line and takes hours here. Instead each key remembers when it was last touched
 * and its contribution is accumulated only then.
 *
 * Positions whose gold label is unknown are skipped for the weight update but
 * still advance the sequence, so a partly-corrected import contributes what it
 * knows without inventing the rest.
 */
class PerceptronTrainer
{
    /** @var array<string,int> feature string => dense id, grown as training proceeds */
    private array $featureIds = [];

    /** @var array<int,float> current raw weights */
    private array $weights = [];

    /** @var array<int,float> accumulated weight-time products, for the average */
    private array $totals = [];

    /** @var array<int,int> timestep each key was last updated */
    private array $lastSeen = [];

    private int $timestep = 0;

    private int $labelCount;

    /** @var array<string,int> */
    private array $labelIndex;

    public function __construct(
        private ResumeLineFeaturizer $featurizer,
        private int $epochs = 10,
        private int $seed = 20260927,
    ) {
        $this->labelCount = Labels::count();
        $this->labelIndex = array_flip(Labels::ALL);
    }

    /**
     * @param  array<int,array{lines:array,gold_labels:array,features:?array,extraction_mode:string,weight:int}>  $examples
     * @return array{model: PerceptronModel, stats: array<string,mixed>}
     */
    public function train(array $examples, ?callable $onEpoch = null): array
    {
        $prepared = array_map(fn ($example) => $this->prepare($example), $examples);
        $prepared = array_values(array_filter($prepared, fn ($e) => $e !== null));

        $started = microtime(true);
        $order = array_keys($prepared);
        $updatesByEpoch = [];

        for ($epoch = 1; $epoch <= $this->epochs; $epoch++) {
            // Seeded per epoch so a run is reproducible from its seed.
            mt_srand($this->seed + $epoch);
            shuffle($order);

            $updates = 0;
            $positions = 0;

            foreach ($order as $key) {
                [$u, $p] = $this->trainOne($prepared[$key]);
                $updates += $u;
                $positions += $p;
            }

            $updatesByEpoch[$epoch] = ['updates' => $updates, 'positions' => $positions];

            if ($onEpoch !== null) {
                $onEpoch($epoch, $updates, $positions);
            }
        }

        return [
            'model' => new PerceptronModel(Labels::ALL, $this->featureIds, $this->averagedWeights()),
            'stats' => [
                'epochs' => $this->epochs,
                'seed' => $this->seed,
                'sequences' => count($prepared),
                'timesteps' => $this->timestep,
                'raw_weights' => count($this->weights),
                'features' => count($this->featureIds),
                'updates_by_epoch' => $updatesByEpoch,
                'trained_seconds' => (int) round(microtime(true) - $started),
            ],
        ];
    }

    /**
     * Static features are cached per example; only the conjunctions with the
     * previous label and current section have to be recomputed, because those
     * depend on decisions made during this pass.
     *
     * @return array{lines:array<int,Line>,gold:array<int,?string>,static:array<int,array>,weight:int}|null
     */
    private function prepare(array $example): ?array
    {
        $lines = array_map(fn ($row) => Line::fromArray($row), $example['lines'] ?? []);

        if ($lines === []) {
            return null;
        }

        $gold = $example['gold_labels'] ?? [];
        $static = $example['features'];

        if (! is_array($static) || count($static) !== count($lines)) {
            $static = $this->featurizer->staticFeatures($lines, $example['extraction_mode'] ?? ResumeLineFeaturizer::MODE_LAYOUT);
        }

        // Nothing to learn from a sequence with no known labels.
        if (array_filter($gold, fn ($label) => $label !== null) === []) {
            return null;
        }

        return [
            'lines' => $lines,
            'gold' => $gold,
            'static' => $static,
            'weight' => max(1, (int) ($example['weight'] ?? 1)),
        ];
    }

    /** @return array{0:int,1:int} updates, positions */
    private function trainOne(array $example): array
    {
        $lines = $example['lines'];
        $gold = $example['gold'];
        $static = $example['static'];
        $weight = $example['weight'];

        $previousGold = null;
        $previousPredicted = null;
        $sectionGold = null;
        $sectionPredicted = null;

        $updates = 0;
        $positions = 0;

        foreach ($lines as $index => $line) {
            $this->timestep++;
            $positions++;

            $predictedFeatures = array_merge(
                $static[$index] ?? [],
                $this->featurizer->dynamicFeatures($lines, $index, $previousPredicted, $sectionPredicted)
            );

            $predicted = $this->predict($predictedFeatures);
            $goldLabel = $gold[$index] ?? null;

            if ($goldLabel !== null && $goldLabel !== $predicted && isset($this->labelIndex[$goldLabel])) {
                // The positive update is scored against the gold history and the
                // negative against the predicted one: that is the violation the
                // model actually made, and correcting it under its own history is
                // what teaches the sequence, not just the line.
                $goldFeatures = array_merge(
                    $static[$index] ?? [],
                    $this->featurizer->dynamicFeatures($lines, $index, $previousGold, $sectionGold)
                );

                $this->adjust($goldFeatures, $this->labelIndex[$goldLabel], $weight);
                $this->adjust($predictedFeatures, $this->labelIndex[$predicted], -$weight);
                $updates++;
            }

            // Teacher forcing where a gold label exists; where it does not, both
            // histories fall back to the prediction so the chain stays intact.
            $previousGold = $goldLabel ?? $predicted;
            $previousPredicted = $predicted;
            $sectionGold = Labels::sectionFor($previousGold) ?? $sectionGold;
            $sectionPredicted = Labels::sectionFor($predicted) ?? $sectionPredicted;
        }

        return [$updates, $positions];
    }

    /** @param array<int,string> $features */
    private function predict(array $features): string
    {
        $scores = array_fill(0, $this->labelCount, 0.0);

        foreach ($features as $feature) {
            $id = $this->featureIds[$feature] ?? null;

            if ($id === null) {
                continue;
            }

            $base = $id * $this->labelCount;

            for ($label = 0; $label < $this->labelCount; $label++) {
                $value = $this->weights[$base + $label] ?? null;
                if ($value !== null) {
                    $scores[$label] += $value;
                }
            }
        }

        $bestLabel = 0;
        $bestScore = -INF;

        foreach ($scores as $label => $score) {
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestLabel = $label;
            }
        }

        return Labels::ALL[$bestLabel];
    }

    /**
     * Nudges the weights of one label for every active feature, accumulating the
     * average as it goes.
     *
     * @param  array<int,string>  $features
     */
    private function adjust(array $features, int $labelIndex, int $delta): void
    {
        foreach ($features as $feature) {
            $id = $this->featureIds[$feature] ??= count($this->featureIds);
            $key = ($id * $this->labelCount) + $labelIndex;

            $current = $this->weights[$key] ?? 0.0;
            // Accrue the weight this key held over the interval since it was last
            // touched, rather than sweeping every key every timestep.
            $this->totals[$key] = ($this->totals[$key] ?? 0.0)
                + ($current * ($this->timestep - ($this->lastSeen[$key] ?? 0)));

            $this->weights[$key] = $current + $delta;
            $this->lastSeen[$key] = $this->timestep;
        }
    }

    /** @return array<int,float> */
    private function averagedWeights(): array
    {
        if ($this->timestep === 0) {
            return [];
        }

        $averaged = [];

        foreach ($this->weights as $key => $weight) {
            $total = ($this->totals[$key] ?? 0.0) + ($weight * ($this->timestep - ($this->lastSeen[$key] ?? 0)));
            $value = $total / $this->timestep;

            if ($value !== 0.0) {
                $averaged[$key] = round($value, 6);
            }
        }

        return $averaged;
    }
}
