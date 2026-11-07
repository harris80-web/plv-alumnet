<?php

namespace App\Console\Commands;

use App\Support\SeedArt;
use Illuminate\Console\Command;

/**
 * Generates the image pools the realistic seeder (RealisticSeeder) draws
 * from, into seed-images/generated/:
 *   profiles/female-N.png, profiles/male-N.png — formal portrait illustrations
 *   logos/logo-N.png                           — company emblem logos
 * Existing files are kept, so re-running only fills in what's missing.
 */
class GenerateSeedArt extends Command
{
    protected $signature = 'seed:generate-art
        {--female=650 : Female portraits to generate}
        {--male=650 : Male portraits to generate}
        {--logos=110 : Company logos to generate}';

    protected $description = 'Generate the portrait and logo image pools used by the realistic seeder';

    public function handle()
    {
        $base = base_path('seed-images/generated');
        foreach (['profiles', 'logos'] as $dir) {
            if (!is_dir("$base/$dir")) mkdir("$base/$dir", 0775, true);
        }

        $female = (int) $this->option('female');
        $male = (int) $this->option('male');
        $logos = (int) $this->option('logos');
        $bar = $this->output->createProgressBar($female + $male + $logos);
        $bar->start();
        $made = 0;

        for ($i = 1; $i <= $female; $i++, $bar->advance()) {
            $path = "$base/profiles/female-$i.png";
            if (!is_file($path)) { SeedArt::portrait($path, 100000 + $i, 'female'); $made++; }
        }
        for ($i = 1; $i <= $male; $i++, $bar->advance()) {
            $path = "$base/profiles/male-$i.png";
            if (!is_file($path)) { SeedArt::portrait($path, 200000 + $i, 'male'); $made++; }
        }
        for ($i = 1; $i <= $logos; $i++, $bar->advance()) {
            $path = "$base/logos/logo-$i.png";
            if (!is_file($path)) { SeedArt::logo($path, 'Logo ' . $i, 300000 + $i); $made++; }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Generated {$made} new image(s) in seed-images/generated/ (existing files kept).");

        return self::SUCCESS;
    }
}
