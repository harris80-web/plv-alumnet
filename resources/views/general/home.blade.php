<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/PLV-AlumNet LOGO.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<style>
    .HeroSection {
        background:
            url('assets/heroSection.svg');
        background-size: cover;
        background-position: center;
    }

    .AlumniServices {
        background:
            url('assets/Landing Page/alumniServices.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .AlumniTestimonial {
        background:
            url('assets/alumniTestimonial.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
    }


    html,
    body {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
        overflow-y: scroll;

    }
</style>

<body>
    @php
    $current_page = 'index';
    @endphp
    @include('partials.header-general')
    @include('partials.success')

    @include('partials.error')
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-4">
            <h1 class="text-5xl font-bold">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <section class="py-16 px-6 max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center justify-items-center">
        <div class="rounded-lg overflow-hidden shadow-xl w-full">
            <img src="{{ asset('assets/Landing Page/graduationImage.png') }}" alt="Graduation" class="w-full h-full object-cover">
        </div>

        <div class="flex flex-col items-center text-center">
            <h2 class="text-4xl font-bold text-[#0E0F3B] mb-6 leading-tight">
                Your Journey has<br>just begun
            </h2>
            <p class="text-black leading-relaxed mb-8">
                <span class="font-bold text-[#0E0F3B]">PLV-AlumNet</span> is the essential digital platform that elevates the connection between all PLV alumni. We function as a dynamic ecosystem, not just a directory, actively working to bridge opportunities, empower professional success, and inspire mentorship across all generations of graduates.
            </p>
            <a href="{{ route('general.about') }}" class="px-8 py-2 rounded-md border-2 border-[#0E0F3B] text-[#0E0F3B] font-bold hover:bg-[#0E0F3B] hover:text-white transition-colors duration-300 uppercase text-sm tracking-widest">
                View More
            </a>
        </div>
    </section>

    <section class="AlumniServices orange-gradient py-16 px-6 text-white text-center">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-4xl font-bold mb-4 uppercase">Alumni Services</h2>
            <p class="items-center text-justify mb-12 text-sm w-3/5 mx-auto">
                This section details the exclusive resources, support, and programs available to all graduates of <span class="font-bold">Pamantasan ng Lungsod ng Valenzuela (PLV)</span>. It typically includes services such as career assistance, networking events, information for claiming of alumni IDs and Yearbook, and access to campus facilities. The goal is to keep alumni connected to the university and to foster mutual support among the network's members.
            </p>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="group bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-users text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase transition-colors group-hover:text-[#C73D1A] group-hover:font-extrabold">Community Updates</span>
                </div>


                <div class="group bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-address-book text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase transition-colors group-hover:text-[#C73D1A] group-hover:font-extrabold">Alumni Directory</span>
                </div>


                <div class="group bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-briefcase text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase transition-colors group-hover:text-[#C73D1A] group-hover:font-extrabold">Job Board</span>
                </div>


                <div class="group bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-comments text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase transition-colors group-hover:text-[#C73D1A] group-hover:font-extrabold">Network Connect</span>
                </div>


                <div class="group bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-id-card text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase transition-colors group-hover:text-[#C73D1A] group-hover:font-extrabold">Alumni ID &amp; Yearbook Claiming Status</span>
                </div>


            </div>
        </div>
    </section>

    <section class="py-12 sm:py-16 px-4 sm:px-6 max-w-6xl mx-auto relative">
        <div class="flex flex-wrap items-end justify-between gap-x-4 gap-y-2 mb-4 sm:mb-6 sm:pl-4">
            <h2 class="inner-text-shadow text-2xl sm:text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent uppercase tracking-tighter">| Campus Events</h2>
            <a href="{{ route('notices.guestEventsSeminars') }}" class="font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent uppercase text-xs sm:text-sm hover:border-b-2 border-[#C73D1A]">Go to Events ></a>
        </div>

        @if ($campusEvents->isEmpty())
        {{-- Empty state: brand navy, not gray. --}}
        <div class="bg-white rounded-2xl border border-slate-200 px-6 py-10 text-center">
            <span class="mx-auto w-14 h-14 rounded-2xl bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-regular fa-calendar-xmark text-2xl"></i></span>
            <p class="mt-3 text-base font-bold text-[#0E0F3B]">No upcoming events to show right now.</p>
            <p class="mt-1 text-sm text-[#1D264F]">New campus events will show up here as soon as they're posted.</p>
        </div>
        @else
        {{-- Same card + arrow design as the Alumni and Employer dashboard carousels. --}}
        <div class="relative sm:px-12">
            <button id="eventsCarouselPrev" type="button" aria-label="Previous events"
                class="hidden md:flex absolute left-0 top-16 sm:top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white border border-slate-200 shadow-md text-[#1D264F] items-center justify-center hover:bg-[#1D264F] hover:text-white transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                <i class="fa-solid fa-chevron-left text-sm"></i>
            </button>

            {{-- Not .carousel-track: that class carries the shared phone rules that shrink card
                 text (12px titles, 10px buttons). Scrollbar hidden by .dash-carousel-track below. --}}
            <div id="eventsCarouselTrack" class="dash-carousel-track flex overflow-x-auto snap-x snap-mandatory scroll-smooth gap-4 sm:gap-6">
                @foreach ($campusEvents as $event)
                @php $overlay = $event->defaultThumbnailOverlay(); @endphp
                <div class="snap-start shrink-0 w-[85%] sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]" data-event-card>
                    {{-- The whole card opens the event details popup (partials.notice-detail-modal),
                         which reads these data-* attributes. --}}
                    <article class="group/card h-full bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col cursor-pointer transition-colors duration-200 hover:border-[#C73D1A]/40"
                        role="button" tabindex="0" aria-label="View event details"
                        onclick="openNoticeDetailModal(this)"
                        onkeydown="if(event.target===this&&(event.key==='Enter'||event.key===' ')){event.preventDefault();openNoticeDetailModal(this);}"
                        data-notice-id="{{ $event->id }}"
                        data-category="{{ $event->category }}"
                        data-title="{{ $event->title }}"
                        data-thumbnail="{{ $event->thumbnailUrl() }}"
                        data-datetime="{{ $event->event_datetime->format('M d, Y - h:i A') }}"
                        data-location="{{ $event->location }}"
                        data-speaker-name="{{ $event->speaker_name }}"
                        data-speaker-topic="{{ $event->speaker_topic }}"
                        data-description="{{ $event->description }}"
                        data-guest="1"
                        @if ($event->usesDefaultThumbnail())
                        data-uses-default="1"
                        data-overlay-color="{{ $overlay['color'] }}"
                        data-overlay-opacity="{{ $overlay['overlayOpacity'] }}"
                        data-image-opacity="{{ $overlay['imageOpacity'] }}"
                        @endif>
                        <div class="relative h-32 sm:h-36 shrink-0">
                            <img src="{{ $event->thumbnailUrl() }}" alt="" class="w-full h-full object-cover mix-blend-multiply"
                                @if ($event->usesDefaultThumbnail())
                                style="opacity:{{ $overlay['imageOpacity'] }}"
                                @endif>
                            @if ($event->usesDefaultThumbnail())
                            <div class="absolute inset-0" style="background-color:{{ $overlay['color'] }}; opacity:{{ $overlay['overlayOpacity'] }}"></div>
                            @endif
                            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 bg-white/95 text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full shadow-sm" style="color:{{ $overlay['color'] }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background-color:{{ $overlay['color'] }}"></span> {{ $event->categoryLabel() }}
                            </span>
                            {{-- Date tile, in the category's color --}}
                            <span class="absolute top-3 right-3 w-12 rounded-lg text-white text-center py-1.5 shadow-md" style="background-color:{{ $overlay['color'] }}">
                                <span class="block text-lg font-bold leading-none">{{ $event->event_datetime->format('d') }}</span>
                                <span class="block mt-0.5 text-[10px] font-semibold uppercase">{{ $event->event_datetime->format('M') }}</span>
                            </span>
                        </div>

                        <div class="p-4 sm:p-5 flex flex-col flex-1">
                            {{-- Always exactly two lines tall, so every card's details line up. --}}
                            <h3 class="font-bold text-[#0E0F3B] uppercase text-sm md:text-[15px] leading-snug line-clamp-2 h-[2.75em] break-words transition-colors group-hover/card:text-[#C73D1A] group-hover/card:font-extrabold" title="{{ $event->title }}">{{ $event->title }}</h3>

                            <ul class="mt-2 space-y-1 text-xs text-slate-500">
                                <li class="flex items-center gap-2">
                                    <i class="fa-regular fa-clock w-3.5 text-center text-slate-400"></i>
                                    {{ $event->event_datetime->format('D, M d, Y · g:i A') }}
                                </li>
                                <li class="flex items-center gap-2 min-w-0">
                                    <i class="fa-solid fa-location-dot w-3.5 text-center text-slate-400"></i>
                                    <span class="truncate" title="{{ $event->location }}">{{ $event->location ?: 'Location to be announced' }}</span>
                                </li>
                            </ul>

                            <p class="mt-3 text-xs text-slate-600 leading-relaxed line-clamp-3">
                                {{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 160) ?: 'No description provided.' }}
                            </p>

                            <div class="mt-auto pt-4 sm:pt-5">
                                <a href="{{ route('auth.login') }}" onclick="event.stopPropagation()"
                                    class="flex items-center justify-center gap-2 w-full bg-[#1D46A4] hover:bg-[#0E0F3B] text-white py-2.5 rounded-lg font-bold text-xs tracking-wider uppercase transition-colors">
                                    <i class="fa-solid fa-lock text-[10px]"></i> Log In to View Full Details
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
                @endforeach
            </div>

            <button id="eventsCarouselNext" type="button" aria-label="Next events"
                class="hidden md:flex absolute right-0 top-16 sm:top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white border border-slate-200 shadow-md text-[#1D264F] items-center justify-center hover:bg-[#1D264F] hover:text-white transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                <i class="fa-solid fa-chevron-right text-sm"></i>
            </button>
        </div>

        <div id="eventsCarouselDots" class="flex justify-center gap-2 mt-6"></div>
        @endif
    </section>

    <style>
        /* Campus Events carousel: no visible scrollbar (swipe / arrows / dots instead). */
        .dash-carousel-track { scrollbar-width: none; -ms-overflow-style: none; }
        .dash-carousel-track::-webkit-scrollbar { display: none; }
    </style>

    @include('partials.carousel-init')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initCardCarousel({
                trackId: 'eventsCarouselTrack',
                prevId: 'eventsCarouselPrev',
                nextId: 'eventsCarouselNext',
                dotsId: 'eventsCarouselDots',
                itemsPerPage: 3,
            });
        });
    </script>

    <section class="py-4 px-6 max-w-6xl mx-auto relative pb-16">
        <div class="flex justify-between items-end mb-8 pl-4">
            <span class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent
            text-4xl font-bold text-blue-900 uppercase tracking-tighter"> | Announcements</span>
            <a href="{{ route('notices.guestAnnouncements') }}" class="font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent uppercase text-xs hover:border-b-2 border-[#C73D1A]">Go to Announcements ></a>
        </div>

        @if ($campusAnnouncements->isEmpty())
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-10 text-center text-gray-500">
            <i class="fa-solid fa-bullhorn text-3xl mb-3 block text-gray-300"></i>
            <p>No announcements right now.</p>
        </div>
        @else
        <div class="space-y-4">
            @foreach ($campusAnnouncements as $notice)
            <div class="flex items-center gap-4 bg-white shadow-md rounded-lg border border-gray-100 p-4 cursor-pointer"
                onclick="openNoticeDetailModal(this)"
                data-notice-id="{{ $notice->id }}"
                data-category="{{ $notice->category }}"
                data-title="{{ $notice->title }}"
                data-thumbnail="{{ $notice->thumbnailUrl() }}"
                data-datetime="{{ $notice->event_datetime->format('M d, Y - h:i A') }}"
                data-location="{{ $notice->location }}"
                data-description="{{ $notice->description }}"
                data-guest="1"
                @if ($notice->usesDefaultThumbnail())
                @php $overlay = $notice->defaultThumbnailOverlay(); @endphp
                data-uses-default="1"
                data-overlay-color="{{ $overlay['color'] }}"
                data-overlay-opacity="{{ $overlay['overlayOpacity'] }}"
                data-image-opacity="{{ $overlay['imageOpacity'] }}"
                @endif>
                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-bullhorn text-amber-600"></i>
                </div>
                <div class="flex-grow min-w-0">
                    <h3 class="font-bold text-blue-900 text-sm truncate">{{ $notice->title }}</h3>
                    <p class="text-xs text-gray-500 truncate">{{ \Illuminate\Support\Str::limit(strip_tags($notice->description ?? ''), 100) }}</p>
                </div>
                <p class="text-[10px] text-gray-400 shrink-0 whitespace-nowrap">{{ $notice->event_datetime->format('M d, Y') }}</p>
                <a href="{{ route('auth.login') }}" onclick="event.stopPropagation()" class="shrink-0 flex items-center gap-1.5 text-[10px] font-bold uppercase py-2 px-3 rounded-md bg-[#1D264F] hover:bg-[#0E0F3B] text-white transition-colors whitespace-nowrap">
                    <i class="fa-solid fa-lock"></i> Log In
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </section>

    <section id="alumni-testimonials" class="AlumniTestimonial py-14 sm:py-20 px-4 sm:px-6 text-white text-center mb-10">
        <h2 class="text-2xl sm:text-3xl font-bold uppercase tracking-widest">Alumni Testimonials</h2>
        <p class="mt-2 mb-8 sm:mb-12 text-sm text-white/70">What PLV graduates say about PLV-AlumNet.</p>

        <div id="testimonial-cards-wrap">
            @include('partials.testimonial-cards')
        </div>
    </section>
    @include('partials.testimonial-cards-script')

    @include('partials.notice-detail-modal')

    @include('partials.footer')

</body>

</html>