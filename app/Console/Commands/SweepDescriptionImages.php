<?php

namespace App\Console\Commands;

use App\Services\StoredFileCleaner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes job-description images nothing uses any more. The rich-text
 * editor uploads an image the moment it's inserted
 * (JobPostingController::uploadDescriptionImage()), so a post that's never
 * saved — or an image deleted from the text before saving — leaves a file
 * behind that no edit/delete hook ever sees. Removals on edit and on job
 * delete are handled inline; this catches the rest.
 *
 * Only files older than --hours (default 24) are considered, so images in
 * a post someone is still writing are never touched.
 */
class SweepDescriptionImages extends Command
{
    protected $signature = 'files:sweep-description-images
        {--hours=24 : Only consider files at least this many hours old}
        {--dry-run : List what would be deleted without deleting anything}';

    protected $description = 'Delete job-description images that no job posting uses any more';

    public function handle(StoredFileCleaner $cleaner)
    {
        $disk = Storage::disk('public');
        $cutoff = now()->subHours(max(1, (int) $this->option('hours')))->getTimestamp();
        $dryRun = (bool) $this->option('dry-run');

        $checked = 0;
        $removed = [];
        foreach ($disk->files(StoredFileCleaner::DESCRIPTION_IMAGE_DIR) as $path) {
            if ($disk->lastModified($path) > $cutoff) {
                continue;
            }
            $checked++;
            if ($cleaner->isReferenced($path)) {
                continue;
            }
            if ($dryRun || $cleaner->deleteIfUnused($path)) {
                $removed[] = $path;
            }
        }

        foreach ($removed as $path) {
            $this->line(($dryRun ? 'would delete ' : 'deleted ') . $path);
        }
        $this->info(sprintf('%s %d unused description image(s) out of %d checked.', $dryRun ? 'Would delete' : 'Deleted', count($removed), $checked));

        return self::SUCCESS;
    }
}
