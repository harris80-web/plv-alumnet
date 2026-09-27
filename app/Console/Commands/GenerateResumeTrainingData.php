<?php

namespace App\Console\Commands;

use App\Models\ResumeParserExample;
use App\Services\ResumeParser\ResumeLineFeaturizer;
use App\Services\ResumeParser\Training\DocumentPlanner;
use App\Services\ResumeParser\Training\ExampleBuilder;
use App\Services\ResumeParser\Training\LayoutVariantFactory;
use App\Services\ResumeParser\Training\ProfileSampler;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

/**
 * Builds the training corpus by rendering known resume content through many
 * layouts and reading it back out.
 *
 * Because the content going in is known, every label is exact and free — no
 * annotation, and no language model involved. The generator is also the check on
 * the featurizer: if alignment coverage drops, the extractor is losing text.
 *
 * Splits are assigned by whole profile and whole layout, never per line, so the
 * same content cannot appear in training and test wearing a different layout.
 * Reported accuracy would otherwise be badly inflated.
 */
class GenerateResumeTrainingData extends Command
{
    protected $signature = 'resume:generate-training-data
                            {--profiles=150 : how many distinct people to invent}
                            {--variants=6 : layouts rendered per profile}
                            {--seed=20260927}
                            {--fresh : delete existing synthetic examples first}
                            {--keep-pdfs= : also write the rendered PDFs here for inspection}
                            {--chunk=50 : rows inserted per batch}';

    protected $description = 'Render synthetic resumes through many layouts and store them as labelled training examples';

    /** Profiles and layouts held out entirely, so generalisation can be measured. */
    private const HELDOUT_PROFILE_RATIO = 0.15;

    private const HELDOUT_VARIANTS = ['v11', 'v12'];

    /** Below this, alignment is reporting an extraction bug rather than noise. */
    private const COVERAGE_FLOOR = 0.95;

    public function handle(): int
    {
        $profileCount = max(1, (int) $this->option('profiles'));
        $variantsPer = max(1, (int) $this->option('variants'));
        $seed = (int) $this->option('seed');
        $keepPdfs = $this->option('keep-pdfs');

        if ($keepPdfs && ! is_dir($keepPdfs) && ! mkdir($keepPdfs, 0775, true)) {
            $this->error("Could not create {$keepPdfs}");

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $deleted = ResumeParserExample::where('source', ResumeParserExample::SOURCE_SYNTHETIC)->delete();
            $this->warn("Deleted {$deleted} existing synthetic examples.");
        }

        $sampler = new ProfileSampler($seed);
        $planner = new DocumentPlanner();
        $builder = new ExampleBuilder();
        $featurizer = new ResumeLineFeaturizer();
        $fingerprint = $featurizer->configFingerprint();

        $allVariants = (new LayoutVariantFactory())->all();
        $plainVariants = array_values(array_filter(
            $allVariants,
            fn ($v) => $v['template'] === LayoutVariantFactory::TEMPLATE_PLAIN
        ));
        $designedVariants = array_values(array_filter(
            $allVariants,
            fn ($v) => $v['template'] === LayoutVariantFactory::TEMPLATE_DESIGNED
        ));

        $heldOutProfiles = (int) ceil($profileCount * self::HELDOUT_PROFILE_RATIO);

        $this->info(sprintf(
            'Generating %d profiles x %d layouts x 2 extraction modes (~%d sequences).',
            $profileCount,
            $variantsPer,
            $profileCount * $variantsPer * 2
        ));

        $bar = $this->output->createProgressBar($profileCount);
        $bar->start();

        $pending = [];
        $coverageByVariant = [];
        $stats = ['examples' => 0, 'lines' => 0, 'labelled' => 0, 'failed' => 0];

        for ($i = 0; $i < $profileCount; $i++) {
            $profile = $sampler->sample($i);
            $isHeldOutProfile = $i < $heldOutProfiles;

            foreach ($this->variantsFor($i, $variantsPer, $designedVariants, $plainVariants) as $variant) {
                $plan = $planner->plan($profile, $variant);
                $expected = $planner->expectedCells($plan);

                try {
                    $pdfBytes = Pdf::loadView($variant['template'], compact('plan', 'variant'))->output();
                } catch (\Throwable $e) {
                    $stats['failed']++;
                    $this->newLine();
                    $this->warn("Render failed ({$profile['profile_key']}/{$variant['variant_key']}): " . $e->getMessage());
                    continue;
                }

                $path = $this->writeTempPdf($pdfBytes, $profile, $variant, $keepPdfs);

                try {
                    $extracted = $featurizer->fromFile($path);
                } catch (\Throwable $e) {
                    $stats['failed']++;
                    @unlink($path);
                    continue;
                }

                $split = $this->splitFor($isHeldOutProfile, $variant['variant_key'], $variant['template']);

                // Emitted twice: once with layout features and once as if the
                // text matrices had been unusable, so the model has seen the
                // degraded regime it will meet on real uploads.
                $modes = [
                    ResumeLineFeaturizer::MODE_LAYOUT => $extracted['lines'],
                    ResumeLineFeaturizer::MODE_TEXT_ONLY => $featurizer->linesFromRawText($extracted['raw_text']),
                ];

                foreach ($modes as $mode => $lines) {
                    if ($lines === []) {
                        continue;
                    }

                    $alignment = $builder->align($lines, $expected);

                    if ($mode === ResumeLineFeaturizer::MODE_LAYOUT) {
                        $coverageByVariant[$variant['variant_key']][] = $alignment['coverage'];
                    }

                    $pending[] = [
                        'source' => ResumeParserExample::SOURCE_SYNTHETIC,
                        'split' => $split,
                        'profile_key' => $profile['profile_key'],
                        'variant_key' => $variant['variant_key'] . ':' . $mode,
                        'extraction_mode' => $mode,
                        'lines' => json_encode(array_map(fn ($line) => $line->toArray(), $lines)),
                        'gold_labels' => json_encode($alignment['gold_labels']),
                        'features' => json_encode($featurizer->staticFeatures($lines, $mode)),
                        'features_fingerprint' => $fingerprint,
                        'weight' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $stats['examples']++;
                    $stats['lines'] += count($lines);
                    $stats['labelled'] += count(array_filter($alignment['gold_labels'], fn ($l) => $l !== null));
                }

                if ($keepPdfs === null) {
                    @unlink($path);
                }

                if (count($pending) >= (int) $this->option('chunk')) {
                    ResumeParserExample::insert($pending);
                    $pending = [];
                }
            }

            $bar->advance();
        }

        if ($pending !== []) {
            ResumeParserExample::insert($pending);
        }

        $bar->finish();
        $this->newLine(2);

        $this->report($stats, $coverageByVariant);

        return $this->lowCoverageVariants($coverageByVariant) === [] ? self::SUCCESS : self::FAILURE;
    }

    /** @return array<int,array<string,mixed>> */
    private function variantsFor(int $index, int $count, array $designed, array $plain): array
    {
        // Mostly the designed family, with the plain template appearing often
        // enough to be learnable but held out of training by split.
        $pool = $designed;
        $chosen = [];

        for ($i = 0; $i < $count; $i++) {
            $chosen[] = $pool[($index * $count + $i) % count($pool)];
        }

        if ($plain !== []) {
            $chosen[count($chosen) - 1] = $plain[$index % count($plain)];
        }

        return $chosen;
    }

    private function splitFor(bool $heldOutProfile, string $variantKey, string $template): string
    {
        if ($template === LayoutVariantFactory::TEMPLATE_PLAIN) {
            return ResumeParserExample::SPLIT_TEST;
        }

        if (in_array($variantKey, self::HELDOUT_VARIANTS, true)) {
            return $heldOutProfile ? ResumeParserExample::SPLIT_TEST : ResumeParserExample::SPLIT_DEV;
        }

        return $heldOutProfile ? ResumeParserExample::SPLIT_DEV : ResumeParserExample::SPLIT_TRAIN;
    }

    private function writeTempPdf(string $bytes, array $profile, array $variant, ?string $keepDir): string
    {
        $name = $profile['profile_key'] . '-' . $variant['variant_key'] . '.pdf';
        $path = $keepDir
            ? rtrim($keepDir, '/\\') . DIRECTORY_SEPARATOR . $name
            : tempnam(sys_get_temp_dir(), 'resume') . '.pdf';

        file_put_contents($path, $bytes);

        return $path;
    }

    private function report(array $stats, array $coverageByVariant): void
    {
        $this->info('Examples stored: ' . $stats['examples']);
        $this->info('Lines: ' . $stats['lines'] . ', labelled: ' . $stats['labelled']);

        if ($stats['failed'] > 0) {
            $this->warn('Renders or extractions that failed: ' . $stats['failed']);
        }

        $rows = [];
        foreach ($coverageByVariant as $variantKey => $values) {
            $average = array_sum($values) / max(1, count($values));
            $rows[] = [
                $variantKey,
                count($values),
                number_format($average, 4),
                number_format(min($values), 4),
                $average >= self::COVERAGE_FLOOR ? 'ok' : 'LOW',
            ];
        }

        usort($rows, fn ($a, $b) => $a[0] <=> $b[0]);
        $this->newLine();
        $this->table(['variant', 'docs', 'mean coverage', 'worst', ''], $rows);

        $counts = ResumeParserExample::where('source', ResumeParserExample::SOURCE_SYNTHETIC)
            ->selectRaw('split, count(*) as total')
            ->groupBy('split')
            ->pluck('total', 'split')
            ->all();

        $this->table(
            ['split', 'examples'],
            array_map(fn ($split, $total) => [$split, $total], array_keys($counts), $counts)
        );

        $low = $this->lowCoverageVariants($coverageByVariant);

        if ($low === []) {
            $this->info('Alignment coverage is at or above ' . self::COVERAGE_FLOOR . ' for every layout.');

            return;
        }

        // Coverage is the Phase 1 check: the rendered text is known exactly, so
        // anything missing was lost on the way out of the PDF.
        $this->error('Low alignment coverage (extraction is losing text) for: ' . implode(', ', $low));
        $this->line('Re-run with --keep-pdfs=<dir> and inspect with resume:debug-parse.');
    }

    /** @return array<int,string> */
    private function lowCoverageVariants(array $coverageByVariant): array
    {
        $low = [];

        foreach ($coverageByVariant as $variantKey => $values) {
            if (array_sum($values) / max(1, count($values)) < self::COVERAGE_FLOOR) {
                $low[] = $variantKey;
            }
        }

        return $low;
    }
}
