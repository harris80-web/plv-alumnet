<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registered globally on the `web` group (see bootstrap/app.php) so it
 * can't be bypassed by simply not attaching it to a route — but it's a
 * no-op for everyone except an account with must_change_password set
 * (currently only newly-created alumni, see UserController::addAlumnus()),
 * so normal traffic is untouched.
 */
class ForcePasswordChange
{
    /**
     * Routes the flagged user must still be able to reach. Besides the
     * change-password page and logout, this includes background polling
     * endpoints (notification bell, chatbot widget) that run unconditionally
     * on every page via the shared header partials — including this very
     * page — and whose plain fetch() calls don't reliably set an
     * Accept: application/json header for the ajax()/expectsJson() check
     * below to catch.
     */
    private const EXEMPT_ROUTES = [
        'users.showChangePassword',
        'users.changePassword',
        'users.logout',
        'auth.login',
        'notifications.index',
        'notifications.markAllRead',
        'widget.start',
        'widget.send',
        'widget.poll',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->must_change_password) {
            return $next($request);
        }

        // Never intercept AJAX/JSON calls (messaging polling, the chatbot
        // widget, notification polling, etc.) — a redirect response there
        // would just break those quietly instead of showing the user anything.
        if ($request->expectsJson() || $request->ajax()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (in_array($routeName, self::EXEMPT_ROUTES, true)) {
            return $next($request);
        }

        return redirect()->route('users.showChangePassword')
            ->with('must_change_password_notice', 'For your security, please set a new password before continuing — this account was created with a temporary system-generated password.');
    }
}
