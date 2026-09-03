@extends('mails.layout')

@section('title', 'Job Post Declined')
@section('preheader', 'Your job post was not approved for publishing.')

@section('content')

    <!-- Heading -->
    <h1 style="margin:0 0 20px 0; font-family:'Montserrat', Arial, sans-serif; font-size:28px; line-height:1.3; font-weight:800; text-align:center;">
        <span style="color:#0E0F3B;">Job Post </span><span style="color:#DC2626; background:linear-gradient(90deg,#DC2626,#F87171); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">Declined</span>
    </h1>

    <!-- Greeting -->
    <p style="margin:0 0 6px 0; font-size:16px; color:#0E0F3B; text-align:center;">
        Hello, <strong>{{ $job->employer->user->user_first_name }} {{ $job->employer->user->user_last_name }}</strong>,
    </p>

    <!-- Intro text -->
    <p style="margin:0 0 24px 0; font-size:14px; line-height:1.6; color:#4B4B63; text-align:center;">
        Your job post below was not approved for publishing on the PLV-AlumNet Job Board. Please review our posting guidelines and feel free to submit a revised listing.
    </p>

    <!-- Job box -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#F4F4F6; border-radius:10px; margin:0 0 28px 0;">
        <tr>
            <td align="center" style="padding:18px 20px;">
                <p style="margin:0; font-size:14px; color:#0E0F3B;">
                    <span style="color:#DC2626; font-weight:700;">Job Post:</span> {{ $job->job_posting_title }}
                </p>
            </td>
        </tr>
    </table>

    <!-- Dashboard button -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="border-radius:8px; background-color:#0E0F3B;">
                <a href="{{ route('employer.dashboard') }}"
                    style="display:inline-block; padding:14px 40px; font-family:'Montserrat', Arial, sans-serif; font-size:14px; font-weight:700; letter-spacing:.5px; color:#ffffff; text-decoration:none; border-radius:8px;">
                    Go to Dashboard
                </a>
            </td>
        </tr>
    </table>

@endsection
