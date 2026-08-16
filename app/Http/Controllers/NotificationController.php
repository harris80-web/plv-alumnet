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
}
