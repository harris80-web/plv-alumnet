@extends('mails.layout')

@section('title', 'Reset Your PLV-AlumNet Password')
@section('preheader', 'Reset the password for your PLV-AlumNet account.')

@section('content')

    <!-- Heading -->
    <h1 style="margin:0 0 20px 0; font-family:'Montserrat', Arial, sans-serif; font-size:28px; line-height:1.3; font-weight:800; text-align:center;">
        <span style="color:#0E0F3B; background:linear-gradient(90deg,#0E0F3B,#C73D1A,#ED7A07); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">Reset Your </span><span style="color:#C73D1A; background:linear-gradient(90deg,#0E0F3B,#C73D1A,#ED7A07); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">Password</span>
    </h1>

    <!-- Intro text -->
    <p style="margin:0 0 24px 0; font-family:'Inter', Arial, sans-serif; font-size:14px; line-height:1.6; color:#4B4B63; text-align:center;">
        We received a request to reset the password for the PLV-AlumNet account registered to <strong style="color:#0E0F3B;">{{ $user->email }}</strong>. Click the button below to choose a new password.
    </p>

    <!-- Reset button -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px 0;">
        <tr>
            <td align="center" style="border-radius:8px; background-color:#0E0F3B;">
                <a href="{{ route('passReset.resetPassword', ['token' => $token]) }}"
                    style="display:inline-block; padding:14px 40px; font-family:'Montserrat', Arial, sans-serif; font-size:14px; font-weight:700; letter-spacing:.5px; color:#ffffff; text-decoration:none; border-radius:8px;">
                    Reset Password
                </a>
            </td>
        </tr>
    </table>

    <!-- Notice box -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#FBEAEA; border-radius:8px;">
        <tr>
            <td align="center" style="padding:14px 18px;">
                <p style="margin:0; font-size:13px; line-height:1.5; font-weight:600; color:#C73D1A; text-align:center;">
                    <span style="display:inline-block; width:16px; height:16px; line-height:16px; background-color:#C73D1A; color:#ffffff; border-radius:50%; font-family:Arial, sans-serif; font-size:11px; font-weight:800; text-align:center; vertical-align:middle;">!</span>
                    <span style="vertical-align:middle;">If you did not request this, you can safely ignore this email — your password will remain unchanged.</span>
                </p>
            </td>
        </tr>
    </table>

@endsection
