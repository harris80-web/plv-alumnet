@extends('mails.layout')

@section('title', 'Job Post Approved')
@section('preheader', 'Your job post is now live on the PLV-AlumNet Job Board.')
@section('footer_disclaimer', "You're receiving this email as part of your employer partner account and job listings on PLV-AlumNet.")

@section('content')

    <!-- Heading -->
    <h1 style="margin:0 0 20px 0; font-family:'Montserrat', Arial, sans-serif; font-size:26px; line-height:1.3; font-weight:800; text-align:center;">
        <span style="color:#0E0F3B; background:linear-gradient(90deg, #0E0F3B 0%, #C73D1A 50%, #ED7A07 100%); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">Job Post Approved!</span>
    </h1>

    <!-- Greeting -->
    <p style="margin:0 0 8px 0; font-family:'Inter', Arial, sans-serif; font-size:15px; color:#0E0F3B; text-align:center;">
        Hello, <strong style="font-weight:700;">{{ $job->employer->user->user_first_name }} {{ $job->employer->user->user_last_name }}</strong>,
    </p>

    <!-- Intro text -->
    <p style="margin:0 0 24px 0; font-family:'Inter', Arial, sans-serif; font-size:14px; line-height:1.6; color:#4B4B63; text-align:center;">
        Great news! Your job post below has been approved and is now live on the PLV-AlumNet Job Board.
    </p>

    <!-- Job box -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#F4F4F6; border-radius:10px; margin:0 auto 24px auto; max-width:320px; width:100%;">
        <tr>
            <td align="center" style="padding:16px 20px;">
                <p style="margin:0; font-family:'Inter', Arial, sans-serif; font-size:14px; color:#0E0F3B; text-align:center;">
                    <span style="color:#15803D; font-weight:700;">Job Post:</span> {{ $job->job_posting_title }}
                </p>
            </td>
        </tr>
    </table>

    <!-- CTA button -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 8px auto;">
        <tr>
            <td align="center" style="border-radius:8px; background-color:#0E0F3B;">
                <a href="{{ route('jobPosting.jobBoard') }}"
                    style="display:inline-block; padding:12px 32px; font-family:'Montserrat', Arial, sans-serif; font-size:14px; font-weight:700; letter-spacing:.5px; color:#ffffff; text-decoration:none; border-radius:8px;">
                    View on Job Board
                </a>
            </td>
        </tr>
    </table>

@endsection
