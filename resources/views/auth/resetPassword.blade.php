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
        @include('partials.success')
    </div>
    <h1>Reset Password</h1>
    <form method="POST" action="{{ route('passReset.updatePassword') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        @error('email')
            <span class="my-custom-error font-semibold text-sm">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </span>
        @enderror

        <label for="user_password">New Password:</label>
        <input type="password" id="user_password" name="user_password" required>
        @error('user_password')
            <span class="my-custom-error font-semibold text-sm">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </span>
        @enderror

        <label for="user_password_confirmation">Confirm New Password:</label>
        <input type="password" id="user_password_confirmation" name="user_password_confirmation" required>
        @error('user_password_confirmation')
            <span class="my-custom-error font-semibold text-sm">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </span>
        @enderror
        <button type="submit">Reset Password</button>
    </form>

</body>

</html>