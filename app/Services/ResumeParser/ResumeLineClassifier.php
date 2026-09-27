<?php

namespace App\Services\ResumeParser;

/**
 * Labels each line of a resume, left to right.
 *
 * Decoding is greedy and carries two pieces of state forward: the label just
 * assigned, and the section opened by the most recent heading. The section is
 * what resolves an otherwise ambiguous short bold line — the same line is a job
 * title under Experience and a certification name under Certifications — and it
 * is legal to use during greedy decoding because it depends only on earlier
 * decisions.
 */
class ResumeLineClassifier
{
    public function __construct(
        private PerceptronModel $model,
        private ResumeLineFeaturizer $featurizer,
    ) {
    }

    public function model(): PerceptronModel
    {
        return $this->model;
    }

    /**
     * @param  array<int,Line>  $lines
     * @return array{labels: array<int,string>, margins: array<int,float>}
     */
    public function decode(array $lines, string $mode): array
    {
        $static = $this->featurizer->staticFeatures($lines, $mode);

        $labels = [];
        $margins = [];
        $previousLabel = null;
        $section = null;

        foreach ($lines as $index => $line) {
            $features = array_merge(
                $static[$index] ?? [],
                $this->featurizer->dynamicFeatures($lines, $index, $previousLabel, $section)
            );

            $best = $this->model->best($this->model->idsFor($features));

            $labels[$index] = $best['label'];
            $margins[$index] = $best['margin'];

            $previousLabel = $best['label'];
            $section = Labels::sectionFor($best['label']) ?? $section;
        }

        return ['labels' => $labels, 'margins' => $margins];
    }
}
