<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    //
    protected $primaryKey = 'application_id';

    protected $fillable = [
        'job_id',
        'alumnus_id',
        'application_date',
        'application_status',
        'hired_at',
        'application_score',
        'is_read',
        'resume_source',
        'resume_path',
        'cover_letter_source',
        'cover_letter_path',
        'builder_resume_snapshot',
    ];

    protected $casts = [
        'application_date' => 'datetime',
        'hired_at' => 'datetime',
        'application_score' => 'decimal:2',
        'is_read' => 'boolean',
        'builder_resume_snapshot' => 'array',
    ];

    /**
     * Stamp application_date when a caller doesn't supply one (applyJob and
     * JobApplicationSeeder don't). The column is NOT NULL, and whether the
     * database fills it in by itself depends on the server: XAMPP's MariaDB
     * does, Hostinger's doesn't and rejects the insert with error 1364. The
     * 2026_09_22 migration also gives the column an explicit default; this
     * keeps inserts working even on a database that hasn't run it yet.
     */
    protected static function booted(): void
    {
        static::creating(function (JobApplication $application) {
            $application->application_date ??= now();
        });
    }

    public function job()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(JobPosting::class, 'job_id', 'job_posting_id');
    }
    public function alumnus()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'user_id');
    }

    public function scopeForJob($query, $jobId)
    {
        return $query->where('job_id', $jobId);
    }
 
    public function scopeRankedByScore($query)
    {
        return $query->orderByDesc('application_score');
    }
 
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('application_status', $status);
    }

    /**
     * The employer dashboard's applicant filters, also carried to My Job
     * Postings and the Applicants page when a widget is clicked:
     *   year       — the year the application came in (PH calendar year)
     *   batch      — the applicant's Year Graduated
     *   college    — the applicant's course's college (Program::COLLEGES code)
     *   program_id — the applicant's course
     * Empty lists are ignored.
     */
    public function scopeMatchingApplicantFilters($query, array $filters)
    {
        if (!empty($filters['year'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['year'] as $year) {
                    [$from, $to] = self::localYearBounds((int) $year);
                    $q->orWhere(fn ($r) => $r->where('job_applications.application_date', '>=', $from)->where('job_applications.application_date', '<', $to));
                }
            });
        }

        if (!empty($filters['batch']) || !empty($filters['college']) || !empty($filters['program_id'])) {
            $query->whereHas('alumnus', function ($a) use ($filters) {
                if (!empty($filters['batch'])) {
                    $a->whereIn(\Illuminate\Support\Facades\DB::raw('YEAR(alumnus_batch)'), $filters['batch']);
                }
                if (!empty($filters['program_id'])) {
                    $a->whereIn('program_id', $filters['program_id']);
                }
                if (!empty($filters['college'])) {
                    $a->whereIn('program_id', Program::whereIn('college', $filters['college'])->pluck('program_id')->all());
                }
            });
        }

        return $query;
    }

    /**
     * A PH-time calendar year as [start, end) timestamps in the stored
     * (app.timezone) clock — e.g. 2026 = 2025-12-31 16:00:00 to
     * 2026-12-31 16:00:00 UTC — so "applied in 2026" means the local year.
     */
    public static function localYearBounds(int $year): array
    {
        $start = \Carbon\Carbon::create($year, 1, 1, 0, 0, 0, config('app.local_timezone'));

        return [
            $start->copy()->setTimezone(config('app.timezone'))->toDateTimeString(),
            $start->copy()->addYear()->setTimezone(config('app.timezone'))->toDateTimeString(),
        ];
    }

    /**
     * The dashboard's applicant filters read from a request, cleaned up:
     * only real years (up to this one), positive ids and real college codes.
     *
     * @return array{year: int[], batch: int[], college: string[], program_id: int[]}
     */
    public static function applicantFiltersFrom(\Illuminate\Http\Request $request): array
    {
        $service = app(\App\Services\DashboardReportService::class);
        $currentYear = now(config('app.local_timezone'))->year;
        $years = array_values(array_filter($service->resolveIntArray($request->input('year')), fn ($y) => $y >= 2000 && $y <= $currentYear));
        sort($years);

        return [
            'year' => $years,
            'batch' => $service->resolveIntArray($request->input('batch')),
            'college' => $service->resolveColleges($request->input('college')),
            'program_id' => $service->resolveIntArray($request->input('program_id')),
        ];
    }

    /** "Applied in: 2026", "Course: BSIT - …" labels for the filters in use. */
    public static function applicantFilterLabels(array $filters): array
    {
        return array_filter([
            'Applied in' => implode(', ', $filters['year'] ?? []),
            'Year Graduated' => implode(', ', $filters['batch'] ?? []),
            'College' => implode(', ', $filters['college'] ?? []),
            'Course' => !empty($filters['program_id']) ? Program::whereIn('program_id', $filters['program_id'])->get()->map->labeledName()->implode(', ') : '',
        ]);
    }
 
    /**
     * Freezes the current live job_matches score onto this application.
     * Call this exactly once, at the moment the alumnus applies.
     */
    public function freezeScoreFromMatch(): void
    {
        $match = JobMatch::where('job_posting_id', $this->job_id)
            ->where('alumnus_id', $this->alumnus_id)
            ->first();
 
        if ($match) {
            $this->application_score = $match->score;
            $this->save();
        }
    }
}
