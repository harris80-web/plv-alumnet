<?php

namespace App\Http\Controllers;

use App\Models\Alumnus;
use Illuminate\Foundation\Auth\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class AlumnusController extends Controller
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
    public function show(Alumnus $alumnus)
    {
        //
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
            'alumnus_skills' => 'nullable|string',
            'alumnus_resume' => 'nullable|mimes:pdf,doc,docx',
            'user_email' => 'required|email|unique:users,user_email,' . $user->user_id . ',user_id',
            'user_number' => 'nullable|string|max:20',
        ]);

        $oldResume = $user->alumnus->alumnus_resume ?? null;
        $resume = null;
        if ($request->hasFile('alumnus_resume')) {
            if ($oldResume && Storage::disk('public')->exists($oldResume)) {
                Storage::disk('public')->delete($oldResume);
            }
            $resume = $request->file('alumnus_resume')->store('resumes', 'public');
        }
        $oldProfilePicture = $user->user_profile_picture ?? null;
        $profilePicture = null;
        if ($request->hasFile('user_profile_picture')) {
            if( $oldProfilePicture && Storage::disk('public')->exists($oldProfilePicture)){
                Storage::disk('public')->delete($oldProfilePicture);
            }
            $profilePicture = $request->file('user_profile_picture')->store('profilePictures', 'public');
        }

        try {
            DB::transaction(function () use ($validated, $resume, $alumnus, $profilePicture) {
                $alumni = Alumnus::where('user_id', $alumnus)->firstOrFail();

                $alumni->update([
                    'alumnus_employment_status' => $validated['alumnus_employment_status'] ?? $alumni->alumnus_employment_status,
                    'alumnus_skills' => $validated['alumnus_skills'] ?? $alumni->alumnus_skills,
                ]);

                if ($resume != null) {
                    $alumni->update([
                        'alumnus_resume' => $resume,
                    ]);
                }


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
            dd([
                'Message' => $e->getMessage(),
                'File' => $e->getFile(),
                'Line' => $e->getLine()
            ]);
            if ($resume) {
                Storage::disk('public')->delete($resume);
            }
            if ($profilePicture) {
                Storage::disk('public')->delete($profilePicture);
            }

            return redirect()->route('user.profile')->with('error', 'An error occurred while uploading the resume: ' . $e->getMessage());
        }

        return redirect()->route('user.profile')->with('success', 'Alumni profile updated successfully.');
    }

    public function deactivateAlumnus(Request $request, $id)
    {
        $alumnus = Alumnus::where('user_id', $id)->firstOrFail();

        $validated = $request->validate([
            'deactivate-reason' => 'required|string|max:255',
        ]);

        Log::info("Alumnus with ID {$alumnus->user->user_id}: {$alumnus->user->user_first_name} {$alumnus->user->user_last_name} deactivated. Reason: {$validated['deactivate-reason']}");

        try{
            $alumnus->user->update([
                'user_active' => false,
            ]);
        }
        catch(\Exception $e){
            return back()->with('error', 'An error occurred while deactivating the alumnus. Please try again later.');
        }

        return back()->with('success', 'Alumnus deactivated successfully!');
    }
}
