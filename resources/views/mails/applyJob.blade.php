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
    <h1 class="text-2xl font-bold">Job Application</h1>
    <p>Hello, {{ $job->job->user->user_first_name }} {{ $job->job->user->user_last_name }}!</p>
    <p>An alumni ({{ $alumni->user->user_first_name }} {{ $alumni->user->user_last_name }}) applied for your post</p>
</body>
</html>