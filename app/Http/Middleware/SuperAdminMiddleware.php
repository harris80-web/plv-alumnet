<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::user()->user_role !== 'super_admin') {
                return $next($request);
            } else {
                Auth::logout();
                return redirect()->route('auth.login');
            }
        } else {
            Auth::logout();
            return redirect()->route('auth.login');
        }
    }
}
