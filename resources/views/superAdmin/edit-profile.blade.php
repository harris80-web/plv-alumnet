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
    @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
    <form action="{{ route('offices.updateProfile', $user->user_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div>
            <img src="{{ asset('storage/' . $user->profile_picture) ?? "No image set" }}" alt="Profile Picture">
            <input type="file" name="user_profile_picture" id="profile_picture">
        </div>
        <div>
            <label for="user_first_name">First Name</label>
            <input type="text" id="user_first_name" name="user_first_name" value="{{ $user->user_first_name }}">
        </div>
        <div>
            <label for="user_middle_name">Middle Name</label>
            <input type="text" id="user_middle_name" name="user_middle_name" value="{{ $user->user_middle_name }}">
        </div>
        <div>
            <label for="user_last_name">Last Name</label>
            <input type="text" id="user_last_name" name="user_last_name" value="{{ $user->user_last_name }}">
        </div>
        <div>
            <label for="user_suffix">Suffix</label>
            <input type="text" id="user_suffix" name="user_suffix" value="{{ $user->user_suffix }}">
        </div>
        <div>
            <label for="office_birth_date">Birth Date</label>
            <input type="date" id="office_birth_date" name="office_birth_date" value="{{ $user->office->office_birth_date }}">
        </div>
        <div>
            <label for="user_email">Email</label>
            <input type="email" id="user_email" name="user_email" value="{{ $user->user_email }}">
        </div>
        <div>
            <label for="user_number">Number</label>
            <input type="text" id="user_number" name="user_number" value="{{ $user->user_number }}">
        </div>
        <div>
            <label for="office_address">Address</label>
            <input type="text" id="office_address" name="office_address" value="{{ $user->office->office_address }}">
        </div>
        <button type="submit">Update Profile</button>
    </form>
    
</body>
</html>