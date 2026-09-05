<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Job Board</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <link rel="preload" as="image" href="{{ asset('assets/heroSectionBackground.png') }}">
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

    #job-list {
        overflow: visible;
    }

    .tab-active {
        color: #C73D1A;
        border-bottom: 3px solid #C73D1A;
    }

    #postJobModal>div {
        overflow-y: auto;
        max-height: 90vh;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    #postJobModal>div::-webkit-scrollbar {
        display: none;
    }
</style>


<body>
    @php
    $current_page = Route::currentRouteName();
    $activeTab ??= 'board';
    $filters ??= [];
    $activeTabRoute = match ($activeTab) {
        'bookmarks' => route('jobPosting.bookmarks'),
        'applications' => route('jobPosting.myApplications'),
        default => route('jobPosting.jobBoard'),
    };
    @endphp
    @if (!$user)
    @include('partials.header-general')
    @elseif($user->user_role === 'alumni')
    @include('partials.header-alumni')
    @else
    @include('partials.header-employer')
    @endif

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">
                @if($activeTab === 'bookmarks')
                My Bookmarked Jobs
                @elseif($activeTab === 'applications')
                My Job Applications
                @else
                Job Board
                @endif
            </h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    @if($user && $user->user_role === 'alumni')
    <nav class="bg-white border-b sticky top-0 z-10 shadow-md">
        <div class="max-w-5xl mx-auto px-4">
            <div class="flex justify-start space-x-8 uppercase text-sm font-bold tracking-wide">
                <a href="{{ route('jobPosting.jobBoard') }}" class="py-4 transition-all {{ $activeTab === 'board' ? 'tab-active' : 'text-gray-500 hover:text-orange-600' }}">Job Board</a>
                <a href="{{ route('jobPosting.myApplications') }}" class="py-4 transition-all {{ $activeTab === 'applications' ? 'tab-active' : 'text-gray-500 hover:text-orange-600' }}">My Applications</a>
                <a href="{{ route('jobPosting.bookmarks') }}" class="py-4 transition-all {{ $activeTab === 'bookmarks' ? 'tab-active' : 'text-gray-500 hover:text-orange-600' }}">Bookmarks</a>
            </div>
        </div>
    </nav>
    @endif

    @include('partials.success')

    <main class="max-w-5xl mx-auto p-6">
        <div class="w-full text-center mb-8">
            <h1 class="inline-block text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                @if($activeTab === 'bookmarks')
                MY BOOKMARKS
                @elseif($activeTab === 'applications')
                MY APPLICATIONS
                @elseif($user && $user->user_role === 'alumni')
                ALUMNI CAREER HUB
                @else
                JOB BOARD
                @endif
            </h1>
        </div>

        <!-- SEARCH & FILTER -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 mb-8">
            <form method="GET" action="{{ $activeTabRoute }}">

                <div class="flex flex-col md:flex-row gap-4">
                    <div class="relative flex-1">
                        <button type="submit" class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 hover:text-[#C73D1A]">
                            <i class="fas fa-search"></i>
                        </button>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search job title or company" class="w-full pl-11 pr-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                    </div>

                    <button type="button" onclick="toggleMoreFilters()"
                        class="more-filters-toggle flex items-center justify-center gap-2 px-6 py-2 rounded-full border font-bold text-xs uppercase tracking-wide shrink-0 transition-colors {{ $moreFiltersActive ? 'bg-[#0E0F3B] text-white border-[#0E0F3B]' : 'border-gray-300 text-gray-600 hover:border-[#C73D1A] hover:text-[#C73D1A]' }}">
                        <i class="fas fa-sliders"></i>
                        <span>More Filter</span>
                        <i class="fas fa-chevron-down text-[10px] transition-transform more-filters-chevron {{ $moreFiltersActive ? 'rotate-180' : '' }}"></i>
                    </button>
                </div>

                <div id="moreFiltersPanel" class="{{ $moreFiltersActive ? '' : 'hidden' }} grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 mt-4 pt-4 border-t border-gray-100">

                    @include('partials.multiselect-filter', [
                        'name' => 'industry',
                        'icon' => 'fas fa-industry',
                        'placeholder' => 'Industry',
                        'options' => $industries->pluck('industry_name', 'industry_id'),
                        'selected' => $filters['industry'] ?? [],
                    ])

                    @include('partials.multiselect-filter', [
                        'name' => 'job_type',
                        'icon' => 'fas fa-briefcase',
                        'placeholder' => 'Job Type',
                        'options' => ['Full-Time' => 'Full-Time', 'Part-Time' => 'Part-Time', 'Freelance' => 'Freelance'],
                        'selected' => $filters['job_type'] ?? [],
                    ])

                    @include('partials.multiselect-filter', [
                        'name' => 'job_setup',
                        'icon' => 'fas fa-building',
                        'placeholder' => 'Job Setup',
                        'options' => ['On-Site' => 'On-Site', 'Remote' => 'Remote', 'Hybrid' => 'Hybrid'],
                        'selected' => $filters['job_setup'] ?? [],
                    ])

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none text-xs">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        <input type="text" name="location" value="{{ $filters['location'] ?? '' }}" placeholder="Location" class="w-full pl-11 pr-4 py-1.5 border rounded-full text-xs focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                    </div>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none text-xs">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <select name="date_posted" onchange="this.form.submit()" class="w-full pl-11 pr-10 py-1.5 border rounded-full bg-white text-xs appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                            <option value="">Date Posted</option>
                            <option value="24h" {{ ($filters['date_posted'] ?? '') === '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                            <option value="7d" {{ ($filters['date_posted'] ?? '') === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30d" {{ ($filters['date_posted'] ?? '') === '30d' ? 'selected' : '' }}>Last 30 Days</option>
                        </select>
                        <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </span>
                    </div>

                </div>
            </form>
            @if (array_filter($filters))
            <div class="mt-3 text-right">
                <a href="{{ $activeTabRoute }}" class="text-xs font-bold text-gray-400 hover:text-[#C73D1A]">
                    <i class="fas fa-times mr-1"></i>CLEAR FILTERS
                </a>
            </div>
            @endif
        </div>

        @if($user && $user->user_role === 'employer')
        <div class="flex justify-end p-4">
            <button
                onclick="openPostJobModal()"
                class="flex items-center gap-2 bg-[#1D264F] hover:bg-blue-900 text-white px-6 py-2.5 rounded-lg font-bold text-sm tracking-widest shadow-lg transition-all transform hover:scale-105 active:scale-95">
                <i class="fas fa-plus text-xs"></i>
                <span>POST A NEW JOB</span>
            </button>
        </div>
        @endif
        <div class="{{ session('noResume', 'hidden') }} bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <h3>You must have a resume in your profile to apply for jobs.</h3>
        </div>
        <div id="job-list" class="space-y-6">
            <!--JOB POST CONTAINER -->

            @forelse($jobPostings as $job)
            @include('partials.job-post-card', ['job' => $job])
            @empty
            <div class="bg-white rounded-3xl shadow-md p-12 text-center text-gray-500">
                @if($activeTab === 'bookmarks')
                <i class="far fa-bookmark text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold">You haven't bookmarked any jobs yet.</p>
                @elseif($activeTab === 'applications')
                <i class="fas fa-file-circle-check text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold">You haven't applied to any jobs yet.</p>
                @else
                <i class="fas fa-briefcase text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold">No job postings match your search.</p>
                @endif
            </div>
            @endforelse

        </div>

        {{ $jobPostings->onEachSide(1)->links('partials.pagination') }}
    </main>

    @include('partials.job-detail-modal')
    @include('partials.company-review-modal')

    @if($user && $user->user_role === 'alumni')
    @include('partials.job-apply-modal')
    @endif

    @if($user && $user->user_role === 'employer')
    @include('partials.post-job-modal', ['jobPoster' => $user])
    @endif

    @if(!$user)
    @include('partials.footer')
    @elseif($user->user_role === 'alumni')
    @include('partials.footer-alumni')
    @else
    @include('partials.footer-employer')
    @endif

</body>

<script>
    // Share Button Copy Logic
    function copyJobLink(button) {
        const dummyUrl = "https://alumnihub.example/jobs/12345";
        navigator.clipboard.writeText(dummyUrl).then(() => {
            const tooltip = button.nextElementSibling;
            tooltip.classList.remove('invisible', 'opacity-0');
            tooltip.classList.add('visible', 'opacity-100', '-translate-y-1');

            setTimeout(() => {
                tooltip.classList.add('invisible', 'opacity-0');
                tooltip.classList.remove('visible', 'opacity-100', '-translate-y-1');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }

    // "Post a New Job" modal JS (open/close, image preview, add/remove
    // program row, client validation, confirm/pending flow) now lives in
    // partials/post-job-modal.blade.php, shared with jobPostings.blade.php.

    // MORE FILTER PANEL
    function toggleMoreFilters() {
        document.getElementById('moreFiltersPanel').classList.toggle('hidden');
        document.querySelector('.more-filters-chevron').classList.toggle('rotate-180');
    }

    // MULTI-SELECT CHECKBOX FILTER DROPDOWNS (Industry / Job Type / Job Setup)
    function toggleMultiselect(btn) {
        const panel = btn.parentElement.querySelector('.multiselect-panel');
        document.querySelectorAll('.multiselect-panel').forEach(function (p) {
            if (p !== panel) p.classList.add('hidden');
        });
        panel.classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.multiselect-filter')) {
            document.querySelectorAll('.multiselect-panel').forEach(function (p) {
                p.classList.add('hidden');
            });
        }
    });

    function toggleMultiselectAll(selectAllCheckbox) {
        const panel = selectAllCheckbox.closest('.multiselect-panel');
        panel.querySelectorAll('.option-checkbox').forEach(function (o) {
            o.checked = selectAllCheckbox.checked;
        });
        selectAllCheckbox.closest('form').submit();
    }

    function onMultiselectOptionChange(checkbox) {
        updateSelectAllState(checkbox.closest('.multiselect-panel'));
        checkbox.closest('form').submit();
    }

    function updateSelectAllState(panel) {
        const options = Array.from(panel.querySelectorAll('.option-checkbox'));
        const selectAll = panel.querySelector('.select-all-checkbox');
        const selectAllCheck = panel.querySelector('.select-all-check');
        const checkedCount = options.filter(function (o) { return o.checked; }).length;

        selectAll.checked = options.length > 0 && checkedCount === options.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < options.length;
        selectAllCheck.classList.toggle('hidden', !selectAll.checked);
    }

    document.querySelectorAll('.multiselect-panel').forEach(updateSelectAllState);
</script>

</html>
