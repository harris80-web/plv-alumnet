<?php

namespace App\Console\Commands;

use App\Models\JobPosting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Copies dummy-data images from seed-images/ into the public disk, where the
 * app's own upload folders live, and optionally gives each example job post
 * an image of its course.
 *
 *   seed-images/jobs/      -> jobImages/
 *   seed-images/notices/   -> noticeThumbnails/
 *   seed-images/profiles/  -> profilePictures/
 *   seed-images/logos/     -> companyLogos/
 *
 * Re-running is safe: files are copied again, and job posts only get a new
 * image when their current one is missing or never existed.
 */
class ImportSeedImages extends Command
{
    protected $signature = 'images:import-seed
        {--assign-jobs : Give example job posts their course image (seed-images/jobs/course-<program id>.png)}';

    protected $description = 'Copy dummy-data images from seed-images/ into storage, and optionally assign course images to job posts';

    private const FOLDERS = [
        'jobs' => 'jobImages',
        'notices' => 'noticeThumbnails',
        'profiles' => 'profilePictures',
        'logos' => 'companyLogos',
    ];

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function handle()
    {
        $source = base_path('seed-images');
        $disk = Storage::disk('public');

        if (!is_dir($source)) {
            $this->error('No seed-images folder found at ' . $source);
            return self::FAILURE;
        }

        $copied = 0;
        foreach (self::FOLDERS as $from => $to) {
            foreach (File::files("$source/$from") as $file) {
                if (!in_array(strtolower($file->getExtension()), self::EXTENSIONS, true)) {
                    continue;
                }
                $disk->put($to . '/' . $file->getFilename(), File::get($file->getPathname()));
                $copied++;
            }
        }
        $this->info("Copied {$copied} image(s) into storage/app/public.");

        if ($this->option('assign-jobs')) {
            $this->assignCourseImages($disk);
        }

        return self::SUCCESS;
    }

    private function assignCourseImages($disk): void
    {
        $assigned = 0;
        $kept = 0;

        JobPosting::with('programs')->get()->each(function (JobPosting $job) use ($disk, &$assigned, &$kept) {
            $current = $job->job_posting_image;
            // Keep any image that isn't one of ours (a real photo, or a file you
            // uploaded) — only generated course/position images get replaced.
            $ours = $current && (str_starts_with($current, 'jobImages/course-') || str_starts_with($current, 'jobImages/pos-'));
            if ($current && $disk->exists($current) && !$ours) {
                $kept++;
                return;
            }

            $programId = optional($job->programs->first())->program_id ?? 0;
            $positionImage = 'jobImages/pos-' . $programId . '-' . Str::slug($job->job_posting_title) . '.png';
            $courseImage = "jobImages/course-{$programId}.png";

            // Most specific first: this course + this position, then the course.
            $target = $disk->exists($positionImage) ? $positionImage : (
                $disk->exists($courseImage) ? $courseImage : null
            );
            if (!$target) {
                return;
            }

            if ($target !== $current) {
                $job->update(['job_posting_image' => $target]);
                $assigned++;
            } else {
                $kept++;
            }
        });

        $this->info("Assigned course images to {$assigned} job post(s); {$kept} already had a working image.");
    }
}
