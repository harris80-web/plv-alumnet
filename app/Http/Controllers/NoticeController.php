<?php

namespace App\Http\Controllers;

use App\Models\Alumnus;
use App\Models\Notice;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class NoticeController extends Controller
{
    private function authorizeStaff(): void
    {
        abort_unless(in_array(Auth::user()->user_role, ['admin', 'super_admin']), 403);
    }

    private function authorizeAlumnus(): void
    {
        abort_unless(Auth::user()->user_role === 'alumni', 403);
    }

    private function authorizeEmployer(): void
    {
        abort_unless(Auth::user()->user_role === 'employer', 403);
    }

    public function index()
    {
        $this->authorizeStaff();

        $notices = Notice::withCount('interestedAlumni')
            ->with(['creator', 'interestedAlumni.user'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $counts = [
            'total' => $notices->count(),
            'event' => $notices->where('category', 'event')->count(),
            'seminar' => $notices->where('category', 'seminar')->count(),
            'announcement' => $notices->where('category', 'announcement')->count(),
        ];

        return view('superAdmin.noticesManagement', compact('notices', 'counts'));
    }

    /**
     * Date and time come in as two separate inputs (see the add/edit forms)
     * and get combined into one event_datetime column here. Location is
     * required for event/seminar only; speaker fields only for seminar —
     * enforced here rather than trusting the client, since the category
     * dropdown deciding which fields even render is purely a JS/UX concern.
     */
    private function validationRules(): array
    {
        return [
            'category' => ['required', 'in:' . implode(',', Notice::CATEGORIES)],
            'title' => ['required', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            'event_date' => ['required', 'date'],
            'event_time' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255', 'required_unless:category,announcement'],
            'description' => ['nullable', 'string'],
            'recipient' => ['required', 'in:' . implode(',', Notice::RECIPIENTS)],
            'speaker_name' => ['nullable', 'string', 'max:150', 'required_if:category,seminar'],
            'speaker_topic' => ['nullable', 'string', 'max:255', 'required_if:category,seminar'],
        ];
    }

    /** Blanks out whichever fields don't apply to the chosen category, regardless of what the client sent. */
    private function preparedData(array $validated): array
    {
        $category = $validated['category'];

        return [
            'category' => $category,
            'title' => $validated['title'],
            'event_datetime' => Carbon::parse($validated['event_date'] . ' ' . $validated['event_time']),
            'location' => $category === 'announcement' ? null : $validated['location'],
            // Same Quill toolbar as the job posting form, so the same
            // sanitizer profile applies (see config/purifier.php).
            'description' => empty($validated['description']) ? null : Purifier::clean($validated['description'], 'job_description'),
            'recipient' => $validated['recipient'],
            'speaker_name' => $category === 'seminar' ? $validated['speaker_name'] : null,
            'speaker_topic' => $category === 'seminar' ? $validated['speaker_topic'] : null,
        ];
    }

    public function store(Request $request)
    {
        $this->authorizeStaff();

        $validated = $request->validate($this->validationRules());

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('noticeThumbnails', 'public');
        }

        try {
            $notice = Notice::create($this->preparedData($validated) + [
                'thumbnail' => $thumbnailPath,
                'created_by' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            if ($thumbnailPath) {
                Storage::disk('public')->delete($thumbnailPath);
            }
            return back()->withErrors(['error' => 'Failed to add notice. Please try again.']);
        }

        if (in_array($notice->recipient, ['alumni', 'everyone'], true)) {
            $this->notifyAllAlumni($notice);
        }

        return back()->with('success', 'Notice added successfully.');
    }

    /**
     * Bulk-inserted (not looped Eloquent::create) since this can fan out to
     * every alumnus in the system — a single INSERT keeps "admin publishes a
     * notice" from turning into hundreds of individual queries.
     */
    private function notifyAllAlumni(Notice $notice): void
    {
        $alumnusIds = Alumnus::pluck('user_id');
        if ($alumnusIds->isEmpty()) {
            return;
        }

        $isAnnouncement = $notice->category === 'announcement';
        $now = now();

        $rows = $alumnusIds->map(fn ($userId) => [
            'user_id' => $userId,
            'type' => $isAnnouncement ? 'new_announcement' : 'new_' . $notice->category,
            'title' => $isAnnouncement ? 'New announcement' : 'New ' . $notice->categoryLabel() . ' posted',
            'body' => $notice->title,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        UserNotification::insert($rows);
    }

    public function update(Request $request, Notice $notice)
    {
        $this->authorizeStaff();

        $validated = $request->validate($this->validationRules());

        $oldThumbnail = $notice->thumbnail;
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('noticeThumbnails', 'public');
        }

        try {
            $notice->update($this->preparedData($validated) + [
                'thumbnail' => $thumbnailPath ?? $oldThumbnail,
            ]);

            if ($thumbnailPath && $oldThumbnail && Storage::disk('public')->exists($oldThumbnail)) {
                Storage::disk('public')->delete($oldThumbnail);
            }
        } catch (\Exception $e) {
            if ($thumbnailPath) {
                Storage::disk('public')->delete($thumbnailPath);
            }
            return back()->withErrors(['error' => 'Failed to update notice. Please try again.']);
        }

        return back()->with('success', 'Notice updated successfully.');
    }

    public function destroy(Notice $notice)
    {
        $this->authorizeStaff();

        if ($notice->thumbnail && Storage::disk('public')->exists($notice->thumbnail)) {
            Storage::disk('public')->delete($notice->thumbnail);
        }

        $notice->delete();

        return back()->with('success', 'Notice deleted successfully.');
    }

    /**
     * One page, two tabs — ?tab=events (default) or ?tab=seminar, each with
     * its own paginated query rather than loading both categories at once,
     * so "pagination below" applies per-tab like the rest of this app's
     * tabbed listings (see JobPostingController::showJobBoard/showBookmarks).
     */
    public function alumniEventsAndSeminars(Request $request)
    {
        $this->authorizeAlumnus();

        $activeTab = $request->query('tab') === 'seminar' ? 'seminar' : 'events';
        $category = $activeTab === 'seminar' ? 'seminar' : 'event';

        // Past events/seminars are done — this listing is "what's coming up",
        // not an archive (see Notice::scopeUpcoming()).
        $notices = Notice::category($category)
            ->visibleToAlumni()
            ->upcoming()
            ->orderBy('event_datetime')
            ->paginate(6)
            ->withQueryString();

        $interestedNoticeIds = Auth::user()->alumnus->interestedNotices->pluck('id')->all();
        $user = Auth::user();
        // Deep-link from the dashboard's "Campus Events" cards (?notice=123)
        // — the view auto-opens this notice's detail modal on load, if it's
        // present on the current page.
        $openNoticeId = $request->query('notice');

        return view('alumni.eventsSeminars', compact('notices', 'activeTab', 'interestedNoticeIds', 'user', 'openNoticeId'));
    }

    public function alumniAnnouncements(Request $request)
    {
        $this->authorizeAlumnus();

        $notices = Notice::category('announcement')
            ->visibleToAlumni()
            ->orderByDesc('event_datetime')
            ->paginate(6)
            ->withQueryString();

        $user = Auth::user();
        // Deep-link from the dashboard's "Announcements" cards (?notice=123)
        // — the view auto-opens this notice's detail modal on load, if it's
        // present on the current page.
        $openNoticeId = $request->query('notice');

        return view('alumni.announcements', compact('notices', 'user', 'openNoticeId'));
    }

    /**
     * Employer counterpart to alumniEventsAndSeminars() — same view
     * (resources/views/alumni/eventsSeminars.blade.php is role-aware: it
     * switches header/footer and hides the alumni-only "Interested" button
     * based on $user->user_role), just scoped to notices visible to the
     * general/employer audience instead of alumni, and with no
     * interestedNoticeIds (employers have no interestedNotices relation —
     * the view never reads that array for a non-alumni user, but it still
     * needs to exist to avoid an undefined-variable error).
     */
    public function employerEventsAndSeminars(Request $request)
    {
        $this->authorizeEmployer();

        $activeTab = $request->query('tab') === 'seminar' ? 'seminar' : 'events';
        $category = $activeTab === 'seminar' ? 'seminar' : 'event';

        // Same "upcoming only" rule as alumniEventsAndSeminars() above.
        $notices = Notice::category($category)
            ->visibleToEmployer()
            ->upcoming()
            ->orderBy('event_datetime')
            ->paginate(6)
            ->withQueryString();

        $user = Auth::user();
        $interestedNoticeIds = [];
        $openNoticeId = $request->query('notice');

        return view('alumni.eventsSeminars', compact('notices', 'activeTab', 'interestedNoticeIds', 'user', 'openNoticeId'));
    }

    /** Employer counterpart to alumniAnnouncements() — see employerEventsAndSeminars() for why the same view is reused. */
    public function employerAnnouncements(Request $request)
    {
        $this->authorizeEmployer();

        $notices = Notice::category('announcement')
            ->visibleToEmployer()
            ->orderByDesc('event_datetime')
            ->paginate(6)
            ->withQueryString();

        $user = Auth::user();
        $openNoticeId = $request->query('notice');

        return view('alumni.announcements', compact('notices', 'user', 'openNoticeId'));
    }

    /** Toggles the current alumnus's interest — one click marks/unmarks, no separate "cancel" flow needed. */
    public function toggleInterest(Request $request, Notice $notice)
    {
        $this->authorizeAlumnus();
        abort_if($notice->category === 'announcement', 403);

        $alumnus = Auth::user()->alumnus;
        $alreadyInterested = $alumnus->interestedNotices()->where('notice_id', $notice->id)->exists();

        if ($alreadyInterested) {
            $alumnus->interestedNotices()->detach($notice->id);
        } else {
            $alumnus->interestedNotices()->attach($notice->id);
        }

        $nowInterested = !$alreadyInterested;

        // The card grid and the detail modal both submit this as a plain
        // form (full page reload, flash message) by default; the JS on
        // eventsSeminars.blade.php upgrades that to a fetch() call instead so
        // it can show a confirmation modal instantly without reloading —
        // this branch is what that fetch() call gets back.
        if ($request->wantsJson()) {
            return response()->json([
                'interested' => $nowInterested,
                'title' => $notice->title,
            ]);
        }

        return back()->with('success', $nowInterested ? 'Marked as interested!' : 'Marked as not interested.');
    }
}
