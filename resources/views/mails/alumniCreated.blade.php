@extends('mails.layout')

@section('title', 'Welcome to PLV-AlumNet')
@section('preheader', 'Your PLV-AlumNet account is ready — here are your login credentials.')

@section('content')

    <!-- Heading -->
    <h1 style="margin:0 0 20px 0; font-family:'Montserrat', Arial, sans-serif; font-size:28px; line-height:1.3; font-weight:800; text-align:center;">
        <span style="color:#0E0F3B;">Welcome to </span><span style="color:#C73D1A; background:linear-gradient(90deg,#C73D1A,#ED7A07); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">PLV-AlumNet!</span>
    </h1>

    <!-- Greeting -->
    <p style="margin:0 0 6px 0; font-size:16px; color:#0E0F3B; text-align:center;">
        Hello, <strong>{{ $user->user_first_name }} {{ $user->user_last_name }}</strong>,
    </p>

    <!-- Intro text -->
    <p style="margin:0 0 24px 0; font-size:14px; line-height:1.6; color:#4B4B63; text-align:center;">
        An account has been created for you. You can now log in using the credentials below:
    </p>

    <!-- Credentials box -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#F4F4F6; border-radius:10px; margin:0 0 28px 0;">
        <tr>
            <td align="center" style="padding:20px 20px;">
                <p style="margin:0 0 8px 0; font-size:14px; color:#0E0F3B;">
                    <span style="color:#C73D1A; font-weight:700;">Email:</span> {{ $user->user_email }}
                </p>
                <p style="margin:0; font-size:14px; color:#0E0F3B;">
                    <span style="color:#C73D1A; font-weight:700;">Password:</span> {{ $password }}
                </p>
            </td>
        </tr>
    </table>

    <!-- Login button -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px 0;">
        <tr>
            <td align="center" style="border-radius:8px; background-color:#0E0F3B;">
                <a href="{{ route('auth.login') }}"
                    style="display:inline-block; padding:14px 40px; font-family:'Montserrat', Arial, sans-serif; font-size:14px; font-weight:700; letter-spacing:.5px; color:#ffffff; text-decoration:none; border-radius:8px;">
                    Click here to Login
                </a>
            </td>
        </tr>
    </table>

    <!-- Warning box -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#FBEAEA; border-radius:8px;">
        <tr>
            <td align="center" style="padding:14px 18px;">
                <p style="margin:0; font-size:13px; line-height:1.5; font-weight:600; color:#C73D1A; text-align:center;">
                    &#9888; Please change your password immediately after logging in for security.
                </p>
            </td>
        </tr>
    </table>

@endsection
