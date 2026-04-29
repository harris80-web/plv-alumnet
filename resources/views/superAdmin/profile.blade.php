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
        
        <div class="flex">
            <img src="{{ asset('storage/' . $user->user_profile_picture) ?? "No image set" }}" alt="Profile Picture" class="h-[100px] w-auto">
            <div class="flex flex-col">
                <h5>{{ $user->user_first_name }} {{ $user->user_middle_name }} {{ $user->user_last_name }} {{ $user->user_suffix }}</h5>
                <br>
                <h4>{{ $user->user_role }}</h4>
                <br>
                <h4>{{ $user->office->office_address }}</h4>
            </div>
        </div>
        <div>
            <h2>PERSONAL INFORMATION</h2>
            <div>
                <h5>FIRST NAME: {{ $user->user_first_name }}</h5>
            </div>
            <div>
                <h5>MIDDLE NAME: {{ $user->user_middle_name }}</h5>
            </div>
            <div>
                <h5>LAST NAME: {{ $user->user_last_name }}</h5>
            </div>
            <div>
                <h5>SUFFIX: {{ $user->user_suffix }}</h5>
            </div>
            <div>
                <h5>BIRTH DATE: {{ $user->office->office_birth_date }}</h5>
            </div>
            <div>
                <h5>EMAIL: {{ $user->user_email }}</h5>
            </div>
            <div>
                <h5>NUMBER: {{ $user->user_number }}</h5>
            </div>
            <div>
                <h5>ADDRESS: {{ $user->office->office_address }}</h5>
            </div>
        </div>
        <div>
            <h2>SYSTEM & SECURITY</h2>
            <div>
                <h5>USER ROLE: {{ $user->user_role }}</h5>
            </div>
            <div>
                <h5>STATUS: {{ $user->user_active ? 'Active' : 'Inactive' }}</h5>
            </div>
            <div>
                <h5>DATE CREATED: {{ $user->created_at }}</h5>
            </div>
        </div>
        <a href="{{ route('users.editProfile') }}">Edit Profile</a>
</body>

</html>