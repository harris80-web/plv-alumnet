<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AlumniMiddleware;
use App\Http\Middleware\EmployerMiddleware;
use App\Http\Middleware\EnsureAdminFeatureAccess;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\RegistrarMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The built-in `auth` middleware's default unauthenticated-guest
        // redirect looks for a route literally named "login" — this app's
        // login route is named "auth.login" instead, so every route
        // guarded by ->middleware('auth') 500'd for a logged-out visitor
        // (RouteNotFoundException: Route [login] not defined) instead of
        // redirecting to the login form.
        $middleware->redirectGuestsTo(fn () => route('auth.login'));

        $middleware->alias([
            'alumni' => AlumniMiddleware::class,
            'employer' => EmployerMiddleware::class,
            'registrar' => RegistrarMiddleware::class,
            'admin' => AdminMiddleware::class,
            'super_admin' => SuperAdminMiddleware::class,
            'feature' => EnsureAdminFeatureAccess::class,
        ]);

        // Global, not opt-in — a forced password change should hold regardless
        // of which route a flagged account tries to hit. No-op for everyone
        // else (see ForcePasswordChange::handle()).
        $middleware->web(append: [
            ForcePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
