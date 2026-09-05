@php $current_page = 'notifications'; @endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-text {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        #sidebar:hover .sidebar-text {
            opacity: 1;
            pointer-events: auto;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        * {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">

        @include('partials.super-admin-side-bar')

        <main class="flex-1 flex flex-col overflow-hidden">

            @include('partials.super-admin-header')
            @include('partials.success')

            <div class="flex-1 overflow-y-auto p-8">
                <div class="max-w-3xl mx-auto">

                    <div class="flex items-center justify-between mb-6">
                        <div class="flex text-sm font-bold">
                            <a href="{{ route('notifications.all') }}"
                                class="px-4 py-2 border-b-2 transition-colors {{ $activeTab === 'all' ? 'border-[#ED7A07] text-[#ED7A07]' : 'border-transparent text-slate-400 hover:text-[#0E0F3B]' }}">
                                All
                            </a>
                            <a href="{{ route('notifications.all', ['tab' => 'unread']) }}"
                                class="px-4 py-2 border-b-2 transition-colors {{ $activeTab === 'unread' ? 'border-[#ED7A07] text-[#ED7A07]' : 'border-transparent text-slate-400 hover:text-[#0E0F3B]' }}">
                                Unread
                            </a>
                        </div>
                        <form action="{{ route('notifications.markAllRead') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-[#0E0F3B] hover:text-[#ED7A07] transition-colors uppercase">
                                Mark all as read
                            </button>
                        </form>
                    </div>

                    @if ($notifications->isEmpty())
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-16 text-center text-slate-400">
                        <i data-lucide="bell-off" class="w-10 h-10 mb-3 mx-auto"></i>
                        <p class="font-semibold">{{ $activeTab === 'unread' ? "You're all caught up." : 'No notifications yet.' }}</p>
                    </div>
                    @else
                    <div class="space-y-3">
                        @foreach ($notifications as $notification)
                        @php $unread = $notification->read_at === null; @endphp
                        <a href="{{ route('notifications.open', $notification) }}"
                            class="flex items-start gap-4 bg-white rounded-xl shadow-sm border p-4 transition-shadow hover:shadow-md cursor-pointer {{ $unread ? 'border-l-4 border-l-blue-600 bg-blue-50/60' : 'border-slate-200' }}">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="bell" class="w-4 h-4 text-[#ED7A07]"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-[#0E0F3B]">{{ $notification->title }}</p>
                                <p class="text-sm text-slate-500 mt-1 break-words">{{ $notification->body }}</p>
                                <p class="text-xs text-slate-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            @if ($unread)
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0 mt-1.5"></span>
                            @endif
                        </a>
                        @endforeach
                    </div>

                    {{ $notifications->onEachSide(1)->links('partials.pagination') }}
                    @endif

                </div>
            </div>

        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
