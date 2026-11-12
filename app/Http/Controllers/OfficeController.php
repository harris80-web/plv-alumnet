<?php

namespace App\Http\Controllers;

use App\Services\StoredFileCleaner;
use App\Mail\ActivateAdminMail;
use App\Mail\DeactAdminMail;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OfficeController extends Controller
{
    /**
     * The empty index/create/store/show/edit/update/destroy scaffold stubs
     * that used to sit here backed Route::resource('offices', ...), which was
     * registered with no middleware at all. Both the route and the stubs are
     * gone; the real office actions are the named methods below, each routed
     * individually with its own auth/super_admin gate.
     */
    public function updateOfficeProfile(Request $request, $id)
    {
        // Self-service only — same missing-ownership-check bug already found
        // and fixed once for AlumnusController::updateAlumniProfile(). Only
        // superAdmin/edit-profile.blade.php and superAdmin/profile.blade.php
        // post here, always with the logged-in admin/super_admin's own id.
        abort_unless(Auth::check() && Auth::id() == $id, 403);

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
        ], [], [
            // Without these, a missing field reads as "The user first name
            // field is required." — the raw attribute name instead of the
            // label actually shown next to that input.
            'user_first_name' => 'First Name',
            'user_middle_name' => 'Middle Name',
            'user_last_name' => 'Last Name',
            'user_suffix' => 'Suffix',
            'user_email' => 'Email',
            'user_number' => 'Phone Number',
            'office_address' => 'Address',
            'office_birth_date' => 'Birth Date',
            'user_profile_picture' => 'Profile Picture',
        ]);

        $user = User::findOrFail($id);

        // Field labels for the success toast ("Profile updated: Phone Number, Address.")
        $labels = [
            'user_first_name' => 'First Name', 'user_middle_name' => 'Middle Name', 'user_last_name' => 'Last Name',
            'user_suffix' => 'Suffix', 'user_email' => 'Email Address', 'user_number' => 'Phone Number',
            'office_address' => 'Address', 'office_birth_date' => 'Date of Birth',
        ];
        $changed = [];

        // Update user fields
        $user->fill([
            'user_first_name'  => $validated['user_first_name'],
            'user_middle_name' => $validated['user_middle_name'] ?? null,
            'user_last_name'   => $validated['user_last_name'],
            'user_suffix'      => $validated['user_suffix'] ?? null,
            'user_email'       => $validated['user_email'],
            'user_number'      => $validated['user_number'] ?? null,
        ]);
        $changed = array_merge($changed, array_keys($user->getDirty()));
        $user->save();

        // Update office fields
        $office = $user->office()->firstOrNew(['user_id' => $user->user_id]);
        $office->fill([
            'office_address'    => $validated['office_address'] ?? null,
            'office_birth_date' => $validated['office_birth_date'] ?? null,
        ]);
        $changed = array_merge($changed, array_keys(array_intersect_key($office->getDirty(), $labels)));
        $office->save();

        // Handle profile picture upload
        if ($request->hasFile('user_profile_picture')) {
            $oldPicture = $user->user_profile_picture;
            $path = $request->file('user_profile_picture')->store('profile_pictures', 'public');
            $user->update(['user_profile_picture' => $path]);
            // After saving the new one, and only if nothing else shares the old file.
            app(StoredFileCleaner::class)->deleteIfUnused($oldPicture);
            $labels['user_profile_picture'] = 'Profile Picture';
            $changed[] = 'user_profile_picture';
        }

        $changedLabels = array_values(array_unique(array_map(fn ($k) => $labels[$k] ?? $k, array_intersect($changed, array_keys($labels)))));
        $message = $changedLabels
            ? 'Profile updated: ' . implode(', ', $changedLabels) . '.'
            : 'No changes to save — your profile is already up to date.';

        // Not user.profile — for an `admin` that resolves to
        // admin.profile, an unfinished, unstyled placeholder view (no
        // header/sidebar at all). superAdmin.profile is the one real
        // profile page both admin and super_admin actually use (see
        // UserController::showSuperAdminProfile(), which authorizeStaff()
        // already allows for both roles) and the page this form lives on.
        // The message shows as the success toast at the top (partials.success).
        return redirect()->route('superAdmin.profile')
            ->with('success', $message);
    }

    /**
     * Single-admin permission edit — the "View Profile" modal's Save button
     * in userManagement.blade.php. Route already restricted to super_admin
     * (see routes/web.php), so no role check needed here.
     */
    public function updatePermissions(Request $request, $id)
    {
        $admin = User::where('user_id', $id)->where('user_role', 'admin')->firstOrFail();

        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(Office::PERMISSIONS)),
        ]);

        $admin->office()->update(['permissions' => $validated['permissions'] ?? []]);

        return back()->with('success', 'Permissions updated for ' . $admin->user_first_name . ' ' . $admin->user_last_name . '.');
    }

    /**
     * Bulk permission edit — applies the exact same permission set to every
     * selected admin, overwriting whatever each one had before (not merged
     * in). Mirrors the ids[] + validated-array pattern already used by
     * UserController::bulkDeactivateAlumni() and the testimonials/FAQ bulk
     * endpoints.
     */
    public function bulkUpdatePermissions(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'permissions' => 'array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(Office::PERMISSIONS)),
        ]);

        $adminIds = User::whereIn('user_id', $validated['ids'])->where('user_role', 'admin')->pluck('user_id');

        Office::whereIn('user_id', $adminIds)->update(['permissions' => json_encode($validated['permissions'] ?? [])]);

        return back()->with('success', 'Permissions updated for ' . $adminIds->count() . ' admin' . ($adminIds->count() === 1 ? '' : 's') . '.');
    }

    /**
     * Deactivating is the admin counterpart to
     * AlumnusController::deactivateAlumnus() — it only flips user_active, so
     * the account stays listed (with a DEACTIVATED badge) and keeps counting
     * toward Total Admin, exactly like the alumni and employer tabs.
     *
     * This replaced an earlier deleteAdmin() that soft-deleted the office and
     * user rows: that dropped the admin out of every metric card at once,
     * including the "Deactivated" one that is meant to be counting them, so
     * the count could never be anything but zero.
     */
    public function deactivateAdmin(Request $request, $id)
    {
        $admin = User::where('user_id', $id)->firstOrFail();

        $validated = $request->validate([
            'deactivate-reason' => 'required|string|max:255',
        ], [], ['deactivate-reason' => 'Reason']);

        if ($admin->user_role !== 'admin') {
            return back()->with('error', 'The specified user is not an admin.');
        }

        // A super admin deactivating themselves would lock the only account
        // that can undo it; nothing in the UI offers this, but the route is
        // reachable directly.
        if ($admin->user_id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $admin->update(['user_active' => false]);
        Log::info("Admin with ID {$admin->user_id}: {$admin->user_first_name} {$admin->user_last_name} deactivated. Reason: {$validated['deactivate-reason']}");

        // Queued rather than sent inline, same as the alumni path — an SMTP
        // failure shouldn't report an error for an account that was already
        // deactivated successfully.
        Mail::to($admin->user_email)->queue(new DeactAdminMail($admin, $validated['deactivate-reason']));

        return back()->with('success', 'Admin deactivated successfully!');
    }

    public function activateAdmin(Request $request, $id)
    {
        $admin = User::where('user_id', $id)->firstOrFail();

        if ($admin->user_role !== 'admin') {
            return back()->with('error', 'The specified user is not an admin.');
        }

        if (!$admin->user_active) {
            $admin->update(['user_active' => true]);
            Log::info("Admin with ID {$admin->user_id}: {$admin->user_first_name} {$admin->user_last_name} reactivated.");
            Mail::to($admin->user_email)->queue(new ActivateAdminMail($admin));
        }

        return back()->with('success', 'Admin activated successfully!');
    }
}
