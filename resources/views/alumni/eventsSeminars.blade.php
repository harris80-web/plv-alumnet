<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Events &amp; Seminars</title>
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

    .tab-active {
        color: #C73D1A;
        border-bottom: 3px solid #C73D1A;
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
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    @php $eventsSeminarsRoute = $user->user_role === 'employer' ? 'notices.employerEventsSeminars' : 'notices.eventsSeminars'; @endphp
    <nav class="bg-white border-b sticky top-0 z-10 shadow-md">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-start space-x-8 uppercase text-sm font-bold tracking-wide">
                <a href="{{ route($eventsSeminarsRoute, ['tab' => 'events']) }}" class="py-4 transition-all {{ $activeTab === 'events' ? 'tab-active' : 'text-gray-500 hover:text-orange-600' }}">Events</a>
                <a href="{{ route($eventsSeminarsRoute, ['tab' => 'seminar']) }}" class="py-4 transition-all {{ $activeTab === 'seminar' ? 'tab-active' : 'text-gray-500 hover:text-orange-600' }}">Seminars</a>
            </div>
        </div>
    </nav>

    @include('partials.success')

    <main class="max-w-6xl mx-auto p-6 pb-16">

        <h2 class="text-2xl font-bold text-[#0E0F3B] uppercase tracking-tight mb-8">
            {{ $activeTab === 'seminar' ? 'Upcoming Seminars' : 'Upcoming Events' }}
        </h2>

        @if ($notices->isEmpty())
        <div class="bg-white rounded-2xl shadow-md p-16 text-center text-gray-400">
            <i class="fa-regular fa-calendar-xmark text-4xl mb-3 block"></i>
            <p class="font-semibold">No {{ $activeTab === 'seminar' ? 'seminars' : 'events' }} to show right now.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($notices as $notice)
            @php $isInterested = in_array($notice->id, $interestedNoticeIds); @endphp
            <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 flex flex-col h-full transition-transform hover:scale-[1.01] cursor-pointer"
                onclick="openNoticeDetailModal(this)"
                data-notice-id="{{ $notice->id }}"
                data-category="{{ $notice->category }}"
                data-title="{{ $notice->title }}"
                data-thumbnail="{{ $notice->thumbnailUrl() }}"
                data-datetime="{{ $notice->event_datetime->format('M d, Y - h:i A') }}"
                data-location="{{ $notice->location }}"
                data-speaker-name="{{ $notice->speaker_name }}"
                data-speaker-topic="{{ $notice->speaker_topic }}"
                data-description="{{ $notice->description }}"
                @if ($user->user_role === 'alumni')
                data-interested="{{ $isInterested ? '1' : '0' }}"
                data-toggle-url="{{ route('notices.toggleInterest', $notice->id) }}"
                @endif>
                <div class="relative h-40 bg-cover bg-center shrink-0" style="background-image:url('{{ $notice->thumbnailUrl() }}')">
                    <div class="absolute inset-0 bg-[#0E0F3B]/25"></div>
                    <span class="absolute top-3 left-3 text-[9px] font-bold uppercase px-2 py-1 rounded-full {{ $notice->categoryBadgeClass() }}">
                        {{ $notice->categoryLabel() }}
                    </span>
                </div>

                <div class="p-5 flex flex-col flex-grow">
                    <h3 class="font-bold text-[#0E0F3B] text-lg mb-1 leading-snug">{{ $notice->title }}</h3>

                    <p class="text-xs text-gray-500 mb-1.5 flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar"></i> {{ $notice->event_datetime->format('M d, Y - h:i A') }}
                    </p>

                    @if($notice->category === 'seminar' && $notice->speaker_name)
                    <p class="text-xs text-gray-500 mb-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-microphone"></i> {{ $notice->speaker_name }} &mdash; {{ $notice->speaker_topic }}
                    </p>
                    @endif

                    @if($notice->location)
                    <p class="text-xs text-gray-500 mb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot"></i> {{ $notice->location }}
                    </p>
                    @endif

                    <div class="relative max-h-[4.5rem] overflow-hidden mb-4 flex-grow">
                        <div class="text-xs text-gray-600 leading-relaxed notice-description-content">
                            {!! $notice->description ?? '<p class="text-gray-400">No description provided.</p>' !!}
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-6 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                    </div>

                    @if ($user->user_role === 'alumni')
                    <div class="mt-auto pt-3 border-t border-gray-100">
                        <form class="interest-form" action="{{ route('notices.toggleInterest', $notice->id) }}" method="POST" onclick="event.stopPropagation()">
                            @csrf
                            <button type="submit"
                                class="w-full text-xs font-bold uppercase py-2.5 rounded-lg transition-colors {{ $isInterested ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-[#1D264F] hover:bg-[#0E0F3B] text-white' }}">
                                <i class="fa-solid {{ $isInterested ? 'fa-circle-check' : 'fa-star' }} mr-1"></i>
                                {{ $isInterested ? "You're Interested" : 'Interested' }}
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{ $notices->onEachSide(1)->links('partials.pagination') }}
        @endif

    </main>

    @include('partials.notice-detail-modal')

    <script>
        // Deep-link from the dashboard's "Campus Events" cards (?notice=123)
        // — auto-opens that notice's detail modal once its card is on the
        // page. No-ops silently if the id is missing or its card isn't on
        // the current page/tab (e.g. a stale link to an event that's since
        // passed and dropped off the upcoming list).
        window.addEventListener('DOMContentLoaded', function () {
            const openNoticeId = @json($openNoticeId);
            if (!openNoticeId) return;
            const card = document.querySelector('[data-notice-id="' + openNoticeId + '"]');
            if (card) {
                openNoticeDetailModal(card);
            }
        });
    </script>

    @if ($user->user_role === 'alumni')
    <script>
        // Card-grid "Interested" buttons — same submitInterestToggle() used
        // by the detail modal's own button (defined in
        // partials/notice-detail-modal.blade.php), so clicking either one
        // updates instantly instead of reloading the page.
        window.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.interest-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    submitInterestToggle(this, this.closest('[data-toggle-url]'));
                });
            });
        });
    </script>
    @endif

    @include($user->user_role === 'employer' ? 'partials.footer-employer' : 'partials.footer-alumni')
</body>

</html>
