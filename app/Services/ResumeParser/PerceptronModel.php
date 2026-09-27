<?php

namespace App\Services\ResumeParser;

/**
 * The trained linear model: one weight per (feature, label) pair.
 *
 * Weights are held in a single flat array keyed by integer, computed as
 * featureId * labelCount + labelId. Nested arrays keyed by feature strings would
 * be the obvious shape and are far slower: scoring one line touches every label
 * for every active feature, so the inner loop must be integer arithmetic on a
 * packed array rather than string concatenation and hashing.
 */
class PerceptronModel
{
    /**
     * @param  array<int,string>  $labels  ordered; a weight's label is its index here
     * @param  array<string,int>  $featureIds  feature string => dense id
     * @param  array<int,float>  $weights  featureId * labelCount + labelId => weight
     */
    public function __construct(
        private array $labels,
        private array $featureIds,
        private array $weights,
    ) {
    }

    public static function empty(array $labels = Labels::ALL): self
    {
        return new self($labels, [], []);
    }

    /** @return array<int,string> */
    public function labels(): array
    {
        return $this->labels;
    }

    public function labelCount(): int
    {
        return count($this->labels);
    }

    public function featureCount(): int
    {
        return count($this->featureIds);
    }

    public function weightCount(): int
    {
        return count($this->weights);
    }

    /**
     * Ids for the features this model knows. Unknown features are dropped rather
     * than added: at prediction time a feature never seen in training carries no
     * information, and adding it would grow the model on every request.
     *
     * @param  array<int,string>  $features
     * @return array<int,int>
     */
    public function idsFor(array $features): array
    {
        $ids = [];

        foreach ($features as $feature) {
            $id = $this->featureIds[$feature] ?? null;
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    /**
     * @param  array<int,int>  $featureIds
     * @return array<int,float> score per label index
     */
    public function scores(array $featureIds): array
    {
        $labelCount = count($this->labels);
        $scores = array_fill(0, $labelCount, 0.0);

        foreach ($featureIds as $id) {
            $base = $id * $labelCount;

            for ($label = 0; $label < $labelCount; $label++) {
                $weight = $this->weights[$base + $label] ?? null;
                if ($weight !== null) {
                    $scores[$label] += $weight;
                }
            }
        }

        return $scores;
    }

    /**
     * Best label plus how far clear it was. The margin is the only confidence
     * signal used anywhere, and only in two places: guarding against one job
     * exploding into several, and the confidence curve in the evaluation.
     *
     * @return array{label: string, index: int, margin: float}
     */
    public function best(array $featureIds): array
    {
        $scores = $this->scores($featureIds);

        $bestIndex = 0;
        $bestScore = -INF;
        $secondScore = -INF;

        foreach ($scores as $index => $score) {
            if ($score > $bestScore) {
                $secondScore = $bestScore;
                $bestScore = $score;
                $bestIndex = $index;
            } elseif ($score > $secondScore) {
                $secondScore = $score;
            }
        }

        return [
            'label' => $this->labels[$bestIndex],
            'index' => $bestIndex,
            'margin' => is_finite($secondScore) ? round($bestScore - $secondScore, 4) : 0.0,
        ];
    }

    /** @return array{labels:array<int,string>,feature_ids:array<string,int>,weights:array<int,float>} */
    public function toArray(): array
    {
        return [
            'labels' => $this->labels,
            'feature_ids' => $this->featureIds,
            'weights' => $this->weights,
        ];
    }

    public static function fromArray(array $data): self
    {
        $weights = [];
        // JSON object keys come back as strings; the scoring loop depends on
        // integer keys, so they are cast once here rather than per lookup.
        foreach ($data['weights'] ?? [] as $key => $weight) {
            $weights[(int) $key] = (float) $weight;
        }

        return new self(
            $data['labels'] ?? Labels::ALL,
            array_map('intval', $data['feature_ids'] ?? []),
            $weights,
        );
    }

    /**
     * Drops near-zero weights. Averaging leaves a long tail of values that round
     * to nothing but still cost space in the database and a lookup when scoring.
     */
    public function pruned(float $threshold): self
    {
        if ($threshold <= 0) {
            return $this;
        }

        $labelCount = count($this->labels);
        $weights = [];
        $keptFeatures = [];

        foreach ($this->weights as $key => $weight) {
            if (abs($weight) < $threshold) {
                continue;
            }
            $weights[$key] = $weight;
            $keptFeatures[intdiv($key, $labelCount)] = true;
        }

        // Features left with no surviving weight are dropped from the index too,
        // otherwise featureCount() overstates the model.
        $featureIds = array_filter($this->featureIds, fn ($id) => isset($keptFeatures[$id]));

        return new self($this->labels, $featureIds, $weights);
    }
}
