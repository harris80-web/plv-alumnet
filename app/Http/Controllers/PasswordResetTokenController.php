<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Mail\ForgetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PasswordResetTokenController extends Controller
{
    /** The "Forgot password?" form. Routed as passReset.forgotPassword. */
    public function index()
    {
        return view('auth.forgotPassword');
    }

    public function resetPassword($token)
    {
        return view('auth.resetPassword', compact('token'));
    }

    public function forgetPassword(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'email' => 'required|email|exists:users,user_email',
        ]);

        // Generate a password reset token
        $token = Str::random(64);

        try {
            DB::transaction(function () use ($validated, $token) {
                // email is this table's PRIMARY KEY, so create() blows up with a
                // duplicate-key violation the second time someone asks for a
                // reset (e.g. the first mail didn't arrive and they click again).
                // updateOrCreate replaces the outstanding token instead, which
                // also invalidates the previous link — same as Laravel's own broker.
                $user = PasswordResetToken::updateOrCreate(
                    ['email' => $validated['email']],
                    ['token' => $token, 'created_at' => Carbon::now()],
                );

                Mail::to($user->email)->send(new ForgetPasswordMail($token, $user));
            });
        } catch (\Exception $e) {
            // Never surface $e->getMessage() here — a DB error carries the
            // connection host, port and database name straight to the browser.
            Log::error('Password reset request failed: ' . $e->getMessage());

            return back()->withErrors(['email' => 'Could not send the reset email. Please try again.']);
        }
        return back()->with('success', 'Password reset token generated!');
    }

    public function updatePassword(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'email' => 'required|email|exists:users,user_email',
            'token' => 'required|string',
            'user_password' => 'required|string|confirmed|min:8',
            'user_password_confirmation' => 'required|string|same:user_password',
        ]);

        // Find the password reset token
        $passwordResetToken = DB::table('password_reset_tokens')
            ->where([
                'token' => $validated['token'],
                'email' => $validated['email'],
            ])->first();

        if (!$passwordResetToken) {
            return back()->withErrors(['email' => 'Invalid token or email.']);
        }

        // created_at was being written but never read, so a reset link stayed
        // valid forever — an old mail out of someone's inbox was still enough
        // to take the account over. Honour the same window Laravel's own broker
        // uses (config/auth.php: passwords.users.expire, 60 minutes here).
        $expiryMinutes = (int) config('auth.passwords.users.expire', 60);
        if (Carbon::parse($passwordResetToken->created_at)->addMinutes($expiryMinutes)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

            return back()->withErrors(['email' => 'This reset link has expired. Please request a new one.']);
        }

        User::where('user_email', $validated['email'])->update([
            'user_password' => Hash::make($validated['user_password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return redirect()->route('auth.login')->with('success', 'Password has been reset successfully!');
    }
}
