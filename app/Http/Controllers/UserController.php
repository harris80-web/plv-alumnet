<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employer;
use App\Models\Alumnus;
use App\Models\Office;
use App\Models\Program;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Mail\AlumniCreatedMail;
use App\Models\Industry;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;


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
            'user_first_name' => 'required|string|max:255',
            'user_last_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255',
            // 'employer_id_picture' => 'required|file|mimes:jpg,png,pdf|max:20000',
            // 'employer_id_picture_selfie' => 'required|file|mimes:jpg,png,pdf|max:20000',
            // 'employer_company_id_picture' => 'required|file|mimes:jpg,png,pdf|max:20000',
            // 'employer_company_id_picture_selfie' => 'required|file|mimes:jpg,png,pdf|max:20000',
            'user_password' => 'required|string|min:8'
        ]);

        // $companyIdPath = null;
        // if ($request->hasFile('employer_company_id_picture')) {
        //     // This stores the file in storage/app/public/logos
        //     $companyIdPath = $request->file('employer_company_id_picture')->store('companyIds', 'public');
        // }

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

        try {
            DB::transaction(function () use ($validated) {
                $user = User::create([
                    'user_email' => $validated['user_email'],
                    'user_password' => Hash::make($validated['user_password']),
                    'user_first_name' => $validated['user_first_name'],
                    'user_last_name' => $validated['user_last_name'],
                    'user_role' => 'employer',
                    'user_active' => false
                ]);

                $user->employer()->create([
                    'employer_company_name' => $validated['employer_company_name'],
                    'employer_website_url' => $validated['employer_website_url'] ?? null,
                    // 'employer_id_picture' => $employerIdPath,
                    // 'employer_id_picture_selfie' => $employerIdSelfiePath,
                    // 'employer_company_id_picture' => $companyIdPath,
                    // 'employer_company_id_picture_selfie' => $companyIdSelfiePath,
                    'industry_id' => 1, // Default position since we don't have this info yet
                    // 'user_id' => $user->user_id
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
            return back()->withErrors($e->getMessage());
        }


        return redirect()->route('general.waitForApproval');
    }

    public function goToWaitForApproval()
    {
        return view('general.waitForApproval');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'user_email' => 'required|email|max:255',
            'user_password' => 'required|string|min:8'
        ]);

        if (Auth::attempt(['user_email' => $validated['user_email'], 'password' => $validated['user_password']], true)) {
            // Authentication passed...
            if (Auth::User()->user_role == 'admin' && Auth::User()->user_active == true) {
                return redirect()->intended('/admin/dashboard');
            } else if (Auth::User()->user_role == 'super_admin' && Auth::User()->user_active == true) {
                $jobPlacementCount = DB::table('job_applications')
                    ->where('application_status', 'hired')
                    ->count();
                $jobApplicationCount = DB::table('job_applications')
                    ->count();
                $jobPlacementRate = $jobApplicationCount > 0 ? ($jobPlacementCount / $jobApplicationCount) * 100 : 0;
                $stats = [
                    'jobPlacementRate' => round($jobPlacementRate, 2),
                    'activeJobs' => DB::table('job_postings')
                        ->where('job_approved', true)
                        ->where('job_closing_date', '>', now())
                        ->count(),
                    'industryPartners' => DB::table('users')
                        ->where('user_active', true)
                        ->where('user_role', 'employer')
                        ->count(),
                    'alumniUsers' => DB::table('users')
                        ->where('user_active', true)
                        ->where('user_role', 'alumni')
                        ->count()
                ];
                return view('superAdmin.dashboard', compact('stats'));
            } else if (Auth::User()->user_role == 'registrar' && Auth::User()->user_active == true) {
                return redirect()->intended('/registrar/dashboard');
            } else if (Auth::User()->user_role == 'employer' && Auth::User()->user_active == true) {
                return redirect()->intended('/employer/dashboard');
            } else if (Auth::User()->user_role == 'alumni' && Auth::User()->user_active == true) {
                $testimonials = Testimonial::all()->where('testimonial_post', true);
                return view('/alumni/dashboard', compact('testimonials'));
            } else if (Auth::User()->user_active == false) {
                return redirect()->route('auth.login')->withErrors('error', 'Your account is not yet approved or the account is deactivated. Please wait for the administrator to approve your account or contact them for reactivating your account.');
            } else {
                return redirect()->route('auth.login')->withErrors('error', 'Your account role is not recognized. Please contact the administrator.');
            }
        } else {
            return back()->withErrors('error', 'Please enter correct credentials');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    public function showUsers(User $user)
    {
        $sections = Section::all();
        $programs = Program::all();
        $employers = Employer::with('user')->get();
        $alumni = Alumnus::with('user')->get();
        $admins = Office::with('user')->get();
        return view('superAdmin.userManagement', compact('employers', 'alumni', 'admins', 'sections', 'programs'));
    }

    public function approveEmployer($id)
    {
        $user = User::findOrFail($id);

        $user->user_active = 1;
        $user->save();

        return back()->with('success', 'Status updated successfully!');
    }

    public function addAlumnus(Request $request)
    {
        $validated = $request->validate([
            'user_first_name' => 'required|string|max:255',
            'user_last_name' => 'required|string|max:255',
            'user_middle_name' => 'required|string|max:255',
            'user_suffix' => 'nullable|string|max:255',
            'program_id' => 'required|exists:programs,program_id',
            'alumnus_batch' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'section_id' => 'required|exists:sections,section_id',
            'user_email' => 'required|email|max:255|unique:users,user_email',
        ]);
        $password = Str::random(10);

        try {
            DB::transaction(function () use ($validated, $password) {
                $user = User::create([
                    'user_email' => $validated['user_email'],
                    'user_password' => Hash::make($password),
                    'user_first_name' => $validated['user_first_name'],
                    'user_last_name' => $validated['user_last_name'],
                    'user_middle_name' => $validated['user_middle_name'],
                    'user_suffix' => $validated['user_suffix'],
                    'user_role' => 'alumni',
                    'user_active' => true
                ]);

                $user->alumnus()->create([
                    'program_id' => $validated['program_id'],
                    'alumnus_batch' => $validated['alumnus_batch'],
                    'section_id' => $validated['section_id'],
                ]);

                Mail::to($user->user_email)->send(new AlumniCreatedMail($user, $password));
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Email failed: ' . $e->getMessage()]);
            dd($e->getMessage());
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('superAdmin.dashboard')->with('success', 'Alumnus added successfully!');
    }

    public function addAdmin()
    {
        $validated = request()->validate([
            'user_first_name' => 'required|string|max:255',
            'user_last_name' => 'required|string|max:255',
            'user_middle_name' => 'required|string|max:255',
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
        if ($user->user_role == 'admin') {
            return view('admin.profile', compact('user'));
        } else if ($user->user_role == 'super_admin') {
            return view('superAdmin.profile', compact('user'));
        } else if ($user->user_role == 'registrar') {
            return view('registrar.profile', compact('user'));
        } else if ($user->user_role == 'employer') {
            return view('employer.profile', compact('user'));
        } else if ($user->user_role == 'alumni') {
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
            return view('employer.edit-profile', compact('user', 'industries'));
        } else if ($user->user_role == 'alumni') {
            return view('alumni.edit-profile', compact('user'));
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
            return redirect()->route('admin.dashboard');
        } else if ($user->user_role == 'super_admin') {
            return redirect()->route('superAdmin.dashboard');
        } else if ($user->user_role == 'registrar') {
            return redirect()->route('registrar.dashboard');
        } else if ($user->user_role == 'employer') {
            return redirect()->route('employer.dashboard');
        } else if ($user->user_role == 'alumni') {
            return redirect()->route('alumni.dashboard');
        } else {
            Auth::logout();
            return redirect()->route('auth.login')->withErrors('error', 'Your account role is not recognized. Please contact the administrator.');
        }
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password'             => 'required|string|min:8',
            'new_password'                 => 'required|string|min:8|confirmed',
            'new_password_confirmation'    => 'required|string|min:8|same:new_password',
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->user_password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update(['user_password' => Hash::make($validated['new_password'])]);

        return back()->with('success', 'Password changed successfully!');
    }

    public function showDashboard()
    {
        $jobPlacementCount = DB::table('job_applications')
            ->where('application_status', 'hired')
            ->count();
        $jobApplicationCount = DB::table('job_applications')->count();
        $jobPlacementRate = $jobApplicationCount > 0
            ? ($jobPlacementCount / $jobApplicationCount) * 100
            : 0;

        $stats = [
            'jobPlacementRate' => round($jobPlacementRate, 2),
            'activeJobs' => DB::table('job_postings')
                ->where('job_approved', true)
                ->where('job_closing_date', '>', now())
                ->count(),
            'industryPartners' => DB::table('users')
                ->where('user_active', true)
                ->where('user_role', 'employer')
                ->count(),
            'alumniUsers' => DB::table('users')
                ->where('user_active', true)
                ->where('user_role', 'alumni')
                ->count()
        ];

        return view('superAdmin.dashboard', compact('stats'));
    }
}
