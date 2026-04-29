<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OfficeController extends Controller
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
    public function show(Office $office)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Office $office)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Office $office)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        //
    }

    public function updateOfficeProfile(Request $request, $office)
    {
        $user = User::where('user_id', $office)->firstOrFail();
        $validated = $request->validate([
            'user_profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'user_first_name' => 'required|string',
            'user_last_name' => 'required|string',
            'user_middle_name' => 'nullable|string',
            'user_suffix' => 'nullable|string',
            'office_birth_date' => 'required|date',
            'user_email' => 'required|email|unique:users,user_email,' . $user->user_id . ',user_id',
            'user_number' => 'required|string',
            'office_address' => 'required|string',
        ]);

        $oldProfilePicture = $user->user_profile_picture ?? null;
        $profilePicture = null;
        if ($request->hasFile('user_profile_picture')) {
            if( $oldProfilePicture && Storage::disk('public')->exists($oldProfilePicture)){
                Storage::disk('public')->delete($oldProfilePicture);
            }
            $profilePicture = $request->file('user_profile_picture')->store('profilePictures', 'public');
        }

        try {
            DB::transaction(function () use ($validated, $office, $profilePicture) {
                $office = Office::where('user_id', $office)->firstOrFail();

                $office->update([
                    'office_birth_date' => $validated['office_birth_date'] ?? $office->office_birth_date,
                    'office_address' => $validated['office_address'] ?? $office->office_address,
                ]);

                
                $office->user->update([
                    'user_first_name' => $validated['user_first_name'] ?? $office->user->user_first_name,
                    'user_last_name' => $validated['user_last_name'] ?? $office->user->user_last_name,
                    'user_middle_name' => $validated['user_middle_name'] ?? $office->user->user_middle_name,
                    'user_suffix' => $validated['user_suffix'] ?? $office->user->user_suffix,
                    'user_email' => $validated['user_email'] ?? $office->user->user_email,
                    'user_number' => $validated['user_number'] ?? $office->user->user_number,
                ]);

                if ($profilePicture != null) {
                    $office->user->update([
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
            if ($profilePicture) {
                Storage::disk('public')->delete($profilePicture);
            }

            return redirect()->route('user.profile')->with('error', 'An error occurred while uploading the resume: ' . $e->getMessage());
        }
        return redirect()->route('user.profile')->with('success', 'Profile updated successfully.');
    }
}
