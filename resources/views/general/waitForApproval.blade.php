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
    <h1>Waiting for admin approval!</h1>
    <a href="{{ route('general.home') }}" class="text-black">Back to Home page</a>
</body>
</html>