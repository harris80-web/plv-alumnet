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
    <h1 class="text-2xl font-bold">Job Approved</h1>
    <p>Hello, {{ $job->employer->user->user_first_name }} {{ $job->employer->user->user_last_name }}!</p>
    <p>Your job post is approved</p>
</body>
</html>