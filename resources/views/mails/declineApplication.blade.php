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
    <h1 class="text-2xl font-bold">Job Declined</h1>
    <p>Hello, {{ $application->alumnus->user->user_first_name }} {{ $application->alumnus->user->user_last_name }}!</p>
    <p>You are declined for {{ $application->job->job_posting_title }}</p>
</body>
</html>