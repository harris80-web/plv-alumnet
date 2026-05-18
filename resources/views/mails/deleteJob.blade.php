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
    <h1 class="text-2xl font-bold">Job Deleted</h1>
    <p>Hello, {{ $user->user_first_name }} {{ $user->user_last_name }}!</p>
    <p>Your job post "{{ $job->job_posting_title }}" has been deleted for the following reason:</p>
    <p><strong>{{ $reason }}</strong></p>
</body>
</html>