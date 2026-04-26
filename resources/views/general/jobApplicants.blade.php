<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body>
    <div>
        <h2>JOB</h2>
        <p>{{ $jobPost->job_posting_title }}</p>
        <p>{{ $jobPost->job_posting_company }}</p>
        <p>{{ $jobPost->job_posting_address }}</p>
        <p>{{ $jobPost->job_posting_employment_type }}</p>
        <p>{{ $jobPost->job_posting_setup }}</p>
        <p>{{ $jobPost->job_posting_description }}</p>
        <p>{{ $jobPost->job_closing_date }}</p>
        @foreach ($jobPost->programs as $program)
            <p>{{ $program->program_name }}</p>
        @endforeach
        
        <img src="{{ asset("storage/" . $jobPost->job_posting_image) }}" alt="" class="w-[100px] h-[100px] object-cover">
    </div>

    <div>
        <h2>JOB APPLICANTS</h2>
        @foreach ($jobPost->applicants as $applicant)
            <p>{{ $applicant->user->user_first_name }} {{ $applicant->user->user_middle_name }} {{ $applicant->user->user_last_name }} {{ $applicant->user->user_suffix }} </p>
            <p>{{ $applicant->program->program_name }}</p>
            
            <a href="{{ asset("storage/" . $applicant->alumnus_resume) ?? '#' }}">View Resume</a>
            <p>{{ $applicant->pivot->application_status }}</p>
            <form action="{{ route('jobApplication.hireApplicant', $jobPost->job_posting_id) }}" method="POST">
                @csrf
                <button type="submit">Hire</button>
            </form>
            <form action="{{ route('jobApplication.declineApplicant', $jobPost->job_posting_id) }}" method="POST">
                @csrf
                <button type="submit">Decline</button>
            </form>
            <form action="{{ route('jobApplication.shortlistApplicant', $jobPost->job_posting_id) }}" method="POST">
                @csrf
                <button type="submit">Shortlist</button>
            </form>
        @endforeach
    </div>
    
</body>
</html>