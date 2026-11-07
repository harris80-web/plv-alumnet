<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Deletes uploaded files from the public disk once nothing uses them any
 * more — on replace (the old file) and on delete (the record's files).
 *
 * "Nothing uses it" is checked across EVERY file column in the database,
 * not just the record's own: seeded data shares files between rows and
 * even between tables (e.g. jobImages/accounting.jpg on 17 job postings,
 * jobImages/softdev.png on job postings, notices and message attachments),
 * so deleting on sight would break every other record pointing at it.
 *
 * Soft-deleted rows don't count as using a file (nothing in the app shows
 * them) — except declined job postings, which the admin's "Declined Job
 * Posts" tab still shows with their image and description.
 *
 * Always call this AFTER the record has been saved/deleted, so its own row
 * no longer points at the file being cleaned up.
 */
class StoredFileCleaner
{
    /** table => columns holding a public-disk path. */
    private const FILE_COLUMNS = [
        'users' => ['user_profile_picture'],
        'employers' => ['employer_company_logo', 'employer_company_document'],
        'alumni' => ['alumnus_resume_file_path', 'alumnus_resume_backup_file_path', 'alumnus_cover_letter_file_path'],
        'job_postings' => ['job_posting_image'],
        'job_applications' => ['resume_path', 'cover_letter_path'],
        'notices' => ['thumbnail'],
        'messages' => ['attachment_path'],
    ];

    /** Folder the rich-text editor uploads description images to (JobPostingController::uploadDescriptionImage()). */
    public const DESCRIPTION_IMAGE_DIR = 'jobDescriptionImages';

    /** Deletes each path that no live record still uses. Returns how many files were removed. */
    public function deleteIfUnused(?string ...$paths): int
    {
        $deleted = 0;
        foreach (array_unique(array_filter($paths)) as $path) {
            $path = $this->normalize($path);
            if ($path === null || $this->isReferenced($path)) {
                continue;
            }
            if (Storage::disk('public')->exists($path) && Storage::disk('public')->delete($path)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /** Description-image paths (relative to the public disk) embedded in a job description's HTML. */
    public function descriptionImagePaths(?string $html): array
    {
        if (!$html) {
            return [];
        }
        preg_match_all('#/storage/(' . self::DESCRIPTION_IMAGE_DIR . '/[A-Za-z0-9._-]+)#', $html, $m);

        return array_values(array_unique($m[1]));
    }

    public function isReferenced(string $path): bool
    {
        foreach (self::FILE_COLUMNS as $table => $columns) {
            $query = $this->liveRows($table)->where(function ($q) use ($columns, $path) {
                foreach ($columns as $column) {
                    $q->orWhere($column, $path);
                }
            });
            if ($query->exists()) {
                return true;
            }
        }

        // Description images live inside job description HTML, not a column of their own.
        if (str_starts_with($path, self::DESCRIPTION_IMAGE_DIR . '/')) {
            return $this->liveRows('job_postings')
                ->where('job_posting_description', 'like', '%' . addcslashes($path, '%_\\') . '%')
                ->exists();
        }

        return false;
    }

    /** Rows that still "use" their files: not soft-deleted, or a declined (still-displayed) job posting. */
    private function liveRows(string $table)
    {
        $query = DB::table($table);

        if (in_array($table, ['users', 'employers', 'alumni'], true)) {
            $query->whereNull('deleted_at');
        } elseif ($table === 'job_postings') {
            $query->where(fn ($q) => $q->whereNull('deleted_at')->orWhereNotNull('job_decline_reason'));
        }

        return $query;
    }

    /**
     * Only plain relative paths on the public disk are ever deleted — never a
     * URL, an absolute path, a bundled asset or anything with "..".
     */
    private function normalize(string $path): ?string
    {
        $path = ltrim(str_replace('\\', '/', trim($path)), '/');
        if ($path === '' || str_contains($path, '..') || preg_match('#^[a-z]+://#i', $path) || str_starts_with($path, 'assets/')) {
            return null;
        }

        return $path;
    }
}
