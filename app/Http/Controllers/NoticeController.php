<?php

namespace App\Http\Controllers;

use App\Models\Notice;
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
            Notice::create($this->preparedData($validated) + [
                'thumbnail' => $thumbnailPath,
                'created_by' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            if ($thumbnailPath) {
                Storage::disk('public')->delete($thumbnailPath);
            }
            return back()->withErrors(['error' => 'Failed to add notice. Please try again.']);
        }

        return back()->with('success', 'Notice added successfully.');
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

        $notices = Notice::category($category)
            ->visibleToAlumni()
            ->orderBy('event_datetime')
            ->paginate(6)
            ->withQueryString();

        $interestedNoticeIds = Auth::user()->alumnus->interestedNotices->pluck('id')->all();

        return view('alumni.eventsSeminars', compact('notices', 'activeTab', 'interestedNoticeIds'));
    }

    public function alumniAnnouncements(Request $request)
    {
        $this->authorizeAlumnus();

        $notices = Notice::category('announcement')
            ->visibleToAlumni()
            ->orderByDesc('event_datetime')
            ->paginate(6)
            ->withQueryString();

        return view('alumni.announcements', compact('notices'));
    }

    /** Toggles the current alumnus's interest — one click marks/unmarks, no separate "cancel" flow needed. */
    public function toggleInterest(Notice $notice)
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

        return back()->with('success', $alreadyInterested ? 'Marked as not interested.' : 'Marked as interested!');
    }
}
