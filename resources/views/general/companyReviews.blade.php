<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | {{ $employer->employer_company_name }} Reviews</title>
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

    .filter-active {
        color: #ffffff;
    }
</style>

<body class="bg-gray-50">
    @if (!$user)
    @include('partials.header-general')
    @elseif($user->user_role === 'alumni')
    @include('partials.header-alumni')
    @else
    @include('partials.header-employer')
    @endif

    <main class="max-w-4xl mx-auto p-6">
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-[#C73D1A] transition-colors mb-6">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="bg-white rounded-3xl shadow-md p-8 mb-8">
            <div class="flex items-center gap-4">
                @if ($employer->employer_company_logo)
                <img src="{{ asset('storage/' . $employer->employer_company_logo) }}" class="w-16 h-16 rounded-2xl object-cover border">
                @else
                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-300">
                    <i class="fas fa-building text-2xl"></i>
                </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-[#0E0F3B]">{{ $employer->employer_company_name }}</h1>
                    <p class="text-sm text-gray-400">Alumni Reviews</p>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-6">
                <div class="flex items-center gap-2 text-green-600">
                    <i class="fas fa-thumbs-up text-xl"></i>
                    <span class="text-2xl font-bold">{{ $upvotes }}</span>
                    <span class="text-sm text-gray-400">Upvotes</span>
                </div>
                <div class="flex items-center gap-2 text-red-600">
                    <i class="fas fa-thumbs-down text-xl"></i>
                    <span class="text-2xl font-bold">{{ $downvotes }}</span>
                    <span class="text-sm text-gray-400">Downvotes</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 mb-6">
            <a href="{{ route('employerReviews.index', ['employer' => $employer->user_id, 'back' => $backUrl]) }}"
                class="px-5 py-2 rounded-full text-xs font-bold uppercase tracking-wide transition-colors {{ !$filter ? 'bg-[#1D46A4] filter-active' : 'bg-white text-gray-500 border hover:border-[#1D46A4]' }}">
                All ({{ $upvotes + $downvotes }})
            </a>
            <a href="{{ route('employerReviews.index', ['employer' => $employer->user_id, 'vote' => 'upvote', 'back' => $backUrl]) }}"
                class="px-5 py-2 rounded-full text-xs font-bold uppercase tracking-wide transition-colors {{ $filter === 'upvote' ? 'bg-green-600 filter-active' : 'bg-white text-gray-500 border hover:border-green-500' }}">
                <i class="fas fa-thumbs-up mr-1"></i> Upvotes ({{ $upvotes }})
            </a>
            <a href="{{ route('employerReviews.index', ['employer' => $employer->user_id, 'vote' => 'downvote', 'back' => $backUrl]) }}"
                class="px-5 py-2 rounded-full text-xs font-bold uppercase tracking-wide transition-colors {{ $filter === 'downvote' ? 'bg-red-600 filter-active' : 'bg-white text-gray-500 border hover:border-red-500' }}">
                <i class="fas fa-thumbs-down mr-1"></i> Downvotes ({{ $downvotes }})
            </a>
        </div>

        <div class="space-y-4">
            @forelse ($reviews as $review)
            <div class="bg-white rounded-2xl shadow-sm border p-5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(($review->alumnus->user->user_first_name ?? 'A') . ' ' . ($review->alumnus->user->user_last_name ?? '')) }}&background=random"
                            class="w-10 h-10 rounded-full">
                        <div>
                            <p class="font-bold text-sm text-[#0E0F3B]">
                                {{ $review->alumnus->user->user_first_name ?? 'Alumnus' }} {{ $review->alumnus->user->user_last_name ?? '' }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $review->alumnus->program->program_name ?? 'PLV Alumnus' }} &middot; {{ $review->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    <span class="flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full {{ $review->vote === 'upvote' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        <i class="fas fa-thumbs-{{ $review->vote === 'upvote' ? 'up' : 'down' }}"></i>
                        {{ ucfirst($review->vote) }}
                    </span>
                </div>
                @if ($review->review_body)
                <p class="text-sm text-gray-600 mt-4 leading-relaxed">{{ $review->review_body }}</p>
                @endif
            </div>
            @empty
            <div class="bg-white rounded-3xl shadow-md p-12 text-center text-gray-500">
                <i class="fas fa-comment-slash text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold">No reviews yet @if($filter) for this filter @endif.</p>
            </div>
            @endforelse
        </div>

        {{ $reviews->onEachSide(1)->links('partials.pagination') }}
    </main>

    @if(!$user)
    @include('partials.footer')
    @elseif($user->user_role === 'alumni')
    @include('partials.footer-alumni')
    @else
    @include('partials.footer-employer')
    @endif
</body>

</html>
