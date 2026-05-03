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
    public function updateOfficeProfile(Request $request, $id)
    {
        $validated = $request->validate([
            'user_first_name'    => 'required|string|max:255',
            'user_middle_name'   => 'nullable|string|max:255',
            'user_last_name'     => 'required|string|max:255',
            'user_suffix'        => 'nullable|string|max:50',
            'user_email'         => 'required|email|max:255|unique:users,user_email,' . $id . ',user_id',
            'user_number'        => 'nullable|string|max:20',
            'office_address'     => 'nullable|string|max:255',
            'office_birth_date'  => 'nullable|date',
            'user_profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = User::findOrFail($id);

        // Update user fields
        $user->update([
            'user_first_name'  => $validated['user_first_name'],
            'user_middle_name' => $validated['user_middle_name'] ?? null,
            'user_last_name'   => $validated['user_last_name'],
            'user_suffix'      => $validated['user_suffix'] ?? null,
            'user_email'       => $validated['user_email'],
            'user_number'      => $validated['user_number'] ?? null,
        ]);

        // Update office fields
        $user->office()->updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'office_address'    => $validated['office_address'] ?? null,
                'office_birth_date' => $validated['office_birth_date'] ?? null,
            ]
        );

        // Handle profile picture upload
        if ($request->hasFile('user_profile_picture')) {
            // Delete old picture if exists
            if ($user->user_profile_picture) {
                Storage::disk('public')->delete($user->user_profile_picture);
            }
            $path = $request->file('user_profile_picture')->store('profile_pictures', 'public');
            $user->update(['user_profile_picture' => $path]);
        }

        return redirect()->route('user.profile')
            ->with('success', 'Profile updated successfully!');
    }
}
