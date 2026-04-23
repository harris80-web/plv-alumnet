<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Mail\ForgetPasswordMail;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetTokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('auth.forgotPassword');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PasswordResetToken $passwordResetToken)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PasswordResetToken $passwordResetToken)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PasswordResetToken $passwordResetToken)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PasswordResetToken $passwordResetToken)
    {
        //
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

                $user = PasswordResetToken::create([
                    'email' => $validated['email'],
                    'token' => $token,
                    'created_at' => Carbon::now(),
                ]);

                
                Mail::to($user->email)->send(new ForgetPasswordMail($token, $user));
            });
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
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

        User::where('user_email', $validated['email'])->update([
            'user_password' => Hash::make($validated['user_password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return redirect()->route('auth.login')->with('success', 'Password has been reset successfully!');
    }
}
