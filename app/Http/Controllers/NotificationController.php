<?php

namespace App\Http\Controllers;

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

    /** Fired when the dropdown is opened — matches "seen it" the moment a user looks, not per-item. */
    public function markAllRead()
    {
        Auth::user()->userNotifications()->unread()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * The dedicated "all notifications" page linked from both bell
     * dropdowns' "View all notifications" — the dropdown itself only ever
     * shows the latest 20 with no pagination. Two view families like the
     * rest of the app: admin/super_admin get the sidebar-layout view,
     * alumni/employer get the top-nav one.
     */
    public function all()
    {
        $notifications = Auth::user()->userNotifications()
            ->latest()
            ->paginate(20);

        // Read here, before the bulk update below — the paginator's models
        // are already loaded into memory with their original read_at, so
        // the page still renders "what was unread when you opened this"
        // even though the DB is updated to all-read immediately after.
        Auth::user()->userNotifications()->unread()->update(['read_at' => now()]);

        $user = Auth::user();
        $view = in_array($user->user_role, ['admin', 'super_admin'], true)
            ? 'superAdmin.notifications'
            : 'notifications.index';

        return view($view, compact('notifications', 'user'));
    }
}
