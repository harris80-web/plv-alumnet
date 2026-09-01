<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestimonialController extends Controller
{
    /** Gate for the admin moderation actions below — see UserController::authorizeStaff() for why this exists. */
    private function authorizeStaff(): void
    {
        abort_unless(Auth::check() && in_array(Auth::user()->user_role, ['admin', 'super_admin'], true), 403);
    }

    /**
     * AJAX target for the "Alumni Testimonials" pagination on both the
     * public homepage and the alumni dashboard (see
     * partials/testimonial-cards-script.blade.php) — returns just the cards
     * + pagination partial, not a full page. Public (no auth) since the
     * homepage instance is guest-visible.
     *
     * `path` tells the paginator which real page to build its link hrefs
     * against (this endpoint's own URL isn't a real page a user should ever
     * land on) — whitelisted to the two known embeds so it can't be used to
     * generate a link to an arbitrary path.
     */
    public function cardsFragment(Request $request)
    {
        // No withQueryString() here — this endpoint's own query string
        // (page, path) is just plumbing for this fetch, not state worth
        // preserving in the links it renders.
        $testimonials = Testimonial::with(['alumnus.user', 'alumnus.program'])
            ->where('testimonial_post', true)
            ->latest()
            ->paginate(4)
            ->fragment('alumni-testimonials');

        if (in_array($request->query('path'), ['/', '/alumni/dashboard'], true)) {
            $testimonials->setPath($request->query('path'));
        }

        return view('partials.testimonial-cards', compact('testimonials'));
    }

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
    public function show(Testimonial $testimonial)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Testimonial $testimonial)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        //
    }

    public function submitTestimonial(Request $request, $id)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'testimonial_body' => 'required|string|max:1000',
        ]);

        // Create a new testimonial record in the database
        try {
            DB::transaction(function () use ($validatedData, $id) {
                Testimonial::create([
                    'testimonial_body' => $validatedData['testimonial_body'],
                    'user_id' => $id,
                    'testimonial_post' => false,
                ]);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to add admin: ' . $e->getMessage()]);
        }
        $testimonials = Testimonial::all();

        $this->notifyStaffOfNewTestimonial($id);

        // Redirect back with a success message
        return redirect()->route('users.dashboardRedirect', compact('testimonials'))->with('success', 'Your testimonial has been submitted successfully!');
    }

    private function notifyStaffOfNewTestimonial($submitterId): void
    {
        $submitter = User::find($submitterId);
        $recipientIds = User::whereIn('user_role', ['admin', 'super_admin'])->pluck('user_id');
        if ($recipientIds->isEmpty()) {
            return;
        }

        $submitterName = $submitter ? "{$submitter->user_first_name} {$submitter->user_last_name}" : 'An alumnus';
        $now = now();
        $rows = $recipientIds->map(fn ($userId) => [
            'user_id' => $userId,
            'type' => 'testimonial_submitted',
            'title' => 'New testimonial submitted',
            'body' => "{$submitterName} submitted a testimonial awaiting review.",
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        UserNotification::insert($rows);
    }

    public function showTestimonials()
    {
        $this->authorizeStaff();
        $testimonials = Testimonial::with(['alumnus.user', 'alumnus.program'])->get();
        return view('superAdmin.testimonialManagement', compact('testimonials'));
    }

    public function postTestimonial($id)
    {
        $this->authorizeStaff();
        $testimonial = Testimonial::findOrFail($id);

        if ($testimonial->testimonial_post) {
            $testimonial->testimonial_post = false;
            $testimonial->save();
            return back()->with('success', 'Status updated successfully!');
        }

        $testimonial->testimonial_post = true;
        $testimonial->save();

        return back()->with('success', 'Status updated successfully!');
    }
    public function deleteTestimonial($id)
    {
        $this->authorizeStaff();
        $testimonial = Testimonial::findOrFail($id);

        Log::info("Admin with ID {$testimonial->testimonial_id}: {$testimonial->alumnus->user->user_first_name} {$testimonial->alumnus->user->user_last_name} deleted. Message: {$testimonial->testimonial_body}");

        $testimonial->delete();

        return back()->with('success', 'Status updated successfully!');
    }

    public function bulkPost(Request $request)
    {
        $this->authorizeStaff();
        $ids = explode(',', $request->input('ids'));
        Testimonial::whereIn('testimonial_id', $ids)->update(['testimonial_post' => true]);
        return back()->with('success', 'Selected testimonials published successfully!');
    }

    public function bulkHide(Request $request)
    {
        $this->authorizeStaff();
        $ids = explode(',', $request->input('ids'));
        Testimonial::whereIn('testimonial_id', $ids)->update(['testimonial_post' => false]);
        return back()->with('success', 'Selected testimonials hidden successfully!');
    }

    public function bulkDelete(Request $request)
    {
        $this->authorizeStaff();
        $ids = explode(',', $request->input('ids'));
        Testimonial::whereIn('testimonial_id', $ids)->delete();
        return back()->with('success', 'Selected testimonials deleted successfully!');
    }
}
