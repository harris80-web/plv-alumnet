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
            url('assets/alumni_testimonial.jpg');
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
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-4">
            <h1 class="text-5xl font-bold">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <section class="py-16 px-6 max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center justify-items-center">
        <div class="rounded-lg overflow-hidden shadow-xl w-full">
            <img src="assets/Landing Page/graduationImage.png" alt="Graduation" class="w-full h-full object-cover">
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
                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-users text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase">Community Updates</span>
                </div>


                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-address-book text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase">Alumni Directory</span>
                </div>


                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-briefcase text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase">Job Board</span>
                </div>


                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-comments text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase">Network Connect</span>
                </div>


                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-id-card text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase">Membership Status</span>
                </div>


            </div>
        </div>
    </section>

    <section class="py-16 px-6 max-w-6xl mx-auto relative">
        <div class="flex justify-between items-end mb-8 pl-4">
            <span class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent
            text-4xl font-bold text-blue-900 uppercase tracking-tighter"> | Campus Events</span>
            <a href="{{ route('notices.guestEventsSeminars') }}" class="font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent uppercase text-xs hover:border-b-2 border-[#C73D1A]">Go to Events ></a>
        </div>

        @if ($campusEvents->isEmpty())
        <div class="bg-white rounded-2xl shadow-md p-12 text-center text-gray-400">
            <i class="fa-regular fa-calendar-xmark text-4xl mb-3 block text-[#C73D1A]"></i>
            <p class="font-semibold">No upcoming events to show right now.</p>
        </div>
        @else
        <div class="relative">
            <button id="eventsCarouselPrev" type="button" aria-label="Previous events"
                class="hidden md:flex absolute -left-4 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white shadow-lg items-center justify-center text-[#0E0F3B] hover:bg-slate-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div id="eventsCarouselTrack" class="carousel-track flex overflow-x-auto snap-x snap-mandatory scroll-smooth gap-6 pb-2">
                @foreach ($campusEvents as $event)
                <div class="snap-start shrink-0 w-full md:w-[calc(33.333%-1rem)]">
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-100 flex flex-col h-full cursor-pointer"
                        onclick="openNoticeDetailModal(this)"
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
                        @php $overlay = $event->defaultThumbnailOverlay(); @endphp
                        data-uses-default="1"
                        data-overlay-color="{{ $overlay['color'] }}"
                        data-overlay-opacity="{{ $overlay['overlayOpacity'] }}"
                        data-image-opacity="{{ $overlay['imageOpacity'] }}"
                        @endif>
                        <div class="h-40 relative">
                            <img src="{{ $event->thumbnailUrl() }}" class="w-full h-full object-cover mix-blend-multiply"
                                @if ($event->usesDefaultThumbnail())
                                style="opacity:{{ $overlay['imageOpacity'] }}"
                                @endif>
                            @if ($event->usesDefaultThumbnail())
                            <div class="absolute inset-0" style="background-color:{{ $overlay['color'] }}; opacity:{{ $overlay['overlayOpacity'] }}"></div>
                            @endif
                        </div>

                        <div class="p-4 flex gap-4 items-start relative flex-grow">
                            <div class="flex-grow">
                                <h3 class="font-bold text-blue-900 uppercase text-sm">{{ $event->title }}</h3>
                                @if ($event->location)
                                <p class="text-[10px] text-gray-500 mb-2">
                                    <i class="fa-solid fa-location-dot mr-1"></i> {{ $event->location }}
                                </p>
                                @endif
                                <p class="text-xs text-black w-3/4 leading-tight">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 90) ?: 'No description provided.' }}
                                </p>
                            </div>
                            <div class="flex-shrink-0 absolute right-0 text-white p-2 text-center w-16 rounded-sm shadow-sm" style="background-color:{{ $event->defaultThumbnailOverlay()['color'] }}">
                                <span class="block text-xl font-bold leading-none tracking-tighter">{{ $event->event_datetime->format('d') }}</span>
                                <span class="text-[10px] uppercase font-semibold">{{ $event->event_datetime->format('M') }}</span>
                            </div>
                        </div>

                        <div class="px-4 pb-4">
                            <a href="{{ route('auth.login') }}" onclick="event.stopPropagation()" class="w-full flex items-center justify-center gap-1.5 text-[10px] font-bold uppercase py-2 rounded-md bg-[#1D264F] hover:bg-[#0E0F3B] text-white transition-colors">
                                <i class="fa-solid fa-lock"></i> Log In to View Full Details
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <button id="eventsCarouselNext" type="button" aria-label="Next events"
                class="hidden md:flex absolute -right-4 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white shadow-lg items-center justify-center text-[#0E0F3B] hover:bg-slate-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

        <div id="eventsCarouselDots" class="flex justify-center gap-2 mt-6"></div>
        @endif
    </section>

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

    <section class="py-4 px-6 max-w-6xl mx-auto relative pb-4">
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

    <section id="alumni-testimonials" class="AlumniTestimonial py-20 px-6 text-white text-center mb-10">
        <h2 class="text-3xl font-bold uppercase mb-12 tracking-widest">Alumni Testimonials</h2>

        <div id="testimonial-cards-wrap">
            @include('partials.testimonial-cards')
        </div>
    </section>
    @include('partials.testimonial-cards-script')

    @include('partials.notice-detail-modal')

    @include('partials.footer')

</body>

</html>