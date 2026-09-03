@extends('mails.layout')

@section('title', 'Application Update')
@section('preheader', 'An update on your job application.')

@section('content')

    <!-- Heading -->
    <h1 style="margin:0 0 20px 0; font-family:'Montserrat', Arial, sans-serif; font-size:28px; line-height:1.3; font-weight:800; text-align:center;">
        <span style="color:#0E0F3B;">Application </span><span style="color:#DC2626; background:linear-gradient(90deg,#DC2626,#F87171); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">Update</span>
    </h1>

    <!-- Greeting -->
    <p style="margin:0 0 6px 0; font-size:16px; color:#0E0F3B; text-align:center;">
        Hello, <strong>{{ $application->alumnus->user->user_first_name }} {{ $application->alumnus->user->user_last_name }}</strong>,
    </p>

    <!-- Intro text -->
    <p style="margin:0 0 24px 0; font-size:14px; line-height:1.6; color:#4B4B63; text-align:center;">
        Thank you for your interest in the position below. After careful consideration, the employer has decided to move forward with other candidates at this time.
    </p>

    <!-- Job box -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#F4F4F6; border-radius:10px; margin:0 0 28px 0;">
        <tr>
            <td align="center" style="padding:18px 20px;">
                <p style="margin:0; font-size:14px; color:#0E0F3B;">
                    <span style="color:#DC2626; font-weight:700;">Position:</span> {{ $application->job->job_posting_title }}
                </p>
            </td>
        </tr>
    </table>

    <!-- CTA button -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="border-radius:8px; background-color:#0E0F3B;">
                <a href="{{ route('jobPosting.jobBoard') }}"
                    style="display:inline-block; padding:14px 40px; font-family:'Montserrat', Arial, sans-serif; font-size:14px; font-weight:700; letter-spacing:.5px; color:#ffffff; text-decoration:none; border-radius:8px;">
                    Browse More Opportunities
                </a>
            </td>
        </tr>
    </table>

@endsection
