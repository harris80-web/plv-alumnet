@extends('mails.layout')

@section('title', 'PLV-AlumNet Account Deactivated')
@section('preheader', 'Your PLV-AlumNet account has been deactivated.')
@section('footer_disclaimer', "You're receiving this email as part of your alumni account status updates on PLV-AlumNet.")

@section('content')

    <!-- Heading -->
    <h1 style="margin:0 0 20px 0; font-family:'Montserrat', Arial, sans-serif; font-size:26px; line-height:1.3; font-weight:800; text-align:center;">
        <span style="color:#0E0F3B; background:linear-gradient(90deg, #0E0F3B 0%, #C73D1A 50%, #ED7A07 100%); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">Account Deactivated</span>
    </h1>

    <!-- Greeting -->
    <p style="margin:0 0 8px 0; font-family:'Inter', Arial, sans-serif; font-size:15px; color:#0E0F3B; text-align:center;">
        Hello, <strong style="font-weight:700;">{{ $user->user_first_name }} {{ $user->user_last_name }}</strong>,
    </p>

    <!-- Intro text -->
    <p style="margin:0 0 24px 0; font-family:'Inter', Arial, sans-serif; font-size:14px; line-height:1.6; color:#4B4B63; text-align:center;">
        Your PLV-AlumNet account has been deactivated by our administrators for the following reason:
    </p>

    <!-- Reason box -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#F4F4F6; border-radius:10px; margin:0 auto 24px auto; max-width:340px; width:100%;">
        <tr>
            <td align="center" style="padding:16px 20px;">
                <p style="margin:0; font-family:'Inter', Arial, sans-serif; font-size:14px; color:#0E0F3B; line-height:1.6; text-align:center;">
                    <span style="color:#DC2626; font-weight:700;">Reason:</span> {{ $reason }}
                </p>
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
                    <span style="vertical-align:middle;">If you believe this was a mistake, please contact the PLV-AlumNet Alumni Relations Office.</span>
                </p>
            </td>
        </tr>
    </table>

@endsection
