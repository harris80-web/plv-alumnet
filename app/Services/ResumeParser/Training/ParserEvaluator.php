<?php

namespace App\Services\ResumeParser\Training;

use App\Services\ResumeParser\Labels;
use App\Services\ResumeParser\Line;
use App\Services\ResumeParser\PerceptronModel;
use App\Services\ResumeParser\ResumeLineClassifier;
use App\Services\ResumeParser\ResumeLineFeaturizer;

/**
 * Scores the line classifier.
 *
 * Macro-F1 rather than plain accuracy, because the label distribution is very
 * uneven — a model that called everything exp_bullet would already look
 * respectable on accuracy alone. Per-label figures and the confusion matrix are
 * kept because they are diagnostic rather than decorative: exp_title confused
 * with exp_meta is the expected dominant error and points straight at which
 * feature to add next.
 */
class ParserEvaluator
{
    public function __construct(private ResumeLineFeaturizer $featurizer)
    {
    }

    /**
     * @param  array<int,array{lines:array,gold_labels:array,extraction_mode:string,variant_key:?string,profile_key:?string}>  $examples
     * @return array<string,mixed>
     */
    public function evaluate(PerceptronModel $model, array $examples): array
    {
        $classifier = new ResumeLineClassifier($model, $this->featurizer);

        $confusion = [];
        $support = [];
        $predictedCount = [];
        $correct = [];
        $totalPositions = 0;
        $totalCorrect = 0;
        $marginBuckets = [];

        foreach ($examples as $example) {
            $lines = array_map(fn ($row) => Line::fromArray($row), $example['lines'] ?? []);

            if ($lines === []) {
                continue;
            }

            $decoded = $classifier->decode($lines, $example['extraction_mode'] ?? ResumeLineFeaturizer::MODE_LAYOUT);
            $gold = $example['gold_labels'] ?? [];

            foreach ($lines as $index => $line) {
                $goldLabel = $gold[$index] ?? null;

                // Unlabelled positions are excluded from scoring for the same
                // reason they are excluded from training: they are unknown, not
                // negative.
                if ($goldLabel === null) {
                    continue;
                }

                $predicted = $decoded['labels'][$index] ?? Labels::OTHER;

                $totalPositions++;
                $support[$goldLabel] = ($support[$goldLabel] ?? 0) + 1;
                $predictedCount[$predicted] = ($predictedCount[$predicted] ?? 0) + 1;
                $confusion[$goldLabel][$predicted] = ($confusion[$goldLabel][$predicted] ?? 0) + 1;

                $isCorrect = $predicted === $goldLabel;

                if ($isCorrect) {
                    $correct[$goldLabel] = ($correct[$goldLabel] ?? 0) + 1;
                    $totalCorrect++;
                }

                $bucket = $this->marginBucket($decoded['margins'][$index] ?? 0.0);
                $marginBuckets[$bucket]['total'] = ($marginBuckets[$bucket]['total'] ?? 0) + 1;
                $marginBuckets[$bucket]['correct'] = ($marginBuckets[$bucket]['correct'] ?? 0) + ($isCorrect ? 1 : 0);
            }
        }

        $perLabel = [];
        $f1Sum = 0.0;
        $labelsPresent = 0;

        foreach (Labels::ALL as $label) {
            $tp = $correct[$label] ?? 0;
            $goldTotal = $support[$label] ?? 0;
            $predTotal = $predictedCount[$label] ?? 0;

            $precision = $predTotal > 0 ? $tp / $predTotal : 0.0;
            $recall = $goldTotal > 0 ? $tp / $goldTotal : 0.0;
            $f1 = ($precision + $recall) > 0 ? (2 * $precision * $recall) / ($precision + $recall) : 0.0;

            $perLabel[$label] = [
                'support' => $goldTotal,
                'predicted' => $predTotal,
                'precision' => round($precision, 4),
                'recall' => round($recall, 4),
                'f1' => round($f1, 4),
            ];

            // Labels absent from this split would otherwise drag macro-F1 down
            // towards zero and make two splits incomparable.
            if ($goldTotal > 0) {
                $f1Sum += $f1;
                $labelsPresent++;
            }
        }

        ksort($marginBuckets);

        return [
            'positions' => $totalPositions,
            'accuracy' => $totalPositions > 0 ? round($totalCorrect / $totalPositions, 4) : 0.0,
            'macro_f1' => $labelsPresent > 0 ? round($f1Sum / $labelsPresent, 4) : 0.0,
            'labels_present' => $labelsPresent,
            'per_label' => $perLabel,
            'confusion' => $confusion,
            'margin_buckets' => $marginBuckets,
        ];
    }

    /**
     * The seen/unseen grid: accuracy broken down by whether the content and the
     * layout were in training. The unseen/unseen cell is the one that actually
     * speaks to handling formats the model has never met.
     *
     * @return array<string,mixed>
     */
    public function grid(PerceptronModel $model, array $examples, array $trainedProfiles, array $trainedVariants): array
    {
        $groups = [
            'seen_profile_seen_layout' => [],
            'seen_profile_unseen_layout' => [],
            'unseen_profile_seen_layout' => [],
            'unseen_profile_unseen_layout' => [],
        ];

        foreach ($examples as $example) {
            $profileSeen = in_array($example['profile_key'] ?? null, $trainedProfiles, true);
            $variantSeen = in_array($example['variant_key'] ?? null, $trainedVariants, true);

            $key = ($profileSeen ? 'seen_profile' : 'unseen_profile')
                . '_' . ($variantSeen ? 'seen_layout' : 'unseen_layout');

            $groups[$key][] = $example;
        }

        $out = [];

        foreach ($groups as $key => $group) {
            if ($group === []) {
                $out[$key] = null;
                continue;
            }

            $result = $this->evaluate($model, $group);
            $out[$key] = [
                'examples' => count($group),
                'positions' => $result['positions'],
                'accuracy' => $result['accuracy'],
                'macro_f1' => $result['macro_f1'],
            ];
        }

        return $out;
    }

    private function marginBucket(float $margin): string
    {
        return match (true) {
            $margin <= 0.0 => '0.0',
            $margin < 1.0 => '0-1',
            $margin < 3.0 => '1-3',
            $margin < 8.0 => '3-8',
            default => '8+',
        };
    }
}
