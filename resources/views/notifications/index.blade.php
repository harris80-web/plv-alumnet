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
    .HeroSection {
        background: url("{{ asset('assets/heroSection.svg') }}");
        background-size: cover;
        background-position: center;
    }

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

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Notifications</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    @include('partials.success')

    @include('partials.error')
    <main class="max-w-3xl mx-auto p-6 w-full flex-1">
        <div class="flex items-center justify-between border-b border-gray-200 mb-6 mt-4">
            <div class="flex text-sm font-bold">
                <a href="{{ route('notifications.all') }}"
                    class="px-4 py-2 border-b-2 transition-colors {{ $activeTab === 'all' ? 'border-[#C73D1A] text-[#C73D1A]' : 'border-transparent text-gray-400 hover:text-[#0E0F3B]' }}">
                    All
                </a>
                <a href="{{ route('notifications.all', ['tab' => 'unread']) }}"
                    class="px-4 py-2 border-b-2 transition-colors {{ $activeTab === 'unread' ? 'border-[#C73D1A] text-[#C73D1A]' : 'border-transparent text-gray-400 hover:text-[#0E0F3B]' }}">
                    Unread
                </a>
            </div>
            <form action="{{ route('notifications.markAllRead') }}" method="POST" class="pb-2">
                @csrf
                <button type="submit" class="text-xs font-bold text-[#0E0F3B] hover:text-[#C73D1A] transition-colors uppercase">
                    Mark all as read
                </button>
            </form>
        </div>

        @if ($notifications->isEmpty())
        <div class="bg-white rounded-2xl shadow-md p-16 text-center text-gray-400">
            <i class="fa-regular fa-bell-slash text-4xl mb-3 block"></i>
            <p class="font-semibold">{{ $activeTab === 'unread' ? "You're all caught up." : 'No notifications yet.' }}</p>
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-sm divide-y divide-gray-50 overflow-hidden">
            @foreach ($notifications as $notification)
            @php $unread = $notification->read_at === null; @endphp
            <a href="{{ route('notifications.open', $notification) }}"
                class="block p-4 transition-colors hover:bg-gray-50 {{ $unread ? 'bg-blue-50' : '' }}">
                <div class="flex items-start gap-2">
                    <span class="w-2 h-2 rounded-full shrink-0 mt-1.5 {{ $unread ? 'bg-blue-600' : '' }}"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-[#0E0F3B]">{{ $notification->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5 break-words">{{ $notification->body }}</p>
                        <p class="text-[10px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{ $notifications->onEachSide(1)->links('partials.pagination') }}
        @endif
    </main>

    @include($user->user_role === 'employer' ? 'partials.footer-employer' : 'partials.footer-alumni')
</body>

</html>
