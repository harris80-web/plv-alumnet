@extends('mails.layout')

@section('title', 'New Job Application')
@section('preheader', 'A new alumni just applied to one of your job posts.')
@section('footer_disclaimer', "You're receiving this email as part of your employer partner account and job listings on PLV-AlumNet.")

@section('content')

    <!-- Heading -->
    <h1 style="margin:0 0 20px 0; font-family:'Montserrat', Arial, sans-serif; font-size:26px; line-height:1.3; font-weight:800; text-align:center;">
        <span style="color:#0E0F3B; background:linear-gradient(90deg, #0E0F3B 0%, #C73D1A 50%, #ED7A07 100%); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">New Job Application</span>
    </h1>

    <!-- Greeting -->
    <p style="margin:0 0 8px 0; font-family:'Inter', Arial, sans-serif; font-size:15px; color:#0E0F3B; text-align:center;">
        Hello, <strong style="font-weight:700;">{{ $job->user->user_first_name }} {{ $job->user->user_last_name }}</strong>,
    </p>

    <!-- Intro text -->
    <p style="margin:0 0 24px 0; font-family:'Inter', Arial, sans-serif; font-size:14px; line-height:1.6; color:#4B4B63; text-align:center;">
        An alumni just applied for your job post below:
    </p>

    <!-- Application box -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#F4F4F6; border-radius:10px; margin:0 auto 24px auto; max-width:320px; width:100%;">
        <tr>
            <td align="center" style="padding:16px 20px;">
                <p style="margin:0 0 6px 0; font-family:'Inter', Arial, sans-serif; font-size:14px; color:#0E0F3B; text-align:center;">
                    <span style="color:#C73D1A; font-weight:700;">Applicant:</span> {{ $alumni->user->user_first_name }} {{ $alumni->user->user_last_name }}
                </p>
                <p style="margin:0; font-family:'Inter', Arial, sans-serif; font-size:14px; color:#0E0F3B; text-align:center;">
                    <span style="color:#C73D1A; font-weight:700;">Job Post:</span> {{ $job->job_posting_title }}
                </p>
            </td>
        </tr>
    </table>

    <!-- CTA button -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 8px auto;">
        <tr>
            <td align="center" style="border-radius:8px; background-color:#0E0F3B;">
                <a href="{{ route('jobApplication.showApplications', $job->job_posting_id) }}"
                    style="display:inline-block; padding:12px 32px; font-family:'Montserrat', Arial, sans-serif; font-size:14px; font-weight:700; letter-spacing:.5px; color:#ffffff; text-decoration:none; border-radius:8px;">
                    View Applicant
                </a>
            </td>
        </tr>
    </table>

@endsection
