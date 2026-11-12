<?php

namespace App\Http\Controllers;

use App\Mail\ActivateAlumniMail;
use App\Mail\DeactAlumniMail;
use App\Models\Alumnus;
use App\Models\Program;
use App\Models\User;
use App\Services\StoredFileCleaner;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class AlumnusController extends Controller
{
    /** Gate for the admin-only account actions below — see UserController::authorizeStaff() for why this exists. */
    private function authorizeStaff(): void
    {
        abort_unless(Auth::check() && in_array(Auth::user()->user_role, ['admin', 'super_admin'], true), 403);
    }

    /**
     * Alumni Directory — search/filter over alumni who opted into a public
     * profile (Alumnus::scopePublicProfiles()). Alumni-only: this is the
     * "browse each other" page, not an admin listing (that's
     * superAdmin.userManagement). Shared by index() (full page) and
     * directoryFragment() (live-search AJAX) so the two can never filter
     * differently from each other.
     */
    private function filteredAlumniQuery(Request $request)
    {
        $query = Alumnus::with(['user', 'program', 'industry', 'skills'])
            ->publicProfiles();

        if ($search = trim((string) $request->input('search'))) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('user_first_name', 'like', "%{$search}%")
                    ->orWhere('user_last_name', 'like', "%{$search}%")
                    ->orWhere('user_middle_name', 'like', "%{$search}%");
            });
        }

        if ($programId = $request->input('program')) {
            $query->where('program_id', $programId);
        }

        if ($batch = $request->input('batch')) {
            $query->whereYear('alumnus_batch', $batch);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_role === 'alumni', 403);

        $alumni = $this->filteredAlumniQuery($request)
            ->orderByDesc('alumnus_batch')
            ->paginate($this->resolvePerPage(10))
            ->withQueryString();

        $programs = Program::orderBy('program_name')->get();
        // Batch filter dropdown stays a year pick — DISTINCT YEAR(...) since
        // alumnus_batch is now a full date, not distinct exact dates.
        $batches = Alumnus::publicProfiles()
            ->whereNotNull('alumnus_batch')
            ->selectRaw('DISTINCT YEAR(alumnus_batch) as year')
            ->orderByDesc('year')
            ->pluck('year');
        $filters = $request->only(['search', 'program', 'batch']);

        return view('alumni.directory', compact('alumni', 'programs', 'batches', 'filters'));
    }

    /**
     * Live-search fragment — the search box on the directory fetches this
     * as the user types (see partials/table-pagination-bar.blade.php's
     * 'ajax' mode + pvSearchAjax()) instead of navigating the whole page.
     * Same abort_unless() as index() since this exposes the same data.
     */
    public function directoryFragment(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_role === 'alumni', 403);

        $alumni = $this->filteredAlumniQuery($request)
            ->orderByDesc('alumnus_batch')
            ->paginate($this->resolvePerPage(10))
            ->withQueryString();

        return view('partials.alumni-directory-table', compact('alumni'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Admin/super_admin "View Alumni" page — read-only profile summary
     * linked from the dropdown in superAdmin.userManagement's alumni tab.
     */
    public function show(Alumnus $alumnus)
    {
        abort_unless(in_array(Auth::user()->user_role, ['admin', 'super_admin']), 403);

        $alumnus->load([
            'user', 'program', 'industry',
            'skills', 'experiences.industry', 'certifications',
            'alumniId', 'yearbook',
        ]);

        return view('superAdmin.alumniProfile', compact('alumnus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Alumnus $alumnus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Alumnus $alumnus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Alumnus $alumnus)
    {
        //
    }
    public function updateAlumniProfile(Request $request, $alumnus)
    {
        // Self-service only — this route has no ownership check at all
        // previously, so any logged-in user could edit any other
        // alumnus's profile by changing the id in the URL/request. Only
        // resources/views/alumni/edit-profile.blade.php posts here, always
        // with the logged-in user's own id, so there's no legitimate case
        // (staff included) that needs this to accept a different id.
        abort_unless(Auth::check() && Auth::id() == $alumnus, 403);

        $user = User::where('user_id', $alumnus)->firstOrFail();

        // "Where do you work?" is a dropdown of this alumnus's own hires plus
        // "other". Building the allowed values from their own hiring options
        // is also the ownership check — someone else's application id simply
        // isn't in the list, so it can't be claimed as a workplace.
        $hiringOptionIds = Alumnus::where('user_id', $alumnus)->firstOrFail()
            ->hiringOptions()
            ->pluck('application_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $validated = $request->validate([
            'user_profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'alumnus_resume_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'alumnus_resume_backup_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'alumnus_cover_letter_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'alumnus_employment_status' => 'required|boolean|max:255',
            'industry_id' => 'required_if:alumnus_employment_status,1|nullable|exists:industries,industry_id',
            // Either one of this alumnus's own hire ids, or "other" for a job
            // that didn't come from this system.
            'alumnus_workplace_source' => ['nullable', 'string', Rule::in(array_merge(['other'], $hiringOptionIds))],
            // Only used when the source is "other" — a selected hire's company
            // name comes from the posting, never from the form.
            'alumnus_workplace_other' => 'nullable|string|max:255',
            'alumnus_workplace_undisclosed' => 'nullable|boolean',
            'alumnus_job_position' => 'nullable|string|max:255',
            'alumnus_employment_date' => 'nullable|date|before_or_equal:today',
            'alumnus_first_job_date' => 'nullable|date|before_or_equal:today',
            'alumnus_first_job_is_internship' => 'nullable|boolean',
            'user_email' => 'required|email|unique:users,user_email,' . $user->user_id . ',user_id',
            'user_number' => 'nullable|string|max:20',
            'alumnus_show_skills' => 'nullable|boolean',
            'alumnus_show_email' => 'nullable|boolean',
            'alumnus_show_linkedin' => 'nullable|boolean',
        ], [], [
            // Without these, a missing/invalid field reads as "The industry
            // id field is required." — the raw attribute name instead of
            // the label actually shown next to that input.
            'user_profile_picture' => 'Profile Picture',
            'alumnus_resume_file' => 'Resume / CV',
            'alumnus_resume_backup_file' => 'Backup Resume / CV',
            'alumnus_cover_letter_file' => 'Cover Letter',
            'alumnus_employment_status' => 'Employment Status',
            'industry_id' => 'Industry / Sector',
            'alumnus_workplace_source' => 'Where Do You Work',
            'alumnus_workplace_other' => 'Workplace',
            'alumnus_job_position' => 'Job Position',
            'alumnus_employment_date' => 'Employment Date',
            'alumnus_first_job_date' => 'First Job Date',
            'user_email' => 'Email',
            'user_number' => 'Phone Number',
        ]);

        // Old files are only removed AFTER the update below succeeds (see the
        // StoredFileCleaner call at the end) — deleting them up front left the
        // profile pointing at a missing file whenever the update failed.
        $oldProfilePicture = $user->user_profile_picture ?? null;
        $profilePicture = null;
        if ($request->hasFile('user_profile_picture')) {
            $profilePicture = $request->file('user_profile_picture')->store('profilePictures', 'public');
        }

        $alumniForUploads = Alumnus::where('user_id', $alumnus)->firstOrFail();

        // A "Remove" click (see edit-profile.blade.php) flags the slot via
        // a hidden remove_* input instead of deleting anything itself —
        // same "staged until Save" pattern as every other field on this
        // form, so Remove can still be undone before saving. A new file in
        // the same slot always wins over a stale remove flag (belt-and-
        // suspenders; the UI already clears the flag on file select).
        $oldResumeFile = $alumniForUploads->alumnus_resume_file_path;
        $resumeFile = null;
        $removeResumeFile = false;
        if ($request->hasFile('alumnus_resume_file')) {
            $resumeFile = $request->file('alumnus_resume_file')->store('alumniDocuments/resumes', 'public');
        } elseif ($request->boolean('remove_alumnus_resume_file') && $oldResumeFile) {
            $removeResumeFile = true;
        }

        $oldResumeBackupFile = $alumniForUploads->alumnus_resume_backup_file_path;
        $resumeBackupFile = null;
        $removeResumeBackupFile = false;
        if ($request->hasFile('alumnus_resume_backup_file')) {
            $resumeBackupFile = $request->file('alumnus_resume_backup_file')->store('alumniDocuments/resumes', 'public');
        } elseif ($request->boolean('remove_alumnus_resume_backup_file') && $oldResumeBackupFile) {
            $removeResumeBackupFile = true;
        }

        $oldCoverLetterFile = $alumniForUploads->alumnus_cover_letter_file_path;
        $coverLetterFile = null;
        $removeCoverLetterFile = false;
        if ($request->hasFile('alumnus_cover_letter_file')) {
            $coverLetterFile = $request->file('alumnus_cover_letter_file')->store('alumniDocuments/coverLetters', 'public');
        } elseif ($request->boolean('remove_alumnus_cover_letter_file') && $oldCoverLetterFile) {
            $removeCoverLetterFile = true;
        }

        try {
            DB::transaction(function () use (
                $validated, $alumnus, $profilePicture,
                $resumeFile, $removeResumeFile,
                $resumeBackupFile, $removeResumeBackupFile,
                $coverLetterFile, $removeCoverLetterFile,
                $request
            ) {
                $alumni = Alumnus::where('user_id', $alumnus)->firstOrFail();

                $employed = (bool) ($validated['alumnus_employment_status'] ?? $alumni->alumnus_employment_status);
                $workplaceUndisclosed = $employed && !empty($validated['alumnus_workplace_undisclosed']);

                // "Where do you work?" is either one of this alumnus's own
                // hires here, or "other" (a job from outside the system).
                // Already restricted to their own hire ids by the Rule::in
                // above; re-read from the database anyway so the company,
                // title and date come from the posting itself rather than
                // from anything the form could have been made to submit.
                $source = $validated['alumnus_workplace_source'] ?? null;
                $selectedHire = $employed && $source !== null && $source !== 'other'
                    ? $alumni->jobApplications()->with('job')
                        ->where('application_id', $source)
                        ->where('application_status', 'hired')
                        ->first()
                    : null;

                if ($employed && !$request->has('alumnus_workplace_source')) {
                    // The form always submits this field. Its absence means the
                    // request didn't come from the current form at all (a page
                    // loaded before this field existed, say), and treating that
                    // as "no workplace chosen" would quietly erase a perfectly
                    // good employer — so leave the workplace exactly as it is.
                    $workplace = $alumni->alumnus_workplace;
                    $jobPosition = $alumni->alumnus_job_position;
                    $employmentDate = $alumni->alumnus_employment_date;
                    $selectedHire = $alumni->hiredApplication;
                } elseif (!$employed) {
                    $workplace = null;
                    $jobPosition = null;
                    $employmentDate = null;
                } elseif ($selectedHire?->job) {
                    $workplace = $selectedHire->job->job_posting_company;
                    $jobPosition = $selectedHire->job->job_posting_title;
                    $employmentDate = optional($selectedHire->hired_at ?? $selectedHire->application_date)->toDateString();
                } else {
                    $workplace = !empty($validated['alumnus_workplace_other']) ? $validated['alumnus_workplace_other'] : null;
                    $jobPosition = !empty($validated['alumnus_job_position']) ? $validated['alumnus_job_position'] : null;
                    $employmentDate = !empty($validated['alumnus_employment_date']) ? $validated['alumnus_employment_date'] : null;
                }

                $alumni->update([
                    'alumnus_employment_status' => $employed,
                    // Now means "the workplace on this profile is one of my
                    // hires here", which is exactly whether a hire is selected.
                    'alumnus_employed_via_platform' => $selectedHire !== null,
                    'alumnus_hired_application_id' => $selectedHire?->application_id,
                    'industry_id' => $employed && !empty($validated['industry_id']) ? $validated['industry_id'] : null,
                    // Kept even while undisclosed: that flag only hides the
                    // name on public pages (profile.blade.php swaps in "Not
                    // disclosed"), so clearing the column here meant toggling
                    // the button off and on again lost the employer for good.
                    'alumnus_workplace' => $workplace,
                    'alumnus_workplace_undisclosed' => $workplaceUndisclosed,
                    'alumnus_job_position' => $jobPosition,
                    'alumnus_employment_date' => $employmentDate,
                    // Settable exactly once, and only by the alumnus: being
                    // hired here no longer fills it in (see
                    // JobApplicationController::hireApplication()), since the
                    // system can't tell which offer someone actually took.
                    // Once set, incoming values are ignored rather than
                    // overwriting the real first date.
                    'alumnus_first_job_date' => $alumni->alumnus_first_job_date
                        ?? (empty($validated['alumnus_first_job_date']) ? null : $validated['alumnus_first_job_date']),
                    // Same set-once lifecycle as alumnus_first_job_date
                    // itself — the checkbox only ever appears in the form
                    // alongside that still-editable date field, so it's only
                    // meaningful the one time the date is actually being set.
                    'alumnus_first_job_is_internship' => $alumni->alumnus_first_job_date
                        ? $alumni->alumnus_first_job_is_internship
                        : (empty($validated['alumnus_first_job_date']) ? null : !empty($validated['alumnus_first_job_is_internship'])),
                    // Directory visibility toggles — plain checkboxes, so an
                    // unchecked box sends nothing at all rather than "false".
                    // $request->boolean() treats that absence as false
                    // correctly instead of falling back to the old value.
                    'alumnus_show_skills' => $request->boolean('alumnus_show_skills'),
                    'alumnus_show_email' => $request->boolean('alumnus_show_email'),
                    'alumnus_show_linkedin' => $request->boolean('alumnus_show_linkedin'),
                ]);

                $alumni->user->update([

                    'user_email' => $validated['user_email'] ?? $alumni->user->user_email,
                    'user_number' => $validated['user_number'] ?? $alumni->user->user_number,
                ]);

                if ($profilePicture != null) {
                    $alumni->user->update([
                        'user_profile_picture' => $profilePicture,
                    ]);
                }

                if ($resumeFile != null) {
                    $alumni->update(['alumnus_resume_file_path' => $resumeFile]);
                } elseif ($removeResumeFile) {
                    $alumni->update(['alumnus_resume_file_path' => null]);
                }

                if ($resumeBackupFile != null) {
                    $alumni->update(['alumnus_resume_backup_file_path' => $resumeBackupFile]);
                } elseif ($removeResumeBackupFile) {
                    $alumni->update(['alumnus_resume_backup_file_path' => null]);
                }

                if ($coverLetterFile != null) {
                    $alumni->update(['alumnus_cover_letter_file_path' => $coverLetterFile]);
                } elseif ($removeCoverLetterFile) {
                    $alumni->update(['alumnus_cover_letter_file_path' => null]);
                }
            });
        } catch (\Exception $e) {
            if ($profilePicture) {
                Storage::disk('public')->delete($profilePicture);
            }
            if ($resumeFile) {
                Storage::disk('public')->delete($resumeFile);
            }
            if ($resumeBackupFile) {
                Storage::disk('public')->delete($resumeBackupFile);
            }
            if ($coverLetterFile) {
                Storage::disk('public')->delete($coverLetterFile);
            }

            return redirect()->route('user.profile')->with('error', 'An error occurred while updating your profile: ' . $e->getMessage());
        }

        // Saved — now drop whatever was replaced or removed (only if no other
        // record shares the file; see StoredFileCleaner).
        app(StoredFileCleaner::class)->deleteIfUnused(
            $profilePicture ? $oldProfilePicture : null,
            ($resumeFile || $removeResumeFile) ? $oldResumeFile : null,
            ($resumeBackupFile || $removeResumeBackupFile) ? $oldResumeBackupFile : null,
            ($coverLetterFile || $removeCoverLetterFile) ? $oldCoverLetterFile : null,
        );

        return redirect()->route('user.profile')->with('success', 'Alumni profile updated successfully.');
    }

    public function deactivateAlumnus(Request $request, $id)
    {
        $this->authorizeStaff();
        $alumnus = Alumnus::where('user_id', $id)->firstOrFail();

        $validated = $request->validate([
            'deactivate-reason' => 'required|string|max:255',
        ]);

        $alumnus->user->update([
            'user_active' => false,
        ]);
        Log::info("Alumnus with ID {$alumnus->user->user_id}: {$alumnus->user->user_first_name} {$alumnus->user->user_last_name} deactivated. Reason: {$request['deactivate-reason']}");

        // Queued, not sent inline — this used to wrap the DB update AND the
        // mail send in the same try/catch, so an SMTP failure made the
        // catch return "An error occurred... Please try again later" even
        // though the account was already deactivated. Queuing (matching
        // JobPostingController's approve/decline/delete — see comment
        // there) removes mail delivery from this request's success path
        // entirely.
        Mail::to($alumnus->user->user_email)->queue(new DeactAlumniMail($alumnus->user, $request['deactivate-reason']));

        return back()->with('success', 'Alumnus deactivated successfully!');
    }

    public function activateAlumnus(Request $request, $id)
    {
        $this->authorizeStaff();
        $alumnus = Alumnus::where('user_id', $id)->firstOrFail();

        if (!$alumnus->user->user_active) {
            $alumnus->user->update([
                'user_active' => true,
            ]);
            Log::info("Alumnus with ID {$alumnus->user->user_id}: {$alumnus->user->user_first_name} {$alumnus->user->user_last_name} activated.");

            Mail::to($alumnus->user->user_email)->queue(new ActivateAlumniMail($alumnus->user));

            return back()->with('success', 'Alumnus activated successfully!');
        }

        return back()->with('error', 'Alumnus is already activated!');
    }
}