<?php

namespace App\Console\Commands;

use App\Services\ResumeParser\ResumeLineFeaturizer;
use Illuminate\Console\Command;

/**
 * Prints what the featurizer actually saw in a PDF.
 *
 * Effectively every bug in the parser will be a featurizer bug — lines glued
 * together, a date column mistaken for a second page column, font sizes not
 * coming through — and none of those are visible from the parsed output alone.
 */
class DebugResumeParse extends Command
{
    protected $signature = 'resume:debug-parse
                            {file : path to a PDF}
                            {--features : also print each line\'s feature strings}
                            {--json= : write the full dump to this path instead of printing}';

    protected $description = 'Dump the lines and features the resume parser extracts from a PDF';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! is_file($path)) {
            $this->error("No such file: {$path}");

            return self::FAILURE;
        }

        $featurizer = new ResumeLineFeaturizer();

        try {
            $result = $featurizer->fromFile($path);
        } catch (\Throwable $e) {
            $this->error('Extraction failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        /** @var array<int,\App\Services\ResumeParser\Line> $lines */
        $lines = $result['lines'];
        $static = $featurizer->staticFeatures($lines, $result['mode']);

        $this->line("mode:        {$result['mode']}");
        $this->line("pages:       {$result['page_count']}");
        $this->line('lines:       ' . count($lines));
        $this->line('raw_text:    ' . mb_strlen($result['raw_text']) . ' chars');
        $this->line('fingerprint: ' . $featurizer->configFingerprint());
        $this->newLine();

        if ($this->option('json')) {
            $dump = [];
            foreach ($lines as $i => $line) {
                $dump[] = $line->toArray() + ['features' => $static[$i] ?? []];
            }
            file_put_contents($this->option('json'), json_encode($dump, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info('Wrote ' . $this->option('json'));

            return self::SUCCESS;
        }

        foreach ($lines as $i => $line) {
            $flags = implode('', [
                $line->bold ? 'B' : '-',
                $line->italic ? 'I' : '-',
                $line->isRowFragment() ? (string) $line->fragmentIndex : '-',
            ]);

            $this->line(sprintf(
                '%3d p%d %s x=%-6.1f y=%-7.1f sz=%-5.1f gap=%-5.1f | %s',
                $i,
                $line->page,
                $flags,
                $line->x,
                $line->y,
                $line->size,
                $line->gapAbove,
                mb_substr($line->text, 0, 78)
            ));

            if ($this->option('features')) {
                $this->line('        ' . implode(' ', $static[$i] ?? []));
            }
        }

        return self::SUCCESS;
    }
}
