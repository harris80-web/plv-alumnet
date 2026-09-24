<?php

namespace App\Http\Middleware;

use App\Models\Office;
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
 * A logged-out or non-staff request still hard-403s here just like it would
 * have without this middleware (User::canAccessAdminFeature() returns false
 * for every role except admin/super_admin) — that's a real "you shouldn't
 * be here at all" case. An authenticated admin who's simply missing this
 * one permission is a narrower, expected case (e.g. they clicked a
 * notification broadcast to every admin, but this feature isn't in their
 * grant) — that one redirects to the dashboard with a flashed message
 * instead, shown there via the site-wide alert modal
 * (partials/alert-modal.blade.php, included in super-admin-header) rather
 * than a raw error page.
 */
class EnsureAdminFeatureAccess
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless(Auth::check() && in_array(Auth::user()->user_role, ['admin', 'super_admin'], true), 403);

        if (! Auth::user()->canAccessAdminFeature($feature)) {
            $label = Office::PERMISSIONS[$feature] ?? $feature;

            return redirect()
                ->route('superAdmin.dashboard')
                ->with('permission_denied', "You don't have permission to access {$label}. Contact a Super Admin if you need access.");
        }

        return $next($request);
    }
}
