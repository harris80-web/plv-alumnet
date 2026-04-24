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
    <h1>Job Board</h1>
    <div class="job-board">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        <br><br>
        <!-- gawin itong modal pop-up -->
        <div>
            <form action="{{ route('jobPosting.addJobPost', ['id' => $users->user_id]) }}" method="post" enctype="multipart/form-data">
                @csrf
                <div>
                    <label for="job_posting_image">Job photo:</label>
                    <input type="file" id="job_posting_image" name="job_posting_image">
                </div>
                <div>
                    <label for="job_posting_title">Job title:</label>
                    <input type="text" id="job_posting_title" name="job_posting_title">
                </div>
                <div>
                    <label for="job_posting_company">Business name:</label>
                    <input type="text" id="job_posting_company" name="job_posting_company">
                </div>
                <div>
                    <label for="job_posting_address">Business address:</label>
                    <input type="text" id="job_posting_address" name="job_posting_address">
                </div>
                <div>
                    <label for="job_posting_employment_type">Job type:</label>
                    <input type="text" id="job_posting_employment_type" name="job_posting_employment_type">
                </div>
                <div>
                    <label for="job_posting_setup">Job setup:</label>
                    <input type="text" id="job_posting_setup" name="job_posting_setup">
                </div>
                <div>
                    <label for="program">Recommended program:</label>
                    <select name="program" id="">
                        <option value="" selected hidden>Select a program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->program_id }}">{{ $program->program_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="job_posting_description">Job description:</label>
                    <input type="text" id="job_posting_description" name="job_posting_description">
                </div>
                <div>
                    <label for="job_closing_date">Validity date:</label>
                    <input type="date" id="job_closing_date" name="job_closing_date">
                </div>
                <button type="submit">Add Job Posting</button>
            </form>
        </div>
        <br><br><br>
        @foreach($jobPostings as $job)
            <div class="job-posting">
                <h2>{{ $job->job_posting_title }}</h2>
                <p><strong>Company:</strong> {{ $job->job_posting_company }}</p>
                <p><strong>Location:</strong> {{ $job->job_posting_address }}</p>
                <p><strong>Posted by:</strong> {{ $job->employer->user->user_first_name }} {{ $job->employer->user->user_last_name }}</p>
                <p><strong>Job type:</strong> {{ $job->job_posting_employment_type }}</p>
                <p><strong>Job setup:</strong> {{ $job->job_posting_setup }}</p>
                <p><strong>Recommended program:</strong> {{ $job->program->program_name }}</p>
                <p><strong>Description:</strong> {{ $job->job_posting_description }}</p>
                <p><strong>Valid until:</strong> {{ $job->job_closing_date }}</p>
                <img src="{{ asset('storage/'.$job->job_posting_image) }}" alt="Job Image" style="max-width: 200px; max-height: 200px;">
            </div>
        @endforeach
    </div>
    
</body>
</html>