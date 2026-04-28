<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body class="flex gap-[20px]">
    <div class="flex flex-col">
        <h1>Superadmin Dashboard</h1>
        <a href="{{ route('superAdmin.dashboard') }}">Dashboard</a>
        <br><br>
        <a href="{{ route('superAdmin.userManagement') }}">User Management</a>
        <br><br>
        <a href="{{ route('jobPosting.jobManagement') }}">Job Management</a>
        <br><br>
        <a href="">Alumni ID and yearbook management</a>
        <br><br>
        <a href="">Notice and events management</a>
        <br><br>
        <a href="">Chatbot and messaging management</a>
        <br><br>
        <a href="">Testimonial management</a>
        <br><br>
        <a href="">Manage faqs</a>
        <br><br>
        <a href="{{ route('user.profile') }}">View Profile</a>
        <br><br><br>
        <form method="POST" action="{{ route('user.logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
    <div>
        <h2>SYSTEM OVERVIEW</h2>
        <div class="flex">
            <div>
                <h3>Job Placement Rate</h3>
                <p>{{ $stats['jobPlacementRate'] }}</p>
            </div>
        </div>
    </div>

</body>

</html>