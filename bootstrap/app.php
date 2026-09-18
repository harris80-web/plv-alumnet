<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        // A page left open past SESSION_LIFETIME (120 min) has a stale CSRF
        // token — the next POST from it (very often Logout, since that's
        // often the first thing clicked after a long idle) fails the CSRF
        // check and would otherwise show Laravel's raw "419 | Page Expired"
        // page. Redirect to login with a friendly message instead — the
        // login page's toast script already renders $errors, so no view
        // change is needed.
        //
        // Handler::prepareException() (see vendor/.../Foundation/Exceptions/
        // Handler.php) converts TokenMismatchException into a generic
        // HttpException(419, ...) BEFORE any renderable() callback runs, so
        // a callback type-hinted on TokenMismatchException itself would
        // never match — it has to catch the wrapping HttpException and
        // check the status code instead (the original TokenMismatchException
        // is still available via getPrevious(), checked here for precision
        // since a 419 is otherwise unused elsewhere in this app).
        $exceptions->render(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 419 && $e->getPrevious() instanceof TokenMismatchException) {
                return redirect()->route('auth.login')
                    ->withErrors(['message' => 'Your session expired. Please log in again.']);
            }
        });
    })->create();
