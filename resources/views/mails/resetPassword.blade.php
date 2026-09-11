@extends('mails.layout')

@section('title', 'Reset Your PLV-AlumNet Password')
@section('preheader', 'Reset the password for your PLV-AlumNet account.')
@php
    $roleContext = match($user->user_role ?? null) {
        'super_admin' => 'super administrator privileges',
        'admin' => 'administrator privileges',
        'employer' => 'employer partner account',
        'alumni' => 'alumni membership',
        default => 'registered account',
    };
@endphp
@section('footer_disclaimer', "You're receiving this email as part of your {$roleContext} on PLV-AlumNet.")

@section('content')

    <!-- Heading -->
    <h1 style="margin:0 0 20px 0; font-family:'Montserrat', Arial, sans-serif; font-size:26px; line-height:1.3; font-weight:800; text-align:center;">
        <span style="color:#0E0F3B; background:linear-gradient(90deg, #0E0F3B 0%, #C73D1A 50%, #ED7A07 100%); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">Reset Your Password</span>
    </h1>

    <!-- Intro text -->
    <p style="margin:0 0 24px 0; font-family:'Inter', Arial, sans-serif; font-size:14px; line-height:1.6; color:#4B4B63; text-align:center;">
        We received a request to reset the password for the PLV-AlumNet account registered to <strong style="color:#0E0F3B;">{{ $user->email }}</strong>. Click the button below to choose a new password.
    </p>

    <!-- Reset button -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 24px auto;">
        <tr>
            <td align="center" style="border-radius:8px; background-color:#0E0F3B;">
                <a href="{{ route('passReset.resetPassword', ['token' => $token]) }}"
                    style="display:inline-block; padding:12px 32px; font-family:'Montserrat', Arial, sans-serif; font-size:14px; font-weight:700; letter-spacing:.5px; color:#ffffff; text-decoration:none; border-radius:8px;">
                    Reset Password
                </a>
            </td>
        </tr>
    </table>

    <!-- Notice box -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#FFF0EE; border:1px solid #FACDCD; border-radius:8px; margin:0 auto; max-width:380px; width:100%;">
        <tr>
            <td align="center" style="padding:10px 16px;">
                <p style="margin:0; font-family:'Inter', Arial, sans-serif; font-size:12px; line-height:1.5; font-weight:600; color:#C73D1A; text-align:center;">
                    <span style="display:inline-block; margin-right:4px; font-size:13px; vertical-align:middle;">&#9888;</span>
                    <span style="vertical-align:middle;">If you did not request this, you can safely ignore this email — your password will remain unchanged.</span>
                </p>
            </td>
        </tr>
    </table>

@endsection
