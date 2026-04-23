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
    <h1 class="text-2xl font-bold">Welcome to PLV-AlumNet!</h1>
    <p>Hello, {{ $user->user_full_name }}!</p>
    <p>An account has been created for you. You can now log in using the credentials below:</p>

    <ul class="flex">
        <li class="bg-sky-500 text-white p-2"><strong>Email:</strong> {{ $user->user_email }}</li>
        <li class="bg-red-500 text-white p-2"><strong>Password:</strong> {{ $password }}</li>
    </ul>

    <p><a href="{{ route('users.login') }}">Click here to login</a></p>
    <p>Please change your password immediately after logging in for security.</p>
</body>

</html>