<?php

namespace App\Http\Controllers;

use App\Models\AlumniId;
use App\Models\AlumniYearbook;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumniIdController extends Controller
{
    private function authorizeStaff(): void
    {
        abort_unless(in_array(Auth::user()->user_role, ['admin', 'super_admin']), 403);
    }

    /**
     * One page, two tabs (see resources/views/superAdmin/alumniIdManagement.blade.php)
     * — Alumni ID and Alumni Yearbook are separate features sharing the same
     * sidebar entry, so both datasets load together here rather than as two
     * separate page loads.
     */
    public function index()
    {
        $this->authorizeStaff();

        $alumniIds = AlumniId::with(['alumnus.user', 'alumnus.program', 'updatedBy'])
            ->latest('created_at')
            ->get();

        $statusCounts = [
            'total' => $alumniIds->count(),
            'pending' => $alumniIds->where('status', 'pending')->count(),
            'under_review' => $alumniIds->where('status', 'under_review')->count(),
            'ready_to_claim' => $alumniIds->where('status', 'ready_to_claim')->count(),
            'claimed' => $alumniIds->where('status', 'claimed')->count(),
        ];

        $yearbooks = AlumniYearbook::with(['alumnus.user', 'updatedBy'])
            ->latest('created_at')
            ->get();

        $yearbookCounts = [
            'total' => $yearbooks->count(),
            'pending' => $yearbooks->where('claiming_status', 'pending')->count(),
            'on_hand' => $yearbooks->where('claiming_status', 'on_hand')->count(),
            'ready_to_claim' => $yearbooks->where('claiming_status', 'ready_to_claim')->count(),
            'claimed' => $yearbooks->where('claiming_status', 'claimed')->count(),
            'not_yet_claimed' => $yearbooks->where('claiming_status', 'not_yet_claimed')->count(),
        ];

        return view('superAdmin.alumniIdManagement', compact('alumniIds', 'statusCounts', 'yearbooks', 'yearbookCounts'));
    }

    /** The single "Mark ..." button — advances exactly one stage. */
    public function mark($id)
    {
        $this->authorizeStaff();

        $alumniId = AlumniId::findOrFail($id);
        $alumniId->markNext();
        $this->notifyStatusChange($alumniId);

        return back()->with('success', 'Alumni ID status updated to ' . $alumniId->statusLabel() . '.');
    }

    /** Explicit set from the view/update modal's status picker. */
    public function updateStatus(Request $request, $id)
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', AlumniId::STATUSES)],
        ]);

        $alumniId = AlumniId::findOrFail($id);
        $alumniId->setStatus($validated['status']);
        $this->notifyStatusChange($alumniId);

        return back()->with('success', 'Alumni ID status updated to ' . $alumniId->statusLabel() . '.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:alumni_ids,id'],
            'status' => ['required', 'in:' . implode(',', AlumniId::STATUSES)],
        ]);

        $alumniIds = AlumniId::whereIn('id', $validated['ids'])->get();

        $count = AlumniId::whereIn('id', $validated['ids'])->update([
            'status' => $validated['status'],
            'status_updated_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        $label = AlumniId::statusLabels()[$validated['status']];

        foreach ($alumniIds as $alumniId) {
            $this->notifyStatusChange($alumniId, $label);
        }

        return back()->with('success', "{$count} alumni ID(s) updated to {$label}.");
    }

    private function notifyStatusChange(AlumniId $alumniId, ?string $label = null): void
    {
        UserNotification::create([
            'user_id' => $alumniId->alumnus_id,
            'type' => 'alumni_id_status',
            'title' => 'Alumni ID status updated',
            'body' => 'Your Alumni ID status is now "' . ($label ?? $alumniId->statusLabel()) . '".',
        ]);
    }
}
