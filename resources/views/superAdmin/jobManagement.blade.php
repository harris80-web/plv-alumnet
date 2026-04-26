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
    <div class="flex flex-col w-full items-center">
        <h1>Job Management</h1>
        <br><br><br>
        @foreach($jobPostings as $job)
        @if ($job->job_approved == 0)


        <div class="flex w-[80vw]">
            <h2>{{ $job->job_posting_title }}</h2>
            <p><strong>Company:</strong> {{ $job->job_posting_company }}</p>
            <p><strong>Location:</strong> {{ $job->job_posting_address }}</p>
            <p><strong>Posted by:</strong> {{ $job->employer->user->user_first_name }} {{ $job->employer->user->user_last_name }}</p>
            <p><strong>Job type:</strong> {{ $job->job_posting_employment_type }}</p>
            <p><strong>Job setup:</strong> {{ $job->job_posting_setup }}</p>
            <p><strong>Recommended program:</strong>
                @foreach ($job->programs as $program)
                {{ $program->program_name }}
                <br><br>
                @endforeach
            </p>
            <p><strong>Description:</strong> {{ $job->job_posting_description }}</p>
            <p><strong>Valid until:</strong> {{ $job->job_closing_date }}</p>

            <br><br><br>
            <form action="{{ route('jobPosting.approve', $job->job_posting_id) }}" method="POST">
                @csrf
                <button type="submit">
                    Approve
                </button>
            </form>
        </div>
        @endif
        @endforeach
        <br><br><br>

        <br><br><br>


</body>

</html>