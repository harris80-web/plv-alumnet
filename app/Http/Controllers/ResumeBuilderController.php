<?php

namespace App\Http\Controllers;

use App\Models\Alumnus;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Industry;
use App\Models\JobApplication;
use App\Models\Skill;
use App\Models\User;
use App\Services\GeminiResumeParser;
use App\Services\JobMatchService;
use App\Services\ResumeTextParser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfTextParser;

class ResumeBuilderController extends Controller
{
    /**
     * Reads text out of an uploaded PDF and extracts structured fields from
     * it. Returns them as JSON — nothing is saved here. The wizard prefills
     * itself from the response so the alumnus reviews and edits before
     * anything actually hits the database, same as any other prefill (see
     * toResumeFormArray()).
     *
     * Tries Gemini first (free tier — see GeminiResumeParser) for better
     * accuracy across varied resume formats, and falls back to the local
     * heuristic parser (ResumeTextParser) if no API key is configured or the
     * request fails for any reason, so importing never hard-depends on an
     * external service being up.
     */
    public function import(Request $request)
    {
        $request->validate([
            'resume_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        try {
            $text = (new PdfTextParser())->parseFile($request->file('resume_file')->getRealPath())->getText();
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Could not read that PDF. Make sure it\'s not scanned/image-only or password-protected.',
            ], 422);
        }

        if (trim($text) === '') {
            return response()->json([
                'message' => 'That PDF has no readable text (likely a scanned image). Try a different file.',
            ], 422);
        }

        $gemini = new GeminiResumeParser();
        $usedAi = false;

        if ($gemini->isConfigured()) {
            try {
                $parsed = $gemini->parse($text);
                $usedAi = true;
            } catch (\Throwable $e) {
                Log::warning('Gemini resume parse failed, falling back to heuristic parser: ' . $e->getMessage());
            }
        }

        $parsed ??= (new ResumeTextParser())->parse($text);

        return response()->json($parsed + ['parsed_with' => $usedAi ? 'ai' : 'heuristic']);
    }

    /**
     * Real, text-based PDF (not a screenshot) — renders the same data as
     * the on-screen resume through a plain-CSS template, since dompdf
     * doesn't run the Tailwind CDN's browser-side JIT compiler.
     */
    public function downloadPdf()
    {
        [$pdf, $filename] = $this->buildResumePdf(Auth::id());

        return $pdf->download($filename);
    }

    /**
     * Lets an employer open (not download) the resume of an alumnus who has
     * actually applied to one of their job postings — the same PDF template
     * as the alumnus's own download, streamed inline so it opens in the
     * browser's PDF viewer instead of forcing a save dialog. Scoped to "has
     * applied to my job" rather than open to any employer, since a resume
     * is personal data that shouldn't be fetchable by guessing user ids.
     */
    public function viewApplicantResume($alumnusId)
    {
        abort_unless(Auth::check() && Auth::user()->user_role === 'employer', 403);

        $hasApplied = JobApplication::where('alumnus_id', $alumnusId)
            ->whereHas('job', fn ($q) => $q->where('user_id', Auth::id()))
            ->exists();
        abort_unless($hasApplied, 403);

        [$pdf, $filename] = $this->buildResumePdf($alumnusId);

        return $pdf->stream($filename);
    }

    private function buildResumePdf($userId): array
    {
        $user = User::with(['alumnus.program', 'alumnus.skills', 'alumnus.experiences.industry', 'alumnus.certifications'])
            ->findOrFail($userId);

        abort_if(($user->alumnus->alumnus_resume_completeness ?? 0) <= 0, 404);

        $pdf = Pdf::loadView('alumni.resume-pdf', compact('user'));
        $filename = Str::slug($user->alumnus->resumeFullName() ?: 'resume') . '-resume.pdf';

        return [$pdf, $filename];
    }

    /**
     * Show the resume builder, prefilled with whatever the alumnus has
     * already saved.
     */
    public function edit()
    {
        $alumnus = Alumnus::with(['skills', 'experiences', 'certifications'])
            ->findOrFail(Auth::id());

        $resumeData = $alumnus->toResumeFormArray();

        $industries = Industry::orderBy('industry_name')->get(['industry_id', 'industry_name']);

        return view('resume.builder', compact('resumeData', 'industries'));
    }

    /**
     * Persist the whole resume in one go. Skills/experiences/certifications
     * are replaced wholesale each save — simplest correct behavior for a
     * wizard where rows can be freely added/removed/reordered client-side.
     */
    public function save(Request $request)
    {
        // Let Alumnus type "linkedin.com/in/juandelacruz" without a scheme —
        // Laravel's `url` rule requires http(s):// to pass, so normalize
        // here before validating.
        $linkedin = $request->input('linkedin_url');
        if ($linkedin && ! preg_match('~^https?://~i', $linkedin)) {
            $request->merge(['linkedin_url' => 'https://' . $linkedin]);
        }

        $validated = $request->validate([
            'resume_summary' => ['nullable', 'string', 'max:500'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],

            'skills' => ['array'],
            'skills.*.name' => ['required_with:skills', 'string', 'max:100'],
            'skills.*.category' => ['nullable', 'in:' . implode(',', array_keys(Skill::CATEGORIES))],

            'experiences' => ['array'],
            'experiences.*.type' => ['required_with:experiences', 'in:work,project'],
            'experiences.*.job_title' => ['required_with:experiences', 'string', 'max:150'],
            'experiences.*.job_description' => ['nullable', 'string', 'max:2000'],
            // Item 21 — start/end date range is now the primary input; a
            // bare duration_months (no dates) still validates on its own so
            // PDF-imported experiences (the parser only extracts a duration,
            // not exact dates) keep working — see computeExperienceDuration().
            'experiences.*.start_date' => ['nullable', 'date'],
            'experiences.*.end_date' => ['nullable', 'date', 'after_or_equal:experiences.*.start_date'],
            'experiences.*.is_ongoing' => ['nullable', 'boolean'],
            'experiences.*.duration_months' => ['nullable', 'integer', 'min:0', 'max:600'],
            'experiences.*.industry_id' => ['nullable', 'exists:industries,industry_id'],

            'certifications' => ['array'],
            'certifications.*.certification_type' => ['required_with:certifications', 'in:certification,seminar,training'],
            'certifications.*.certification_name' => ['required_with:certifications', 'string', 'max:150'],
            'certifications.*.certification_from' => ['nullable', 'string', 'max:150'],
            'certifications.*.certification_date' => ['nullable', 'date'],
        ]);

        $alumnus = Alumnus::findOrFail(Auth::id());

        DB::transaction(function () use ($alumnus, $validated) {
            // ---- Summary & links ----
            $alumnus->alumnus_resume_summary = $validated['resume_summary'] ?? null;
            $alumnus->linkedin_url = $validated['linkedin_url'] ?? null;
            $alumnus->save();

            // ---- Skills: find-or-create by name, then sync the pivot ----
            // Item 22: a category submitted alongside a NEW skill name is
            // used as that skill's category at creation time; firstOrCreate's
            // second array is ignored when the row already exists, so an
            // already-categorized skill picked from search keeps its real
            // category regardless of what the client happened to send.
            $skillsByName = collect($validated['skills'] ?? [])
                ->filter(fn ($s) => filled($s['name'] ?? null))
                ->unique(fn ($s) => mb_strtolower(trim($s['name'])));

            $skillIds = $skillsByName->map(function ($s) {
                $skill = Skill::firstOrCreate(
                    ['skill_name' => trim($s['name'])],
                    ['skill_category' => $s['category'] ?? 'domain']
                );
                return $skill->skill_id;
            });

            $alumnus->skills()->sync($skillIds);

            // ---- Experience & projects: replace wholesale ----
            $alumnus->experiences()->delete();
            foreach ($validated['experiences'] ?? [] as $exp) {
                $startDate = $exp['start_date'] ?? null ?: null;
                $isOngoing = filter_var($exp['is_ongoing'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $endDate = ($startDate && !$isOngoing) ? ($exp['end_date'] ?? null ?: null) : null;

                Experience::create([
                    'alumnus_id' => $alumnus->user_id,
                    'experience_type' => $exp['type'],
                    'experience_job_title' => $exp['job_title'],
                    'experience_job_description' => $exp['job_description'] ?? null,
                    'experience_start_date' => $startDate,
                    'experience_end_date' => $endDate,
                    // Use ?: (not ??) here: these fields are always present
                    // in the submitted array, just possibly empty strings
                    // when left blank in the form. ?? only catches a missing
                    // key, not an empty value. `experience_duration_months`
                    // is NOT NULL (default 0), so an explicit '' -> null
                    // still violates the column — fall back to 0 instead.
                    // `industry_id` is a nullable FK, so null is the correct
                    // "not specified" value there.
                    //
                    // Item 21 — auto-derived from the date range whenever a
                    // start date is given (the normal path now); falls back
                    // to a bare submitted duration_months for rows that only
                    // ever had one (e.g. still-unedited PDF-imported
                    // experiences, since the parser doesn't extract dates).
                    'experience_duration_months' => $startDate
                        ? $this->computeExperienceDurationMonths($startDate, $endDate)
                        : (($exp['duration_months'] ?? null) ?: 0),
                    'industry_id' => ($exp['industry_id'] ?? null) ?: null,
                ]);
            }

            // ---- Certifications & seminars: replace wholesale ----
            $alumnus->certifications()->delete();
            foreach ($validated['certifications'] ?? [] as $cert) {
                Certification::create([
                    'alumnus_id' => $alumnus->user_id,
                    'certification_type' => $cert['certification_type'],
                    'certification_name' => $cert['certification_name'],
                    'certification_from' => ($cert['certification_from'] ?? null) ?: null,
                    'certification_date' => ($cert['certification_date'] ?? null) ?: null,
                ]);
            }

            // ---- Recompute completeness (source of truth, server-side) ----
            // Alumnus::refreshResumeCompleteness() re-queries skills/experiences/
            // certifications itself, so no need to refresh/reload first.
            $alumnus->refreshResumeCompleteness();
        });

        $alumnus = $alumnus->fresh(['skills', 'experiences', 'certifications']);

        // Deterministic-only (no Gemini call) so saving the resume stays
        // fast — the dashboard's job recommendations are current for this
        // alumnus right away instead of waiting for the hourly schedule.
        // AI enrichment catches up on the next job-matches:recompute --ai run.
        app(JobMatchService::class)->refreshForAlumnus($alumnus);

        return response()->json([
            'resume_completeness' => $alumnus->alumnus_resume_completeness,
            'breakdown' => $alumnus->completenessBreakdown(),
        ]);
    }

    /** Item 21 — whole months between a start date and an end date (or today, if still ongoing). */
    private function computeExperienceDurationMonths(string $startDate, ?string $endDate): int
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = $endDate ? \Carbon\Carbon::parse($endDate) : now();

        return max(0, (int) $start->diffInMonths($end));
    }
}