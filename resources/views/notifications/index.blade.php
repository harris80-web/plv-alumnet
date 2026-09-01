<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Notifications</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<style>
    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
    }

    html,
    body {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<body class="bg-gray-50 min-h-screen flex flex-col">
    @include($user->user_role === 'employer' ? 'partials.header-employer' : 'partials.header-alumni')

    @include('partials.success')

    <main class="max-w-3xl mx-auto p-6 w-full flex-1">
        <h2 class="text-2xl font-bold text-[#0E0F3B] uppercase tracking-tight mb-8 mt-4">Notifications</h2>

        @if ($notifications->isEmpty())
        <div class="bg-white rounded-2xl shadow-md p-16 text-center text-gray-400">
            <i class="fa-regular fa-bell-slash text-4xl mb-3 block"></i>
            <p class="font-semibold">No notifications yet.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach ($notifications as $notification)
            @php
                $unread = $notification->read_at === null;
                $url = $notification->targetUrl();
            @endphp
            <{{ $url ? 'a' : 'div' }} @if ($url) href="{{ $url }}" @endif
                class="flex items-start gap-4 bg-white rounded-xl shadow-sm border p-4 transition-shadow {{ $url ? 'hover:shadow-md cursor-pointer' : '' }} {{ $unread ? 'border-l-4 border-l-[#C73D1A] bg-orange-50/40' : 'border-gray-100' }}">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-triangle-exclamation text-[#C73D1A] text-sm"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-[#0E0F3B]">{{ $notification->title }}</p>
                    <p class="text-sm text-gray-500 mt-1 break-words">{{ $notification->body }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                @if ($unread)
                <span class="w-2.5 h-2.5 rounded-full bg-[#C73D1A] shrink-0 mt-1.5"></span>
                @endif
            </{{ $url ? 'a' : 'div' }}>
            @endforeach
        </div>

        {{ $notifications->onEachSide(1)->links('partials.pagination') }}
        @endif
    </main>

    @include($user->user_role === 'employer' ? 'partials.footer-employer' : 'partials.footer-alumni')
</body>

</html>
