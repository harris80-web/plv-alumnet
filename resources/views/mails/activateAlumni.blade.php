@extends('mails.layout')

@section('title', 'Your PLV-AlumNet Account is Active')
@section('preheader', 'Good news — your PLV-AlumNet account has been reactivated.')
@section('footer_disclaimer', "You're receiving this email as part of your registered alumni account with Pamantasan ng Lungsod ng Valenzuela on PLV-AlumNet.")

@section('content')

    <!-- Heading -->
    <h1 style="margin:0 0 20px 0; font-family:'Montserrat', Arial, sans-serif; font-size:26px; line-height:1.3; font-weight:800; text-align:center;">
        <span style="color:#0E0F3B; background:linear-gradient(90deg, #0E0F3B 0%, #C73D1A 50%, #ED7A07 100%); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">Your Account is Active!</span>
    </h1>

    <!-- Greeting -->
    <p style="margin:0 0 8px 0; font-family:'Inter', Arial, sans-serif; font-size:15px; color:#0E0F3B; text-align:center;">
        Hello, <strong style="font-weight:700;">{{ $user->user_first_name }} {{ $user->user_last_name }}</strong>,
    </p>

    <!-- Intro text -->
    <p style="margin:0 0 24px 0; font-family:'Inter', Arial, sans-serif; font-size:14px; line-height:1.6; color:#4B4B63; text-align:center;">
        Good news! Your PLV-AlumNet account has been reactivated by our administrators. You can now log back in and continue exploring opportunities.
    </p>

    <!-- Login button -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 8px auto;">
        <tr>
            <td align="center" style="border-radius:8px; background-color:#0E0F3B;">
                <a href="{{ route('auth.login') }}"
                    style="display:inline-block; padding:12px 32px; font-family:'Montserrat', Arial, sans-serif; font-size:14px; font-weight:700; letter-spacing:.5px; color:#ffffff; text-decoration:none; border-radius:8px;">
                    Click here to Login
                </a>
            </td>
        </tr>
    </table>

@endsection
