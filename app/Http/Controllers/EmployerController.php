<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployerController extends Controller
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
    public function show(Employer $employer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employer $employer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employer $employer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employer $employer)
    {
        //
    }

    public function updateEmployerProfile(Request $request, $employer)
    {
        $user = User::where('user_id', $employer)->firstOrFail();
        // Validate the incoming request data
        $validated = $request->validate([
            'user_profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'user_first_name' => 'nullable|string',
            'user_last_name' => 'nullable|string',
            'user_middle_name' => 'nullable|string',
            'user_suffix' => 'nullable|string',
            'employer_position' => 'nullable|string',
            'user_email' => 'nullable|email|unique:users,user_email,' . $user->user_id . ',user_id',
            'user_number' => 'nullable|string',
            'employer_company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'employer_company_name' => 'nullable|string',
            'employer_year_established' => 'nullable|date_format:Y',
            'employer_company_size' => 'nullable|string',
            'employer_industry' => 'nullable|exists:industries,industry_id',
            'employer_website_url' => 'nullable|url',
        ]);

        // Update the user's profile information
        $oldProfilePicture = $user->user_profile_picture ?? null;
        $profilePicture = null;
        if ($request->hasFile('user_profile_picture')) {
            if ($oldProfilePicture && Storage::disk('public')->exists($oldProfilePicture)) {
                Storage::disk('public')->delete($oldProfilePicture);
            }
            $profilePicture = $request->file('user_profile_picture')->store('profilePictures', 'public');
        }

        $oldCompanyLogo = $user->employer->employer_company_logo ?? null;
        $companyLogo = null;
        if ($request->hasFile('employer_company_logo')) {
            if ($oldCompanyLogo && Storage::disk('public')->exists($oldCompanyLogo)) {
                Storage::disk('public')->delete($oldCompanyLogo);
            }
            $companyLogo = $request->file('employer_company_logo')->store('companyLogos', 'public');
        }

        try {
            DB::transaction(function () use ($validated, $employer, $profilePicture, $companyLogo) {
                $employer = Employer::where('user_id', $employer)->firstOrFail();

                $employer->update([
                    'employer_position' => $validated['employer_position'] ?? $employer->employer_position,
                    'employer_company_name' => $validated['employer_company_name'] ?? $employer->employer_company_name,
                    'employer_year_established' => $validated['employer_year_established'] ?? $employer->employer_year_established,
                    'employer_company_size' => $validated['employer_company_size'] ?? $employer->employer_company_size,
                    'industry_id' => $validated['employer_industry'] ?? $employer->industry_id,
                    'employer_website_url' => $validated['employer_website_url'] ?? $employer->employer_website_url,
                ]);


                $employer->user->update([
                    'user_first_name' => $validated['user_first_name'] ?? $employer->user->user_first_name,
                    'user_last_name' => $validated['user_last_name'] ?? $employer->user->user_last_name,
                    'user_middle_name' => $validated['user_middle_name'] ?? $employer->user->user_middle_name,
                    'user_suffix' => $validated['user_suffix'] ?? $employer->user->user_suffix,
                    'user_email' => $validated['user_email'] ?? $employer->user->user_email,
                    'user_number' => $validated['user_number'] ?? $employer->user->user_number,
                ]);

                if ($profilePicture != null) {
                    $employer->user->update([
                        'user_profile_picture' => $profilePicture,
                    ]);
                }

                if ($companyLogo != null) {
                    $employer->update([
                        'employer_company_logo' => $companyLogo,
                    ]);
                }
            });
        } catch (\Exception $e) {
            dd([
                'Message' => $e->getMessage(),
                'File' => $e->getFile(),
                'Line' => $e->getLine()
            ]);
            if ($profilePicture) {
                Storage::disk('public')->delete($profilePicture);
            }
            if ($companyLogo) {
                Storage::disk('public')->delete($companyLogo);
            }

            return redirect()->route('user.profile')->with('error', 'An error occurred while uploading the resume: ' . $e->getMessage());
        }

        return redirect()->route('user.profile')->with('success', 'Profile updated successfully.');
    }

    public function deactivateEmployer(Request $request, $id)
    {
        $Employer = Employer::where('user_id', $id)->firstOrFail();

        $validated = $request->validate([
            'deactivate-reason' => 'required|string|max:255',
        ]);

        Log::info("Employer with ID {$Employer->user->user_id}: {$Employer->user->user_first_name} {$Employer->user->user_last_name} deactivated. Reason: {$validated['deactivate-reason']}");

        try{
            $Employer->user->update([
                'user_active' => false,
            ]);
        }
        catch(\Exception $e){
            return back()->with('error', 'An error occurred while deactivating the Employer. Please try again later.');
        }

        return back()->with('success', 'Employer deactivated successfully!');
    }
}
