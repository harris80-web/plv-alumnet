<?php

namespace App\Http\Controllers;

use App\Mail\RejectEmployerMail;
use App\Models\User;
use App\Models\Employer;
use App\Models\Alumnus;
use App\Models\Office;
use App\Models\Program;
use App\Models\Section;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Mail\AlumniCreatedMail;
use App\Mail\DeactAlumniMail;
use App\Models\Industry;
use App\Models\JobApplication;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    public function storeEmployer(Request $request)
    {
        $validated = $request->validate([
            'employer_company_name' => 'required|string|max:255',
            'employer_website_url' => 'nullable|url|max:255',
            'user_first_name' => 'required|string|max:100',
            'user_last_name' => 'required|string|max:100',
            'user_email' => 'required|email|max:100|unique:users,user_email',
            'employer_company_document' => 'required|file|mimes:pdf|max:20000',
            // 'employer_id_picture_selfie' => 'required|file|mimes:jpg,png,pdf|max:20000',
            // 'employer_company_id_picture' => 'required|file|mimes:jpg,png,pdf|max:20000',
            // 'employer_company_id_picture_selfie' => 'required|file|mimes:jpg,png,pdf|max:20000',
            'user_password' => 'required|string|min:8'
        ]);

        $companyDocumentPath = null;
        if ($request->hasFile('employer_company_document')) {
            $companyDocumentPath = $request->file('employer_company_document')->store('companyDocuments', 'public');
        }

        // $companyIdSelfiePath = null;
        // if ($request->hasFile('employer_company_id_picture_selfie')) {
        //     // This stores the file in storage/app/public/logos
        //     $companyIdSelfiePath = $request->file('employer_company_id_picture_selfie')->store('companyIdSelfies', 'public');
        // }

        // $employerIdPath = null;
        // if ($request->hasFile('employer_id_picture')) {
        //     // This stores the file in storage/app/public/logos
        //     $employerIdPath = $request->file('employer_id_picture')->store('employerIds', 'public');
        // }

        // $employerIdSelfiePath = null;
        // if ($request->hasFile('employer_id_picture_selfie')) {
        //     // This stores the file in storage/app/public/logos
        //     $employerIdSelfiePath = $request->file('employer_id_picture_selfie')->store('employerIdSelfies', 'public');
        // }

        $newEmployerUser = null;

        try {
            DB::transaction(function () use ($validated, $companyDocumentPath, &$newEmployerUser) {
                $user = User::create([
                    'user_email' => $validated['user_email'],
                    'user_password' => Hash::make($validated['user_password']),
                    'user_first_name' => $validated['user_first_name'],
                    'user_last_name' => $validated['user_last_name'],
                    'user_role' => 'employer',
                    'user_active' => false
                ]);
                $newEmployerUser = $user;

                $user->employer()->create([
                    'employer_company_name' => $validated['employer_company_name'],
                    'employer_website_url' => $validated['employer_website_url'] ?? null,
                    'employer_company_document' => $companyDocumentPath ?? null,
                    // 'employer_id_picture' => $employerIdPath,
                    // 'employer_id_picture_selfie' => $employerIdSelfiePath,
                    // 'employer_company_id_picture' => $companyIdPath,
                    // 'employer_company_id_picture_selfie' => $companyIdSelfiePath,
                    'industry_id' => 1, // Default position since we don't have this info yet
                    // 'user_id' => $user->user_id
                    'employer_approved' => false,
                ]);

                // Handle file uploads and save paths to the database
                // ...

            });
        } catch (\Exception $e) {
            // If the DB fails, delete the file we just uploaded
            // if ($employerIdPath) {
            //     Storage::disk('public')->delete($employerIdPath);
            // }
            // if ($employerIdSelfiePath) {
            //     Storage::disk('public')->delete($employerIdSelfiePath);
            // }
            // if ($companyIdPath) {
            //     Storage::disk('public')->delete($companyIdPath);
            // }
            // if ($companyIdSelfiePath) {
            //     Storage::disk('public')->delete($companyIdSelfiePath);
            // }
            return back()->withErrors([
                'user_email' => $e->getMessage(),
                'user_password' => $e->getMessage(),
                'user_first_name' => $e->getMessage(),
                'user_last_name' => $e->getMessage(),
                'employer_company_name' => $e->getMessage(),
                'employer_website_url' => $e->getMessage(),
                'employer_company_document' => $e->getMessage(),
            ]);
        }

        $this->notifyStaffOfPendingEmployer($newEmployerUser, $validated['employer_company_name']);

        return redirect()->route('auth.register')->with('success', 'Account registered successfully!');
    }

    /** New employer registrations are unrestricted to either staff role — see UserController::showUsers()/approveEmployer(). */
    private function notifyStaffOfPendingEmployer(User $employerUser, string $companyName): void
    {
        $recipientIds = User::whereIn('user_role', ['admin', 'super_admin'])->pluck('user_id');
        if ($recipientIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $recipientIds->map(fn ($userId) => [
            'user_id' => $userId,
            'type' => 'employer_registration_pending',
            'title' => 'New employer awaiting verification',
            'body' => "{$companyName} ({$employerUser->user_first_name} {$employerUser->user_last_name}) registered and needs verification.",
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        UserNotification::insert($rows);
    }

    public function goToWaitForApproval()
    {
        return view('general.waitForApproval');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'user_email' => 'required|email|max:255|',
            'user_password' => 'required|string|min:8'
        ]);

        if (Auth::attempt(['user_email' => $validated['user_email'], 'password' => $validated['user_password']], true)) {
            // Authentication passed...
            // if ((Auth::User()->user_role == 'super_admin' || Auth::User()->user_role == 'admin') && Auth::User()->user_active == true) {
            //     $jobPlacementCount = DB::table('job_applications')
            //         ->where('application_status', 'hired')
            //         ->count();
            //     $jobApplicationCount = DB::table('job_applications')
            //         ->count();
            //     $jobPlacementRate = $jobApplicationCount > 0 ? ($jobPlacementCount / $jobApplicationCount) * 100 : 0;
            //     $stats = [
            //         'jobPlacementRate' => round($jobPlacementRate, 2),
            //         'activeJobs' => DB::table('job_postings')
            //             ->where('job_approved', true)
            //             ->where('job_closing_date', '>', now())
            //             ->count(),
            //         'industryPartners' => DB::table('users')
            //             ->where('user_active', true)
            //             ->where('user_role', 'employer')
            //             ->count(),
            //         'alumniUsers' => DB::table('users')
            //             ->where('user_active', true)
            //             ->where('user_role', 'alumni')
            //             ->count()
            //     ];
            //     return view('superAdmin.dashboard', compact('stats'));
            // } else if (Auth::User()->user_role == 'registrar' && Auth::User()->user_active == true) {
            //     return redirect()->intended('/registrar/dashboard');
            // } else if (Auth::User()->user_role == 'employer' && Auth::User()->user_active == true) {
            //     return redirect()->intended('/employer/dashboard');
            // } else if (Auth::User()->user_role == 'alumni' && Auth::User()->user_active == true) {
            //     $testimonials = Testimonial::all()->where('testimonial_post', true);
            //     return view('/alumni/dashboard', compact('testimonials'));
            // } else if (Auth::User()->user_active == false) {
            //     return redirect()->route('auth.login')->withErrors('error', 'Your account is not yet approved or the account is deactivated. Please wait for the administrator to approve your account or contact them for reactivating your account.');
            // } else {
            //     return redirect()->route('auth.login')->withErrors('error', 'Your account role is not recognized. Please contact the administrator.');
            // }
            return redirect()->route('users.dashboardRedirect');
        } else {
            return back()->withErrors(['user_password' => 'invalid password'])->onlyInput('user_email');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function showUsers(User $user)
    {
        $sections = Section::all();
        $programs = Program::all();
        $industries = Industry::orderBy('industry_name')->get();
        $employers = Employer::with(['user', 'industry'])->get();
        $alumni = Alumnus::with('user')->get();
        $admins = Office::with('user')->get();
        return view('superAdmin.userManagement', compact('employers', 'alumni', 'admins', 'sections', 'programs', 'industries'));
    }

    public function approveEmployer($id)
    {
        $user = User::findOrFail($id);

        $user->update(['user_active' => 1]);

        $user->employer->update(['employer_approved' => 1]);
        return back()->with('success', 'Status updated successfully!');
    }

    public function rejectEmployer(Request $request, $id)
    {
        
        $user = Employer::where('user_id', $id)->firstOrFail();
       
        $validated = $request->validate([
            'reject-reason' => 'required|string|max:255'
        ]);

        try {
            // Send email FIRST bago i-delete
            Mail::to($user->user->user_email)->send(new RejectEmployerMail($user->user, $validated['reject-reason']));

            // Delete after successful email
            $user->delete();
            $user->user->delete();
            
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
        
        return redirect()->back()->with('success', 'Employer has been rejected and removed.');
    }

    public function addAlumnus(Request $request)
    {
        $validated = $request->validate([
            'user_first_name' => 'required|string|max:255',
            'user_last_name' => 'required|string|max:255',
            'user_middle_name' => 'required|string|max:255',
            'user_suffix' => 'nullable|string|max:255',
            'alumnus_gender' => 'required|in:male,female,prefer_not_to_say',
            'program_id' => 'required|exists:programs,program_id',
            'alumnus_batch' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'section_id' => 'required|exists:sections,section_id',
            'user_email' => 'required|email|max:255|unique:users,user_email',
        ]);

        try {
            $this->createAlumnusAccount($validated);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to add alumnus: ' . $e->getMessage()]);
        }

        return redirect()->route('superAdmin.userManagement')->with('success', 'Alumnus added successfully!');
    }

    /**
     * Shared by addAlumnus() (manual single add) and importAlumniCsv() (one
     * call per valid row) so both paths create the exact same User +
     * Alumnus + AlumniId + Yearbook bundle the same way. $data keys match
     * addAlumnus()'s validated array: user_first_name, user_middle_name,
     * user_last_name, user_suffix (nullable), user_email, alumnus_gender,
     * program_id, section_id, alumnus_batch.
     *
     * Mail is queued (not sent inline) — importAlumniCsv() can call this in
     * a loop over many rows, and a live SMTP round-trip per row risks the
     * same request-timeout problem bulk hiring already ran into (see
     * JobApplicationController::hireApplication()).
     */
    private function createAlumnusAccount(array $data): User
    {
        $password = Str::random(10);

        return DB::transaction(function () use ($data, $password) {
            $user = User::create([
                'user_email' => $data['user_email'],
                'user_password' => Hash::make($password),
                'user_first_name' => $data['user_first_name'],
                'user_last_name' => $data['user_last_name'],
                'user_middle_name' => $data['user_middle_name'],
                'user_suffix' => $data['user_suffix'] ?? null,
                'user_role' => 'alumni',
                'user_active' => true,
                // They're logging in with a system-generated password
                // emailed in plaintext below — force a change before
                // they can use the account for anything else.
                'must_change_password' => true,
            ]);

            $alumnus = $user->alumnus()->create([
                'program_id' => $data['program_id'],
                'alumnus_batch' => $data['alumnus_batch'],
                'section_id' => $data['section_id'],
                'alumnus_gender' => $data['alumnus_gender'],
            ]);

            $alumnus->alumniId()->create([
                'status' => 'pending',
                'status_updated_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            $alumnus->yearbook()->create([
                'distribution_status' => 'pending',
                'claiming_status' => 'pending',
            ]);

            Mail::to($user->user_email)->queue(new AlumniCreatedMail($user, $password));

            return $user;
        });
    }

    /** Plain-text CSV, matching the columns importAlumniCsv() expects — the "Download Template" link in User Management. */
    public function downloadAlumniCsvTemplate()
    {
        $columns = ['first_name', 'middle_name', 'last_name', 'suffix', 'email', 'gender', 'program', 'section', 'batch'];
        $example = ['Juan', 'Santos', 'Dela Cruz', '', 'juan.delacruz@example.com', 'male', 'BSIT', 'Section A', (string) date('Y')];

        $callback = function () use ($columns, $example) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            fputcsv($handle, $example);
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="alumni_import_template.csv"',
        ]);
    }

    /**
     * Row-by-row CSV import, reusing createAlumnusAccount() for each valid
     * row — a bad row (missing field, unknown program/section, duplicate
     * email, bad gender/batch value) is skipped and reported, not fatal to
     * the whole file, so one typo doesn't block everyone else in the batch.
     */
    public function importAlumniCsv(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);
            return back()->withErrors(['error' => 'The CSV file is empty.']);
        }

        // Normalize so column order/casing/stray whitespace in the uploaded
        // file doesn't have to match the template byte-for-byte.
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        $required = ['first_name', 'middle_name', 'last_name', 'email', 'gender', 'program', 'section', 'batch'];
        $missing = array_diff($required, $header);

        if (!empty($missing)) {
            fclose($handle);
            return back()->withErrors(['error' => 'CSV is missing required column(s): ' . implode(', ', $missing) . '. Download the template for the correct format.']);
        }

        $genderMap = [
            'male' => 'male',
            'female' => 'female',
            'prefer not to say' => 'prefer_not_to_say',
            'prefer_not_to_say' => 'prefer_not_to_say',
        ];

        $rowNum = 1; // row 1 was the header
        $created = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // blank line
            }

            $data = array_combine($header, array_pad($row, count($header), null));

            $firstName = trim((string) ($data['first_name'] ?? ''));
            $middleName = trim((string) ($data['middle_name'] ?? ''));
            $lastName = trim((string) ($data['last_name'] ?? ''));
            $suffix = trim((string) ($data['suffix'] ?? ''));
            $email = trim((string) ($data['email'] ?? ''));
            $genderRaw = strtolower(trim((string) ($data['gender'] ?? '')));
            $programName = trim((string) ($data['program'] ?? ''));
            $sectionName = trim((string) ($data['section'] ?? ''));
            $batch = trim((string) ($data['batch'] ?? ''));

            if ($firstName === '' || $middleName === '' || $lastName === '' || $email === '') {
                $errors[] = "Row {$rowNum}: first name, middle name, last name, and email are required.";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$rowNum}: \"{$email}\" is not a valid email address.";
                continue;
            }

            if (User::where('user_email', $email)->exists()) {
                $errors[] = "Row {$rowNum}: {$email} is already registered.";
                continue;
            }

            $gender = $genderMap[$genderRaw] ?? null;
            if (!$gender) {
                $errors[] = "Row {$rowNum}: gender must be \"male\", \"female\", or \"prefer not to say\".";
                continue;
            }

            $program = Program::where('program_name', $programName)->first();
            if (!$program) {
                $errors[] = "Row {$rowNum}: program \"{$programName}\" was not found.";
                continue;
            }

            $section = Section::where('section_name', $sectionName)->first();
            if (!$section) {
                $errors[] = "Row {$rowNum}: section \"{$sectionName}\" was not found.";
                continue;
            }

            if (!ctype_digit($batch) || (int) $batch < 1900 || (int) $batch > (int) date('Y') + 1) {
                $errors[] = "Row {$rowNum}: \"{$batch}\" is not a valid batch year.";
                continue;
            }

            try {
                $this->createAlumnusAccount([
                    'user_first_name' => $firstName,
                    'user_middle_name' => $middleName,
                    'user_last_name' => $lastName,
                    'user_suffix' => $suffix !== '' ? $suffix : null,
                    'user_email' => $email,
                    'alumnus_gender' => $gender,
                    'program_id' => $program->program_id,
                    'section_id' => $section->section_id,
                    'alumnus_batch' => (int) $batch,
                ]);
                $created++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: failed to create account ({$e->getMessage()}).";
            }
        }

        fclose($handle);

        $summary = "{$created} alumni imported successfully.";

        if (empty($errors)) {
            return redirect()->route('superAdmin.userManagement')->with('success', $summary);
        }

        // Cap flashed row errors — a bad file with hundreds of bad rows
        // shouldn't blow up the session flash payload.
        $shown = array_slice($errors, 0, 20);
        $more = count($errors) - count($shown);
        if ($more > 0) {
            $shown[] = "...and {$more} more error(s) not shown.";
        }

        return redirect()->route('superAdmin.userManagement')
            ->with('success', $summary)
            ->withErrors($shown);
    }

    /** Real data version of the alumni table's "Export CSV" button — mirrors the visible table's columns exactly. */
    public function exportAlumniCsv()
    {
        $alumni = Alumnus::with(['user', 'program', 'section'])->get();

        $callback = function () use ($alumni) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Last Name', 'First Name', 'Middle Name', 'Suffix', 'Gender', 'Program', 'Section', 'Batch', 'Email', 'Status']);

            foreach ($alumni as $i => $alumnus) {
                fputcsv($handle, [
                    $i + 1,
                    $alumnus->user?->user_last_name,
                    $alumnus->user?->user_first_name,
                    $alumnus->user?->user_middle_name,
                    $alumnus->user?->user_suffix,
                    Alumnus::genderLabels()[$alumnus->alumnus_gender] ?? '',
                    $alumnus->program->program_name ?? '',
                    $alumnus->section->section_name ?? '',
                    $alumnus->alumnus_batch,
                    $alumnus->user?->user_email,
                    $alumnus->user?->user_active ? 'Active' : 'Deactivated',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="alumni_list_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Bulk counterpart to AlumnusController::deactivateAlumnus() — same
     * user_active flip + DeactAlumniMail, just over a batch. Silently skips
     * any selected id that's already deactivated (or not an alumnus id at
     * all) rather than erroring the whole request, same "drop out of the
     * batch" philosophy as JobApplicationController's bulk actions.
     */
    public function bulkDeactivateAlumni(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'deactivate-reason' => ['required', 'string', 'max:255'],
        ]);

        $alumni = Alumnus::with('user')
            ->whereIn('user_id', $validated['ids'])
            ->whereHas('user', fn ($q) => $q->where('user_active', true))
            ->get();

        foreach ($alumni as $alumnus) {
            $alumnus->user->update(['user_active' => false]);
            Log::info("Alumnus with ID {$alumnus->user_id}: {$alumnus->user->user_first_name} {$alumnus->user->user_last_name} deactivated (bulk). Reason: {$validated['deactivate-reason']}");
            Mail::to($alumnus->user->user_email)->queue(new DeactAlumniMail($alumnus->user, $validated['deactivate-reason']));
        }

        if ($alumni->isEmpty()) {
            return back()->with('error', 'None of the selected accounts could be deactivated (already inactive?).');
        }

        return back()->with('success', $alumni->count() . ' alumni account(s) deactivated.');
    }

    public function addAdmin(Request $request)
    {
        $validated = $request->validate([
            'user_first_name' => 'required|string|max:255',
            'user_last_name' => 'required|string|max:255',
            'user_middle_name' => 'nullable|string|max:255',
            'user_suffix' => 'nullable|string|max:255',
            'office_address' => 'required|string|max:255',
            'user_email' => 'required|email|max:255|unique:users,user_email',
            'user_password' => 'required|string|min:8|confirmed',
            'user_password_confirmation' => 'required|string|min:8|same:user_password',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $user = User::create([
                    'user_email' => $validated['user_email'],
                    'user_password' => Hash::make($validated['user_password']),
                    'user_first_name' => $validated['user_first_name'],
                    'user_last_name' => $validated['user_last_name'],
                    'user_middle_name' => $validated['user_middle_name'],
                    'user_suffix' => $validated['user_suffix'],
                    'user_role' => 'admin',
                    'user_active' => true
                ]);

                $user->office()->create([
                    'office_address' => $validated['office_address'],
                ]);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to add admin: ' . $e->getMessage()]);
        }
        return redirect()->route('superAdmin.userManagement')->with('success', 'Admin added successfully!');
    }

    public function showProfile()
    {
        $user = Auth::user();
        $industries = Industry::all();
        if ($user->user_role == 'admin') {
            return view('admin.profile', compact('user'));
        } else if ($user->user_role == 'super_admin') {
            return view('superAdmin.profile', compact('user'));
        } else if ($user->user_role == 'registrar') {
            return view('registrar.profile', compact('user'));
        } else if ($user->user_role == 'employer') {
            return view('employer.profile', compact('user'));
        } else if ($user->user_role == 'alumni') {
            // Only needed for the finished resume view now — the builder
            // itself moved to Edit Profile (see editProfile() below).
            $user->load(['alumnus.program', 'alumnus.section', 'alumnus.industry', 'alumnus.skills', 'alumnus.experiences.industry', 'alumnus.certifications']);
            return view('alumni.profile', compact('user'));
        } else {
            Auth::logout();
            return redirect()->route('auth.login')->withErrors('error', 'Your account role is not recognized. Please contact the administrator.');
        }
    }

    public function editProfile()
    {
        $user = Auth::user();
        $industries = Industry::all();
        if ($user->user_role == 'admin') {
            return view('admin.edit-profile', compact('user'));
        } else if ($user->user_role == 'super_admin') {
            return view('superAdmin.edit-profile', compact('user'));
        } else if ($user->user_role == 'registrar') {
            return view('registrar.edit-profile', compact('user'));
        } else if ($user->user_role == 'employer') {
            return view('employer.edit-profile', compact('user'));
        } else if ($user->user_role == 'alumni') {
            $user->load(['alumnus.skills', 'alumnus.experiences.industry', 'alumnus.certifications']);
            $resumeData = $user->alumnus->toResumeFormArray();
            return view('alumni.edit-profile', compact('user', 'industries', 'resumeData'));
        } else {
            Auth::logout();
            return redirect()->route('auth.login')->withErrors('error', 'Your account role is not recognized. Please contact the administrator.');
        }
    }

    public function showChangePassword(Request $request)
    {
        return view('general.changePassword');
    }

    public function redirectToDashboard()
    {
        $user = Auth::user();
        if ($user->user_role == 'admin') {
            // admin.dashboard is an unfinished placeholder view — admin and
            // super_admin already share identical permissions on every real
            // page (job management, notices, alumni ID, user management),
            // and the shared sidebar's own "Dashboard" link already points
            // here too, so this is the one real dashboard for both roles.
            return redirect()->route('superAdmin.dashboard');
        } else if ($user->user_role == 'super_admin') {
            return redirect()->route('superAdmin.dashboard');
        } else if ($user->user_role == 'registrar') {
            return redirect()->route('registrar.dashboard');
        } else if ($user->user_role == 'employer') {
            return redirect()->route('employer.dashboard');
        } else if ($user->user_role == 'alumni') {
            return redirect()->route('alumnus.dashboard');
        } else {
            Auth::logout();
            return redirect()->route('auth.login')->withErrors('error', 'Your account role is not recognized. Please contact the administrator.');
        }
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string|min:8|same:new_password',
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->user_password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'user_password' => Hash::make($validated['new_password']),
                'must_change_password' => false,
            ]);

        return redirect()->route('users.dashboardRedirect')
            ->with('password_changed', true);
    }

    public function showDashboard()
    {
        // Batch/Course/Employment Status filter — see buildOverviewStats()
        // and buildEmploymentReports() for exactly how each report uses it.
        $batch = request()->query('batch');
        $programId = request()->query('program_id');
        $employmentStatus = request()->query('employment_status');
        $hireMonths = $this->resolveHireMonths(request()->query('hire_months'));

        $stats = $this->buildOverviewStats($batch, $programId, $employmentStatus);

        $batches = Alumnus::whereNotNull('alumnus_batch')->distinct()->orderByDesc('alumnus_batch')->pluck('alumnus_batch');
        $programs = Program::orderBy('program_name')->get();
        // hire_months lives outside $dashboardFilters on purpose — it always
        // has a value (default 6), and $dashboardFilters drives both the
        // sticky select values AND the "Clear" link's visibility check,
        // which should only fire for an actually-applied batch/program/
        // status filter, not the hire-range default.
        $dashboardFilters = ['batch' => $batch, 'program_id' => $programId, 'employment_status' => $employmentStatus];

        $reports = $this->buildEmploymentReports($batch, $programId, $employmentStatus, $hireMonths);

        return view('superAdmin.dashboard', array_merge(
            compact('stats', 'batches', 'programs', 'dashboardFilters', 'hireMonths'),
            $reports
        ));
    }

    /**
     * The 4 overview stat cards — split out of showDashboard() so the CSV
     * export (exportDashboardReport()) can compute the exact same numbers
     * shown on screen instead of re-deriving them differently.
     */
    /**
     * "Hires per Month" range selector — validates the raw query param
     * into one of a small fixed set of lengths rather than trusting an
     * arbitrary integer straight into a date-range loop.
     */
    private function resolveHireMonths(?string $raw): int
    {
        $allowed = [3, 6, 12, 24];
        $value = (int) $raw;

        return in_array($value, $allowed, true) ? $value : 6;
    }

    private function buildOverviewStats(?string $batch, ?string $programId, ?string $employmentStatus): array
    {
        $applyAlumniFilters = function ($query, string $alumniTable = 'alumni') use ($batch, $programId, $employmentStatus) {
            if ($batch) {
                $query->where("$alumniTable.alumnus_batch", $batch);
            }
            if ($programId) {
                $query->where("$alumniTable.program_id", $programId);
            }
            if ($employmentStatus !== null && $employmentStatus !== '') {
                $query->where("$alumniTable.alumnus_employment_status", $employmentStatus === 'employed' ? 1 : 0);
            }
            return $query;
        };

        $jobApplicationsQuery = $applyAlumniFilters(
            DB::table('job_applications')->join('alumni', 'alumni.user_id', '=', 'job_applications.alumnus_id')
        );
        $jobPlacementCount = (clone $jobApplicationsQuery)->where('application_status', 'hired')->count();
        $jobApplicationCount = $jobApplicationsQuery->count();
        $jobPlacementRate = $jobApplicationCount > 0
            ? ($jobPlacementCount / $jobApplicationCount) * 100
            : 0;

        return [
            'jobPlacementRate' => round($jobPlacementRate, 2),
            'activeJobs' => DB::table('job_postings')
                ->where('job_approved', true)
                ->where('job_closing_date', '>', now())
                ->count(),
            'industryPartners' => DB::table('users')
                ->where('user_active', true)
                ->where('user_role', 'employer')
                ->count(),
            'alumniUsers' => $applyAlumniFilters(
                DB::table('users')
                    ->join('alumni', 'alumni.user_id', '=', 'users.user_id')
                    ->where('users.user_active', true)
                    ->where('users.user_role', 'alumni')
            )->count(),
        ];
    }

    /**
     * The dashboard's "EXPORT CSV" button — one CSV covering every report
     * on the page (overview stats, each breakdown chart's underlying
     * numbers, the employed alumni list, and the companies report),
     * scoped by the same Batch/Course/Employment Status filter currently
     * applied on screen so the export always matches what the admin is
     * looking at.
     */
    public function exportDashboardReport()
    {
        $batch = request()->query('batch');
        $programId = request()->query('program_id');
        $employmentStatus = request()->query('employment_status');
        $hireMonths = $this->resolveHireMonths(request()->query('hire_months'));

        $stats = $this->buildOverviewStats($batch, $programId, $employmentStatus);
        $r = $this->buildEmploymentReports($batch, $programId, $employmentStatus, $hireMonths);

        $batchLabel = $batch ?: 'All';
        $programLabel = $programId ? (Program::find($programId)->program_name ?? $programId) : 'All';
        $statusLabel = $employmentStatus ? ucfirst($employmentStatus) : 'All';

        $callback = function () use ($stats, $r, $batchLabel, $programLabel, $statusLabel, $hireMonths) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['PLV-AlumNet — Admin Dashboard Report Export']);
            fputcsv($out, ['Generated', now()->format('M d, Y h:i A')]);
            fputcsv($out, ['Filters', "Batch: $batchLabel | Program: $programLabel | Employment Status: $statusLabel"]);
            fputcsv($out, []);

            fputcsv($out, ['OVERVIEW']);
            fputcsv($out, ['Metric', 'Value']);
            fputcsv($out, ['Total Alumni Users', $stats['alumniUsers']]);
            fputcsv($out, ['Employment Rate', $r['employmentRate'] . '%']);
            fputcsv($out, ['Unemployment Rate', $r['unemploymentRate'] . '%']);
            fputcsv($out, ['Industry Partners', $stats['industryPartners']]);
            fputcsv($out, ['Active Job Postings', $stats['activeJobs']]);
            fputcsv($out, ['Job Placement Rate', $stats['jobPlacementRate'] . '%']);
            fputcsv($out, []);

            fputcsv($out, ['EMPLOYMENT RATE BY BATCH/YEAR']);
            fputcsv($out, ['Batch', 'Total', 'Employed', 'Rate']);
            foreach ($r['employmentByBatch'] as $batchYear => $row) {
                fputcsv($out, [$batchYear, $row['total'], $row['employed'], $row['rate'] . '%']);
            }
            fputcsv($out, []);

            fputcsv($out, ['INDUSTRY DISTRIBUTION OF EMPLOYED ALUMNI']);
            fputcsv($out, ['Industry', 'Employed Count']);
            foreach ($r['industryDistribution'] as $industry => $count) {
                fputcsv($out, [$industry, $count]);
            }
            fputcsv($out, []);

            fputcsv($out, ['EMPLOYMENT RATE BY GENDER']);
            fputcsv($out, ['Gender', 'Total', 'Employed', 'Rate']);
            foreach ($r['genderEmployment'] as $row) {
                fputcsv($out, [$row['label'], $row['total'], $row['employed'], $row['rate'] . '%']);
            }
            fputcsv($out, []);

            fputcsv($out, ['JOB-TO-DEGREE ALIGNMENT BY PROGRAM (Overall: ' . $r['alignmentRate'] . '%)']);
            fputcsv($out, ['Program', 'Employed', 'Aligned', 'Rate']);
            foreach ($r['programAlignment'] as $program => $row) {
                fputcsv($out, [$program, $row['total'], $row['aligned'], $row['rate'] . '%']);
            }
            fputcsv($out, []);

            fputcsv($out, ['EMPLOYMENT INTERVAL (graduation to first job)']);
            fputcsv($out, ['Interval', 'Alumni']);
            foreach ($r['employmentInterval'] as $bucket => $count) {
                fputcsv($out, [$bucket, $count]);
            }
            fputcsv($out, []);

            fputcsv($out, ['JOB PLACEMENT & HIRING']);
            fputcsv($out, ['Total Applications', $r['totalApplications']]);
            fputcsv($out, ['Total Hired', $r['totalHired']]);
            fputcsv($out, []);
            fputcsv($out, ['Top Hiring Companies']);
            fputcsv($out, ['Company', 'Hires']);
            foreach ($r['topHiringCompanies'] as $row) {
                fputcsv($out, [$row->job_posting_company, $row->hires]);
            }
            fputcsv($out, []);
            fputcsv($out, ["Hires per Month (last $hireMonths months)"]);
            fputcsv($out, ['Month', 'Hires']);
            foreach ($r['hiresPerMonth'] as $month => $count) {
                fputcsv($out, [$month, $count]);
            }
            fputcsv($out, []);

            fputcsv($out, ['EMPLOYED ALUMNI REPORT']);
            fputcsv($out, ['Name', 'Batch', 'Program', 'Workplace', 'Position', 'Industry', 'Employment Date', 'Aligned']);
            foreach ($r['employedAlumniTable'] as $a) {
                fputcsv($out, [
                    trim(($a->user->user_first_name ?? '') . ' ' . ($a->user->user_last_name ?? '')),
                    $a->alumnus_batch,
                    $a->program->program_name ?? 'N/A',
                    $a->alumnus_workplace_undisclosed ? 'Undisclosed' : ($a->alumnus_workplace ?? 'N/A'),
                    $a->alumnus_job_position ?? 'N/A',
                    $a->industry->industry_name ?? 'N/A',
                    optional($a->alumnus_employment_date)->format('M d, Y') ?? 'N/A',
                    $a->alumnus_employment_status ? ($a->hasCourseAlignedJob() ? 'Aligned' : 'Not Aligned') : '',
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['REGISTERED COMPANIES (' . $r['registeredCompanies']->count() . ')']);
            fputcsv($out, ['Company', 'Industry', 'Contact']);
            foreach ($r['registeredCompanies'] as $employer) {
                fputcsv($out, [$employer->employer_company_name, $employer->industry->industry_name ?? 'N/A', $employer->user->user_email ?? 'N/A']);
            }
            fputcsv($out, []);

            fputcsv($out, ['PENDING / UNREGISTERED COMPANIES (' . $r['pendingCompanies']->count() . ')']);
            fputcsv($out, ['Company', 'Industry', 'Contact']);
            foreach ($r['pendingCompanies'] as $employer) {
                fputcsv($out, [$employer->employer_company_name, $employer->industry->industry_name ?? 'N/A', $employer->user->user_email ?? 'N/A']);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="dashboard_report_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Same report as exportDashboardReport() (CSV), same filters, rendered
     * as a formatted PDF instead — shares the same data-building methods so
     * the two exports can never drift apart on what counts as "the report".
     */
    public function exportDashboardReportPdf()
    {
        $batch = request()->query('batch');
        $programId = request()->query('program_id');
        $employmentStatus = request()->query('employment_status');
        $hireMonths = $this->resolveHireMonths(request()->query('hire_months'));

        $stats = $this->buildOverviewStats($batch, $programId, $employmentStatus);
        $r = $this->buildEmploymentReports($batch, $programId, $employmentStatus, $hireMonths);

        $batchLabel = $batch ?: 'All';
        $programLabel = $programId ? (Program::find($programId)->program_name ?? $programId) : 'All';
        $statusLabel = $employmentStatus ? ucfirst($employmentStatus) : 'All';

        $pdf = Pdf::loadView('superAdmin.dashboard-report-pdf', compact('stats', 'r', 'batchLabel', 'programLabel', 'statusLabel', 'hireMonths'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('dashboard_report_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Everything under the dashboard's "Reports & Analytics" section.
     * Split out of showDashboard() purely to keep that method readable —
     * this is still page-specific, not a reusable service.
     *
     * Design note: $batch/$programId scope every report here (a cohort
     * lens on the whole section), but $employmentStatus is deliberately
     * NOT applied to the rate/breakdown reports (employment-by-batch,
     * industry distribution, gender breakdown, alignment) — filtering
     * "Employed" alumni down to a chart of employment status would make
     * the chart trivially 100/0%. It's applied only to the Employed
     * Alumni table below, where picking "Unemployed" meaningfully swaps
     * which list of names is shown.
     */
    private function buildEmploymentReports(?string $batch, ?string $programId, ?string $employmentStatus, int $hireMonths = 6): array
    {
        $alumniQuery = Alumnus::with(['user', 'program', 'industry']);
        if ($batch) {
            $alumniQuery->where('alumnus_batch', $batch);
        }
        if ($programId) {
            $alumniQuery->where('program_id', $programId);
        }
        $allAlumni = $alumniQuery->get();
        $employedAlumni = $allAlumni->where('alumnus_employment_status', true);

        $totalAlumni = $allAlumni->count();
        $employedCount = $employedAlumni->count();
        $employmentRate = $totalAlumni > 0 ? round($employedCount / $totalAlumni * 100, 2) : 0;
        $unemploymentRate = $totalAlumni > 0 ? round(100 - $employmentRate, 2) : 0;

        // 1. Employment rate by batch/year
        $employmentByBatch = $allAlumni->groupBy('alumnus_batch')
            ->filter(fn ($group, $key) => $key !== null && $key !== '')
            ->sortKeys()
            ->map(function ($group) {
                $total = $group->count();
                $employed = $group->where('alumnus_employment_status', true)->count();
                return ['total' => $total, 'employed' => $employed, 'rate' => $total > 0 ? round($employed / $total * 100, 2) : 0];
            });

        // Industry/sector distribution of employed alumni
        $industryDistribution = $employedAlumni->groupBy(fn ($a) => $a->industry->industry_name ?? 'Unspecified')
            ->map->count()
            ->sortDesc();

        // Employment rate by gender
        $genderLabels = Alumnus::genderLabels();
        $genderEmployment = $allAlumni->groupBy(fn ($a) => $a->alumnus_gender ?: '')
            ->map(function ($group, $key) use ($genderLabels) {
                $total = $group->count();
                $employed = $group->where('alumnus_employment_status', true)->count();
                return [
                    'label' => $genderLabels[$key] ?? 'Unspecified',
                    'total' => $total,
                    'employed' => $employed,
                    'rate' => $total > 0 ? round($employed / $total * 100, 2) : 0,
                ];
            })->values();

        // Job-to-degree alignment, overall and per program (employed alumni only)
        $overallAligned = $employedAlumni->filter->hasCourseAlignedJob()->count();
        $alignmentRate = $employedCount > 0 ? round($overallAligned / $employedCount * 100, 2) : 0;
        $programAlignment = $employedAlumni->groupBy(fn ($a) => $a->program->program_name ?? 'Unspecified')
            ->map(function ($group) {
                $total = $group->count();
                $aligned = $group->filter->hasCourseAlignedJob()->count();
                return ['total' => $total, 'aligned' => $aligned, 'rate' => $total > 0 ? round($aligned / $total * 100, 2) : 0];
            })
            ->sortByDesc('total');

        // Employment interval — months from batch graduation (approximated as
        // June of the batch year, PLV's school-year end) to first job date.
        $employmentInterval = ['Within 6 months' => 0, '6–12 months' => 0, '1–2 years' => 0, 'Over 2 years' => 0];
        foreach ($allAlumni as $a) {
            if (!$a->alumnus_first_job_date || !$a->alumnus_batch) {
                continue;
            }
            $graduation = Carbon::create((int) $a->alumnus_batch, 6, 1);
            $months = max(0, $graduation->diffInMonths($a->alumnus_first_job_date, false));
            $bucket = match (true) {
                $months <= 6 => 'Within 6 months',
                $months <= 12 => '6–12 months',
                $months <= 24 => '1–2 years',
                default => 'Over 2 years',
            };
            $employmentInterval[$bucket]++;
        }

        // Employed Alumni report — the one table where $employmentStatus
        // actually changes which list is shown (see class doc note above).
        $employedAlumniTable = $employmentStatus === 'unemployed'
            ? $allAlumni->where('alumnus_employment_status', false)->sortByDesc('updated_at')->values()
            : $allAlumni->where('alumnus_employment_status', true)->sortByDesc('alumnus_employment_date')->values();

        // Job placement & hiring — same alumni cohort filters as jobPlacementRate.
        $hiringBase = fn () => DB::table('job_applications')
            ->join('alumni', 'alumni.user_id', '=', 'job_applications.alumnus_id')
            ->join('job_postings', 'job_postings.job_posting_id', '=', 'job_applications.job_id')
            ->when($batch, fn ($q) => $q->where('alumni.alumnus_batch', $batch))
            ->when($programId, fn ($q) => $q->where('alumni.program_id', $programId));

        $totalApplications = $hiringBase()->count();
        $totalHired = $hiringBase()->where('job_applications.application_status', 'hired')->count();

        // hired_at is set the moment an application is actually marked
        // hired (JobApplicationController::hireApplication()) — using it
        // instead of updated_at means a later, unrelated edit to the same
        // row (e.g. a score correction) no longer shifts it to a different
        // month here.
        $hiresPerMonth = collect(range($hireMonths - 1, 0))->mapWithKeys(function ($i) use ($hiringBase) {
            $month = now()->subMonths($i);
            $count = $hiringBase()
                ->where('job_applications.application_status', 'hired')
                ->whereYear('job_applications.hired_at', $month->year)
                ->whereMonth('job_applications.hired_at', $month->month)
                ->count();
            return [$month->format('M Y') => $count];
        });

        $topHiringCompanies = $hiringBase()
            ->where('job_applications.application_status', 'hired')
            ->select('job_postings.job_posting_company', DB::raw('count(*) as hires'))
            ->groupBy('job_postings.job_posting_company')
            ->orderByDesc('hires')
            ->limit(5)
            ->get();

        // Registered vs pending/unregistered companies
        $registeredCompanies = Employer::with(['user', 'industry'])->where('employer_approved', true)->latest('created_at')->get();
        $pendingCompanies = Employer::with(['user', 'industry'])->where('employer_approved', false)->latest('created_at')->get();

        return compact(
            'totalAlumni', 'employedCount', 'employmentRate', 'unemploymentRate', 'employmentByBatch', 'industryDistribution',
            'genderEmployment', 'programAlignment', 'alignmentRate', 'employmentInterval',
            'employedAlumniTable', 'totalApplications', 'totalHired', 'hiresPerMonth', 'topHiringCompanies',
            'registeredCompanies', 'pendingCompanies'
        );
    }

    public function showSuperAdminProfile()
    {
        $user = Auth::user();
        return view('superAdmin.profile', compact('user'));
    }
}
