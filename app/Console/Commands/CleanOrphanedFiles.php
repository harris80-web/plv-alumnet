<?php

namespace App\Console\Commands;

use App\Services\StoredFileCleaner;
use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * One-off / occasional cleanup of uploads nothing uses any more (files left
 * behind before StoredFileCleaner existed, from features that were turned
 * off, etc.). Ongoing cleanup on replace/delete happens inline — see
 * StoredFileCleaner — and abandoned description images are swept daily by
 * files:sweep-description-images.
 *
 * Dry run by default: lists what's unused and changes nothing. With --move,
 * unused files are MOVED (not deleted) to
 * storage/app/orphaned-files-backup/<timestamp>/<original path> with a
 * MANIFEST.txt, so the cleanup can be undone by moving files back. Delete
 * that backup folder once you're sure nothing is missing.
 *
 * A file is KEPT if any of these hold:
 *  - a live record uses it (StoredFileCleaner rules: soft-deleted rows don't
 *    count, declined job postings do)
 *  - its path or name appears in any other text column of any table, all
 *    rows included — catches references in tables StoredFileCleaner doesn't know
 *  - its name isn't a Laravel-generated random name (hand-named files are
 *    seed/demo assets — seeders reference e.g. jobImages/softdev.png)
 *  - its name appears in database/seeders, app/ or resources/
 *  - it was modified within --hours (could be an upload still in progress)
 */
class CleanOrphanedFiles extends Command
{
    protected $signature = 'files:clean-orphans
        {--move : Actually move the unused files to the backup folder (default is a dry run)}
        {--hours=24 : Ignore files modified more recently than this}
        {--list : Print every unused file, not just the per-folder summary}
        {--force : With --move, skip the confirmation prompt}';

    protected $description = 'Find uploaded files nothing uses any more (dry run), or move them to a backup folder with --move';

    /** Columns StoredFileCleaner already judges with live-row rules — left out of the raw text scan. */
    private const COVERED_COLUMNS = [
        'users.user_profile_picture', 'employers.employer_company_logo', 'employers.employer_company_document',
        'alumni.alumnus_resume_file_path', 'alumni.alumnus_resume_backup_file_path', 'alumni.alumnus_cover_letter_file_path',
        'job_postings.job_posting_image', 'job_postings.job_posting_description',
        'job_applications.resume_path', 'job_applications.cover_letter_path',
        'notices.thumbnail', 'messages.attachment_path',
    ];

    public function handle(StoredFileCleaner $cleaner)
    {
        $disk = Storage::disk('public');
        $cutoff = now()->subHours(max(1, (int) $this->option('hours')))->getTimestamp();

        $this->info('Scanning the database and source for file references...');
        $dbText = $this->otherDatabaseText();
        $sourceText = $this->sourceText();

        $kept = ['in use by a record' => 0, 'mentioned elsewhere in the database' => 0,
            'hand-named (seed/demo asset)' => 0, 'named in the source code' => 0, 'modified recently' => 0];
        $orphans = [];

        foreach ($disk->allFiles() as $path) {
            $name = basename($path);
            if ($name === '.gitignore') {
                continue;
            }
            if ($cleaner->isReferenced($path)) { $kept['in use by a record']++; continue; }
            if (str_contains($dbText, $path) || str_contains($dbText, $name)) { $kept['mentioned elsewhere in the database']++; continue; }
            if (!preg_match('/^[A-Za-z0-9]{40}\.[A-Za-z0-9]+$/', $name)) { $kept['hand-named (seed/demo asset)']++; continue; }
            if (str_contains($sourceText, $name)) { $kept['named in the source code']++; continue; }
            if ($disk->lastModified($path) > $cutoff) { $kept['modified recently']++; continue; }
            $orphans[] = $path;
        }

        $this->newLine();
        $this->line('<comment>Kept</comment>');
        foreach ($kept as $why => $n) {
            $this->line(sprintf('  %-38s %d', $why, $n));
        }

        $byFolder = [];
        $bytes = 0;
        foreach ($orphans as $path) {
            $folder = str_contains($path, '/') ? strstr($path, '/', true) : '(root)';
            $size = $disk->size($path);
            $byFolder[$folder] = [($byFolder[$folder][0] ?? 0) + 1, ($byFolder[$folder][1] ?? 0) + $size];
            $bytes += $size;
        }
        ksort($byFolder);

        $this->newLine();
        $this->line(sprintf('<comment>Unused: %d file(s), %.1f MB</comment>', count($orphans), $bytes / 1048576));
        $this->table(['Folder', 'Files', 'MB'], array_map(
            fn ($folder, $stats) => [$folder, $stats[0], number_format($stats[1] / 1048576, 1)],
            array_keys($byFolder), $byFolder
        ));

        if ($this->option('list')) {
            foreach ($orphans as $path) {
                $this->line('  ' . $path);
            }
        }

        if (!$orphans) {
            $this->info('Nothing to clean up.');
            return self::SUCCESS;
        }

        if (!$this->option('move')) {
            $this->warn('Dry run — nothing was moved. Run again with --move to move these to a backup folder.');
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Move these ' . count($orphans) . ' file(s) out of public storage into a backup folder?')) {
            $this->warn('Cancelled — nothing was moved.');
            return self::SUCCESS;
        }

        $backupRoot = storage_path('app/orphaned-files-backup/' . now()->format('Y-m-d_His'));
        $moved = [];
        foreach ($orphans as $path) {
            $target = $backupRoot . '/' . $path;
            if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0775, true) && !is_dir(dirname($target))) {
                $this->error('Could not create ' . dirname($target));
                continue;
            }
            if (@rename($disk->path($path), $target)) {
                $moved[] = $path;
            } else {
                $this->error('Could not move ' . $path);
            }
        }

        file_put_contents($backupRoot . '/MANIFEST.txt',
            'Moved out of storage/app/public by `php artisan files:clean-orphans --move` on ' . now() . "\n"
            . "To restore a file, move it back to storage/app/public/<same path>.\n"
            . "Delete this folder once you're sure nothing is missing.\n\n"
            . implode("\n", $moved) . "\n");

        $this->info(sprintf('Moved %d file(s) to %s', count($moved), $backupRoot));

        return count($moved) === count($orphans) ? self::SUCCESS : self::FAILURE;
    }

    /** Every text column NOT in COVERED_COLUMNS, all rows (incl. soft-deleted), as one searchable string. */
    private function otherDatabaseText(): string
    {
        $text = '';
        $columns = DB::select(
            "SELECT TABLE_NAME AS t, COLUMN_NAME AS c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND DATA_TYPE IN ('varchar','char','text','tinytext','mediumtext','longtext','json')",
            [DB::getDatabaseName()]
        );
        foreach ($columns as $col) {
            if (in_array("{$col->t}.{$col->c}", self::COVERED_COLUMNS, true)) {
                continue;
            }
            foreach (DB::table($col->t)->whereNotNull($col->c)->where($col->c, 'like', '%/%')->pluck($col->c) as $value) {
                $text .= "\n" . $value;
            }
        }

        return $text;
    }

    /** Seeders, app code and views — catches file names hard-coded there. */
    private function sourceText(): string
    {
        $text = '';
        foreach (['database/seeders', 'app', 'resources'] as $dir) {
            $path = base_path($dir);
            if (!is_dir($path)) {
                continue;
            }
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if ($file->isFile() && preg_match('/\.(php|js|css)$/', $file->getFilename())) {
                    $text .= "\n" . file_get_contents($file->getPathname());
                }
            }
        }

        return $text;
    }
}
