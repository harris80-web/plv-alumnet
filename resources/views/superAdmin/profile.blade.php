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
    <h1>SUPERADMIN PROFILE</h1>
    <div>
        <div>
            <h5>LAST NAME: {{ $user->last_name }}</h5>
        </div>
        <div>
            <h5>ADDRESS: {{ $user->office->office_address }}</h5>
        </div>
        <div>
            <h5>LAST NAME: {{ $user->last_name }}</h5>
        </div>
    </div>
</body>
</html>