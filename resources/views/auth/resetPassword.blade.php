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
    <div>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @include('partials.success')
    </div>
    <h1>Reset Password</h1>
    <form method="POST" action="{{ route('passReset.updatePassword') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>

        <label for="user_password">New Password:</label>
        <input type="password" id="user_password" name="user_password" required>
        
        <label for="user_password_confirmation">Confirm New Password:</label>
        <input type="password" id="user_password_confirmation" name="user_password_confirmation" required>
        <button type="submit">Reset Password</button>
    </form>

</body>
</html>