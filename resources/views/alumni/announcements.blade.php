<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Announcements</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSectionBackground.png') }}");
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

    .notice-description-content img {
        max-width: 100%;
        height: auto;
    }
</style>

<body>
    @php $current_page = Route::currentRouteName(); @endphp
    @include($user->user_role === 'employer' ? 'partials.header-employer' : 'partials.header-alumni')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Campus Announcements</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    @include('partials.success')

    <main class="max-w-6xl mx-auto p-6 pb-16">

        @php $announcementsRoute = $user->user_role === 'employer' ? 'notices.employerAnnouncements' : 'notices.announcements'; @endphp
        <!-- SEARCH & FILTER -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 mb-8">
            <form method="GET" action="{{ route($announcementsRoute) }}" class="flex flex-col md:flex-row gap-4">

                <div class="relative flex-1">
                    <button type="submit" class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 hover:text-[#C73D1A]">
                        <i class="fas fa-search"></i>
                    </button>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by title" class="w-full pl-11 pr-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                </div>

                <div class="relative md:w-56 shrink-0">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    <select name="date_posted" onchange="this.form.submit()" class="w-full pl-11 pr-10 py-2 border rounded-full bg-white appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                        <option value="">Date Posted</option>
                        <option value="24h" {{ ($filters['date_posted'] ?? '') === '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                        <option value="7d" {{ ($filters['date_posted'] ?? '') === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30d" {{ ($filters['date_posted'] ?? '') === '30d' ? 'selected' : '' }}>Last 30 Days</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </span>
                </div>

            </form>
            @if (array_filter($filters))
            <div class="mt-3 text-right">
                <a href="{{ route($announcementsRoute) }}" class="text-xs font-bold text-gray-400 hover:text-[#C73D1A]">
                    <i class="fas fa-times mr-1"></i>CLEAR FILTERS
                </a>
            </div>
            @endif
        </div>

        @if ($notices->isEmpty())
        <div class="bg-white rounded-2xl shadow-md p-16 text-center text-gray-400">
            <i class="fa-regular fa-bell-slash text-4xl mb-3 block text-[#ED7A07]"></i>
            <p class="font-semibold">No announcements to show right now.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($notices as $notice)
            <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 flex flex-col h-full transition-transform hover:scale-[1.01] cursor-pointer"
                onclick="openNoticeDetailModal(this)"
                data-notice-id="{{ $notice->id }}"
                data-category="{{ $notice->category }}"
                data-title="{{ $notice->title }}"
                data-thumbnail="{{ $notice->thumbnailUrl() }}"
                data-datetime="{{ $notice->event_datetime->format('M d, Y - h:i A') }}"
                data-location="{{ $notice->location }}"
                data-description="{{ $notice->description }}">
                <div class="relative h-40 bg-cover bg-center shrink-0" style="background-image:url('{{ $notice->thumbnailUrl() }}')">
                    <div class="absolute inset-0 bg-[#0E0F3B]/25"></div>
                    <span class="absolute top-3 left-3 text-[9px] font-bold uppercase px-2 py-1 rounded-full {{ $notice->categoryBadgeClass() }}">
                        {{ $notice->categoryLabel() }}
                    </span>
                </div>

                <div class="p-5 flex flex-col flex-grow">
                    <h3 class="font-bold text-[#0E0F3B] text-lg mb-1 leading-snug">{{ $notice->title }}</h3>

                    <p class="text-xs text-gray-500 mb-3 flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar"></i> {{ $notice->event_datetime->format('M d, Y - h:i A') }}
                    </p>

                    <div class="relative max-h-[6rem] overflow-hidden flex-grow">
                        <div class="text-xs text-gray-600 leading-relaxed notice-description-content">
                            {!! $notice->description ?? '<p class="text-gray-400">No description provided.</p>' !!}
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-6 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{ $notices->onEachSide(1)->links('partials.pagination') }}
        @endif

    </main>

    @include('partials.notice-detail-modal')

    <script>
        // Deep-link from the dashboard's "Announcements" cards (?notice=123)
        // — auto-opens that notice's detail modal once its card is on the
        // page. No-ops silently if the id is missing or its card isn't on
        // the current page (e.g. it's since been pushed past page 1).
        window.addEventListener('DOMContentLoaded', function () {
            const openNoticeId = @json($openNoticeId);
            if (!openNoticeId) return;
            const card = document.querySelector('[data-notice-id="' + openNoticeId + '"]');
            if (card) {
                openNoticeDetailModal(card);
            }
        });
    </script>

    @include($user->user_role === 'employer' ? 'partials.footer-employer' : 'partials.footer-alumni')
</body>

</html>
