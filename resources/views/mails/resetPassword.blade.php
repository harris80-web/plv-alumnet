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
    <h1 class="text-2xl font-bold">Reset password</h1>
    <p>Hello, {{ $user->user_full_name }}!</p>
    <p>You have requested to reset your password. You can do so by clicking the link below:</p>
    <p><a href="{{ route('passReset.resetPassword', ['token' => $token]) }}" class="text-blue-500">Reset Password</a></p>
    <p>If you did not request a password reset, please ignore this email.</p>
</body>

</html>