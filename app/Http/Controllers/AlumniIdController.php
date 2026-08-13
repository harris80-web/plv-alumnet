<?php

namespace App\Http\Controllers;

use App\Models\AlumniId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumniIdController extends Controller
{
    private function authorizeStaff(): void
    {
        abort_unless(in_array(Auth::user()->user_role, ['admin', 'super_admin']), 403);
    }

    public function index()
    {
        $this->authorizeStaff();

        $alumniIds = AlumniId::with(['alumnus.user', 'alumnus.program'])
            ->latest('created_at')
            ->get();

        $statusCounts = [
            'total' => $alumniIds->count(),
            'pending' => $alumniIds->where('status', 'pending')->count(),
            'under_review' => $alumniIds->where('status', 'under_review')->count(),
            'ready_to_claim' => $alumniIds->where('status', 'ready_to_claim')->count(),
            'claimed' => $alumniIds->where('status', 'claimed')->count(),
        ];

        return view('superAdmin.alumniIdManagement', compact('alumniIds', 'statusCounts'));
    }

    /** The single "Mark ..." button — advances exactly one stage. */
    public function mark($id)
    {
        $this->authorizeStaff();

        $alumniId = AlumniId::findOrFail($id);
        $alumniId->markNext();

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

        $count = AlumniId::whereIn('id', $validated['ids'])->update([
            'status' => $validated['status'],
            'status_updated_at' => now(),
        ]);

        $label = AlumniId::statusLabels()[$validated['status']];

        return back()->with('success', "{$count} alumni ID(s) updated to {$label}.");
    }
}
