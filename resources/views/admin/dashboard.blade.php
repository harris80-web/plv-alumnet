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
    <h1>Admin Dashboard</h1>
    <br><br>
    <a href="{{ route('user.profile') }}">View Profile</a>
    <br>
    <form method="POST" action="{{ route('user.logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>