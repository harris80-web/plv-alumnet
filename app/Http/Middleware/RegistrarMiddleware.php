<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RegistrarMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::user()->user_role !== 'registrar') {
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
