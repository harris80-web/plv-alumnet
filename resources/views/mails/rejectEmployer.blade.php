@extends('mails.layout')

@section('title', 'PLV-AlumNet Employer Registration')
@section('preheader', 'An update on your PLV-AlumNet employer registration.')

@section('content')

    <!-- Heading -->
    <h1 style="margin:0 0 20px 0; font-family:'Montserrat', Arial, sans-serif; font-size:28px; line-height:1.3; font-weight:800; text-align:center;">
        <span style="color:#0E0F3B;">Registration Not </span><span style="color:#DC2626; background:linear-gradient(90deg,#DC2626,#F87171); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">Approved</span>
    </h1>

    <!-- Greeting -->
    <p style="margin:0 0 6px 0; font-size:16px; color:#0E0F3B; text-align:center;">
        Hello, <strong>{{ $user->user_first_name }} {{ $user->user_last_name }}</strong>,
    </p>

    <!-- Intro text -->
    <p style="margin:0 0 24px 0; font-size:14px; line-height:1.6; color:#4B4B63; text-align:center;">
        Thank you for your interest in joining PLV-AlumNet as an employer partner. After reviewing your registration, we're unable to approve it at this time for the following reason:
    </p>

    <!-- Reason box -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#F4F4F6; border-radius:10px; margin:0 0 28px 0;">
        <tr>
            <td align="center" style="padding:18px 20px;">
                <p style="margin:0; font-size:14px; color:#0E0F3B; line-height:1.6;">
                    <span style="color:#DC2626; font-weight:700;">Reason:</span> {{ $reason }}
                </p>
            </td>
        </tr>
    </table>

    <!-- Notice box -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#FBEAEA; border-radius:8px;">
        <tr>
            <td align="center" style="padding:14px 18px;">
                <p style="margin:0; font-size:13px; line-height:1.5; font-weight:600; color:#C73D1A; text-align:center;">
                    Your registration details have been removed from our system. You're welcome to re-apply once the noted issue has been addressed.
                </p>
            </td>
        </tr>
    </table>

@endsection
