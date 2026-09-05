<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /** Polled by the bell icon dropdown (see partials/user-sidebar.blade.php). */
    public function index()
    {
        $notifications = Auth::user()->userNotifications()->latest()->limit(20)->get();

        return response()->json([
            'unreadCount' => $notifications->whereNull('read_at')->count(),
            'notifications' => $notifications->map->toNotificationArray(),
        ]);
    }

    /**
     * Fired only by the explicit "Mark all as read" action (dropdown button
     * or the "all" page) — no longer auto-triggered by opening the
     * dropdown, so unread state persists until the user actually dismisses
     * it. Dropdown calls this via fetch() with an Accept: application/json
     * header; the "all" page's plain <form> POST doesn't send one, so a
     * normal redirect-back is what that request actually needs.
     */
    public function markAllRead(Request $request)
    {
        Auth::user()->userNotifications()->unread()->update(['read_at' => now()]);

        return $request->wantsJson()
            ? response()->json(['success' => true])
            : back();
    }

    /**
     * Single entry point for "click a notification": marks just that one
     * read, then sends the user to whatever it's about (targetUrl()), or
     * back to the notifications list for types with no specific
     * destination. Used by both the dropdown and the full "all" page so
     * there's exactly one place individual mark-as-read + navigate lives.
     */
    public function open(UserNotification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return redirect($notification->targetUrl() ?? route('notifications.all'));
    }

    /**
     * The dedicated "all notifications" page linked from both bell
     * dropdowns' "View all notifications" — the dropdown itself only ever
     * shows the latest 20 with no pagination. Two view families like the
     * rest of the app: admin/super_admin get the sidebar-layout view,
     * alumni/employer get the top-nav one.
     *
     * No longer marks everything read as a side effect of viewing this
     * page — read state now only changes via an explicit click (open()
     * above) or "Mark all as read", so the "Unread" tab stays meaningful.
     */
    public function all(Request $request)
    {
        $activeTab = $request->query('tab') === 'unread' ? 'unread' : 'all';

        $notifications = Auth::user()->userNotifications()
            ->when($activeTab === 'unread', fn ($q) => $q->unread())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $user = Auth::user();
        $view = in_array($user->user_role, ['admin', 'super_admin'], true)
            ? 'superAdmin.notifications'
            : 'notifications.index';

        return view($view, compact('notifications', 'user', 'activeTab'));
    }
}
