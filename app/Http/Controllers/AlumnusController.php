<?php

namespace App\Http\Controllers;

use App\Mail\ActivateAlumniMail;
use App\Mail\DeactAlumniMail;
use App\Models\Alumnus;
use App\Models\Program;
use Illuminate\Foundation\Auth\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;


class AlumnusController extends Controller
{
    /**
     * Alumni Directory — search/filter over alumni who opted into a public
     * profile (Alumnus::scopePublicProfiles()). Alumni-only: this is the
     * "browse each other" page, not an admin listing (that's
     * superAdmin.userManagement).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_role === 'alumni', 403);

        $query = Alumnus::with(['user', 'program', 'section', 'industry', 'skills'])
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
            $query->where('alumnus_batch', $batch);
        }

        $alumni = $query->orderByDesc('alumnus_batch')->paginate(10)->withQueryString();

        $programs = Program::orderBy('program_name')->get();
        $batches = Alumnus::publicProfiles()->distinct()->orderByDesc('alumnus_batch')->pluck('alumnus_batch');
        $filters = $request->only(['search', 'program', 'batch']);

        return view('alumni.directory', compact('alumni', 'programs', 'batches', 'filters'));
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
            'user', 'program', 'section', 'industry',
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

        $user = User::where('user_id', $alumnus)->firstOrFail();
        $validated = $request->validate([
            'user_profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'alumnus_employment_status' => 'required|boolean|max:255',
            'industry_id' => 'required_if:alumnus_employment_status,1|nullable|exists:industries,industry_id',
            'alumnus_workplace' => 'nullable|string|max:255',
            'alumnus_workplace_undisclosed' => 'nullable|boolean',
            'alumnus_job_position' => 'nullable|string|max:255',
            'alumnus_employment_date' => 'nullable|date|before_or_equal:today',
            'alumnus_first_job_date' => 'nullable|date|before_or_equal:today',
            'user_email' => 'required|email|unique:users,user_email,' . $user->user_id . ',user_id',
            'user_number' => 'nullable|string|max:20',
        ]);

        $oldProfilePicture = $user->user_profile_picture ?? null;
        $profilePicture = null;
        if ($request->hasFile('user_profile_picture')) {
            if ($oldProfilePicture && Storage::disk('public')->exists($oldProfilePicture)) {
                Storage::disk('public')->delete($oldProfilePicture);
            }
            $profilePicture = $request->file('user_profile_picture')->store('profilePictures', 'public');
        }

        try {
            DB::transaction(function () use ($validated, $alumnus, $profilePicture) {
                $alumni = Alumnus::where('user_id', $alumnus)->firstOrFail();

                $employed = (bool) ($validated['alumnus_employment_status'] ?? $alumni->alumnus_employment_status);
                $workplaceUndisclosed = $employed && !empty($validated['alumnus_workplace_undisclosed']);

                // Job position + employment date are system-of-record once
                // hired through an in-system job posting (see
                // JobApplicationController::hireApplication()) — this
                // self-service form can't overwrite them while that's still
                // true. The only way it clears is marking yourself
                // unemployed; a later "Employed" self-edit after that is by
                // definition a job that didn't come from the platform.
                $viaPlatform = $employed && $alumni->alumnus_employed_via_platform;

                $alumni->update([
                    'alumnus_employment_status' => $employed,
                    'alumnus_employed_via_platform' => $viaPlatform,
                    'industry_id' => $employed && !empty($validated['industry_id']) ? $validated['industry_id'] : null,
                    // Not required — alumni can share their industry without
                    // naming the employer, or skip it entirely.
                    'alumnus_workplace' => $employed && !$workplaceUndisclosed && !empty($validated['alumnus_workplace'])
                        ? $validated['alumnus_workplace']
                        : null,
                    'alumnus_workplace_undisclosed' => $workplaceUndisclosed,
                    'alumnus_job_position' => $viaPlatform
                        ? $alumni->alumnus_job_position
                        : ($employed && !empty($validated['alumnus_job_position']) ? $validated['alumnus_job_position'] : null),
                    'alumnus_employment_date' => $viaPlatform
                        ? $alumni->alumnus_employment_date
                        : ($employed && !empty($validated['alumnus_employment_date']) ? $validated['alumnus_employment_date'] : null),
                    // Settable exactly once — for alumni whose job didn't
                    // come through the system (the system itself sets this
                    // automatically on hire, see JobApplicationController::
                    // hireApplicant()). Once set, incoming values are
                    // ignored rather than overwriting the real first date.
                    'alumnus_first_job_date' => $alumni->alumnus_first_job_date
                        ?? (empty($validated['alumnus_first_job_date']) ? null : $validated['alumnus_first_job_date']),
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
            });
        } catch (\Exception $e) {
            if ($profilePicture) {
                Storage::disk('public')->delete($profilePicture);
            }

            return redirect()->route('user.profile')->with('error', 'An error occurred while updating your profile: ' . $e->getMessage());
        }

        return redirect()->route('user.profile')->with('success', 'Alumni profile updated successfully.');
    }

    public function deactivateAlumnus(Request $request, $id)
    {
        $alumnus = Alumnus::where('user_id', $id)->firstOrFail();

        $validated = $request->validate([
            'deactivate-reason' => 'required|string|max:255',
        ]);

        try {
            $alumnus->user->update([
                'user_active' => false,
            ]);
            Log::info("Alumnus with ID {$alumnus->user->user_id}: {$alumnus->user->user_first_name} {$alumnus->user->user_last_name} deactivated. Reason: {$request['deactivate-reason']}");

            Mail::to($alumnus->user->user_email)->send(new DeactAlumniMail($alumnus->user, $request['deactivate-reason']));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while deactivating the alumnus. Please try again later.');
        }

        return back()->with('success', 'Alumnus deactivated successfully!');
    }

    public function activateAlumnus(Request $request, $id)
    {
        $alumnus = Alumnus::where('user_id', $id)->firstOrFail();


        if (!$alumnus->user->user_active) {
            try {
                $alumnus->user->update([
                    'user_active' => true,
                ]);
                Log::info("Alumnus with ID {$alumnus->user->user_id}: {$alumnus->user->user_first_name} {$alumnus->user->user_last_name} deactivated. Reason: {$request['deactivate-reason']}");

                Mail::to($alumnus->user->user_email)->send(new ActivateAlumniMail($alumnus->user));
            } catch (\Exception $e) {
                return back()->with('error', 'An error occurred while activating the alumnus. Please try again later.');
            }
            return back()->with('success', 'Alumnus activated successfully!');
        }

        return back()->with('error', 'Alumnus is already activated!');
    }
}