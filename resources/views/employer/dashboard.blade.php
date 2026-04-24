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
    <h1>Employer Dashboard</h1>
    <br><br>
    <a href="{{ route('user.profile') }}">View Profile</a>
    <br><br>
    <a href="{{ route('jobPosting.jobBoard') }}">Job board</a>
    <br><br>
    <a href="{{ route('jobPosting.myJobPosts', ['id' => Auth::id()]) }}">My job postings</a>
    <br><br>
    
    <form method="POST" action="{{ route('user.logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>