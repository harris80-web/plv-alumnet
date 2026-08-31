<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Granular per-feature gate for the admin RBAC system — layered on top of
 * ->middleware('auth') (and each controller's own authorizeStaff() role
 * check), not a replacement for either. Usage: ->middleware('feature:jobs')
 * where the parameter is one of the Office::PERMISSIONS keys.
 *
 * A logged-out or non-staff request 403s here just like it would have
 * without this middleware (User::canAccessAdminFeature() returns false for
 * every role except admin/super_admin) — this only adds a *narrower* gate
 * for `admin` accounts specifically, never a looser one.
 */
class EnsureAdminFeatureAccess
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless(Auth::check() && Auth::user()->canAccessAdminFeature($feature), 403);

        return $next($request);
    }
}
