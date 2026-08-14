<?php

namespace App\Http\Controllers;

use App\Models\AlumniYearbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumniYearbookController extends Controller
{
    private function authorizeStaff(): void
    {
        abort_unless(in_array(Auth::user()->user_role, ['admin', 'super_admin']), 403);
    }

    private function validationRules(): array
    {
        return [
            'claiming_status' => ['nullable', 'in:' . implode(',', AlumniYearbook::CLAIMING_STATUSES)],
            'distribution_status' => ['nullable', 'in:' . implode(',', AlumniYearbook::DISTRIBUTION_STATUSES)],
            'distribution_scheduled_at' => ['nullable', 'date'],
            'distribution_building' => ['nullable', 'string', 'max:255'],
            'distribution_room_floor' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * One Save button for the whole modal (both the Claiming Status and
     * Distribution Info tabs) — the form always carries every field's
     * current value forward regardless of which tab is showing, so a
     * straight fill() is correct here even though only one tab may have
     * actually been touched.
     */
    public function updateYearbook(Request $request, $id)
    {
        $this->authorizeStaff();

        $validated = $request->validate($this->validationRules());
        $yearbook = AlumniYearbook::findOrFail($id);
        $yearbook->applyUpdate($validated, Auth::id());

        return back()->with('success', 'Alumni yearbook record updated.');
    }

    /**
     * Bulk-edit modal has no per-record current values to prefill, so
     * unlike updateYearbook() this only applies fields the admin actually
     * filled in — an empty field here must NOT blank out that field on
     * every selected record.
     */
    public function bulkUpdateYearbook(Request $request)
    {
        $this->authorizeStaff();

        $validated = $request->validate(array_merge($this->validationRules(), [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:alumni_yearbooks,id'],
        ]));

        $fields = collect($validated)->except(['ids'])->filter(fn ($value) => $value !== null && $value !== '')->all();

        if (empty($fields)) {
            return back()->with('error', 'Nothing to update — fill in at least one field.');
        }

        $yearbooks = AlumniYearbook::whereIn('id', $validated['ids'])->get();
        foreach ($yearbooks as $yearbook) {
            $yearbook->applyUpdate($fields, Auth::id());
        }

        return back()->with('success', count($yearbooks) . ' yearbook record(s) updated.');
    }
}
