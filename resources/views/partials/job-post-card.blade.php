@php
    // extract(), not @include() — a Blade @include() gets its own isolated
    // scope, so anything it assigned wouldn't propagate back here. See
    // App\Services\JobCardDataBuilder's own doc comment for why this needs
    // to be a plain PHP class instead of a shared partial.
    extract(\App\Services\JobCardDataBuilder::build(
        $job,
        $user ?? null,
        $appliedJobs ?? null,
        $bookmarkedIds ?? [],
        (float) ($recommendedThreshold ?? 50)
    ));
    $companyLogoUrl = $employer?->employer_company_logo ? asset('storage/' . $employer->employer_company_logo) : null;
    // No logo yet → the company's initials on the system's navy gradient
    // ("Copperline Holdings" → "CH"; corporate suffixes like Inc./Corp. skipped).
    $companyWords = array_values(array_filter(
        preg_split('/\s+/', trim(preg_replace('/[^\pL\pN\s]/u', ' ', (string) $job->job_posting_company))),
        fn ($w) => $w !== '' && !in_array(mb_strtolower($w), ['inc', 'corp', 'co', 'ltd', 'llc', 'the', 'of', 'and'], true)
    ));
    $companyInitials = mb_strtoupper(count($companyWords) > 1
        ? mb_substr($companyWords[0], 0, 1) . mb_substr($companyWords[1], 0, 1)
        : mb_substr($companyWords[0] ?? 'PLV', 0, 2));
@endphp

{{-- Desktop (>= 1024px): a compact card — company logo, job title, company, location,
     work setup and work type, in the system's own look (navy/orange tags, gradient title
     on hover, the job's image with the dashboards' round arrow). Clicking it shows everything else (description, apply,
     bookmark, share, votes, rating, reviews, status) in the Job Board's side panel. --}}
<div class="hidden lg:flex jb-list-card group/card relative items-stretch bg-white rounded-2xl shadow-md overflow-hidden cursor-pointer transition-shadow hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C73D1A]"
    role="button" tabindex="0" aria-label="View details: {{ $job->job_posting_title }}"
    @foreach ($cardData as $attr => $value)
    data-{{ $attr }}="{{ $value }}"
    @endforeach
    onclick="openJobModal(this)"
    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openJobModal(this);}">
    <div class="flex-1 min-w-0 flex items-center gap-3 xl:gap-4 py-4 pl-4 xl:pl-5 pr-3 xl:pr-4">
        @if ($companyLogoUrl)
        <div class="w-12 h-12 xl:w-14 xl:h-14 shrink-0 rounded-xl bg-white ring-1 ring-slate-200 overflow-hidden flex items-center justify-center">
            <img src="{{ $companyLogoUrl }}" alt="" loading="lazy" class="w-full h-full object-contain">
        </div>
        @else
        <div class="jb-logo-initials w-12 h-12 xl:w-14 xl:h-14 shrink-0 rounded-xl bg-gradient-to-br from-[#0E0F3B] to-[#1D46A4] text-white text-lg font-extrabold tracking-wide flex items-center justify-center shadow-sm" aria-hidden="true">{{ $companyInitials }}</div>
        @endif

        <div class="min-w-0 flex-1">
            {{-- Hover / selected: the title fills with the system gradient (see .jb-list-title-text in general/jobBoard). --}}
            <h2 class="jb-list-title font-bold uppercase text-[15px] leading-snug tracking-tight text-[#0E0F3B] line-clamp-2 break-words"><span class="jb-list-title-text">{{ $job->job_posting_title }}</span></h2>
            <p class="mt-0.5 text-sm font-semibold text-[#1D46A4] truncate">{{ $job->job_posting_company }}</p>
            <p class="mt-1 flex items-center gap-1.5 min-w-0 text-xs text-slate-500">
                <i class="fa-solid fa-location-dot text-[11px] text-[#ED7A07] shrink-0"></i>
                <span class="truncate">{{ $job->job_posting_address }}</span>
            </p>
            <div class="mt-2.5 flex flex-wrap gap-1.5">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#0E0F3B] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white" title="Work setup">
                    <i class="fa-solid fa-house-laptop text-[9px]"></i><span class="sr-only">Work setup:</span> {{ $job->job_posting_setup }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-[#ED7A07] bg-[#ED7A07]/5 px-2.5 py-[3px] text-[10px] font-bold uppercase tracking-wider text-[#C73D1A]" title="Work type">
                    <i class="fa-solid fa-briefcase text-[9px]"></i><span class="sr-only">Work type:</span> {{ $job->job_posting_employment_type }}
                </span>
            </div>
        </div>

    </div>

    {{-- The job's image on the right — its own photo, or the system's default job artwork —
         with the same tint as the full card / details panel, and the dashboards' round
         "View Details" arrow on top. --}}
    @php $thumbOverlay = $job->defaultThumbnailOverlay(); @endphp
    <div class="jb-list-thumb relative w-20 xl:w-32 shrink-0 overflow-hidden bg-[#0E0F3B]" aria-hidden="true">
        @if ($job->usesDefaultThumbnail())
        <img src="{{ $job->thumbnailUrl() }}" alt="" loading="lazy" style="opacity:{{ $thumbOverlay['imageOpacity'] }}"
            class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover/card:scale-105">
        <div class="absolute inset-0" style="background-color:{{ $thumbOverlay['color'] }}; opacity:{{ $thumbOverlay['overlayOpacity'] }}"></div>
        @else
        <img src="{{ $job->thumbnailUrl() }}" alt="" loading="lazy"
            class="absolute inset-0 w-full h-full object-cover opacity-80 transition-transform duration-300 group-hover/card:scale-105">
        <div class="absolute inset-0 bg-[#0E0F3B]/50"></div>
        @endif
        <span class="jb-list-arrow absolute inset-0 m-auto w-9 h-9 rounded-full bg-white/95 text-[#C73D1A] flex items-center justify-center shadow-md transition-colors group-hover/card:bg-[#C73D1A] group-hover/card:text-white">
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </span>
    </div>
</div>

{{-- Phones/tablets: the full card (its details open in the modal). --}}
<div class="lg:hidden bg-white rounded-3xl shadow-md flex flex-col md:flex-row relative hover:shadow-lg transition-shadow md:min-h-[340px] rp-job-card">

    <div class="md:w-1/4 h-48 md:h-auto bg-gray-300 relative rounded-t-3xl md:rounded-l-3xl md:rounded-tr-none overflow-hidden group cursor-pointer"
        role="button" tabindex="0" aria-label="View job details"
        @foreach ($cardData as $attr => $value)
        data-{{ $attr }}="{{ $value }}"
        @endforeach
        onclick="openJobModal(this)"
        onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openJobModal(this);}">
        @if (!$job->usesDefaultThumbnail())
        <img src="{{ $job->thumbnailUrl() }}"
            class="object-cover w-full h-full opacity-60 group-hover:opacity-80 group-hover:scale-105 transition-all duration-300">
        <div class="absolute inset-0 bg-[#0E0F3B]/60"></div>
        @else
        @php $overlay = $job->defaultThumbnailOverlay(); @endphp
        <img src="{{ $job->thumbnailUrl() }}" style="opacity:{{ $overlay['imageOpacity'] }}"
            class="object-cover w-full h-full group-hover:scale-105 transition-all duration-300">
        <div class="absolute inset-0" style="background-color:{{ $overlay['color'] }}; opacity:{{ $overlay['overlayOpacity'] }}"></div>
        @endif
        @if ($isRecommended)
        <span class="absolute top-2 left-2 z-10 bg-[#ED7A07] text-white text-[10px] font-bold uppercase px-3 py-1 rounded-full shadow-md">
            <i class="fas fa-star mr-1"></i> Recommended
        </span>
        @endif
        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
            <span class="bg-white/90 text-[#1D264F] text-[11px] font-bold px-3 py-1.5 rounded-full shadow-lg">
                <i class="fas fa-eye mr-1"></i> VIEW DETAILS
            </span>
        </div>
    </div>

    <div class="p-6 flex-1 relative rp-job-body">
        <div class="flex justify-between items-start rp-job-head">
            <div>
                <h2 class="text-2xl font-bold uppercase text-[#0E0F3B]">
                    {{ $job->job_posting_title }}
                </h2>

                <p class="text-gray-600">
                    {{ $job->job_posting_company }}
                </p>

                @if ($employer && !$isEmployer)
                <a href="{{ route('employerReviews.index', ['employer' => $employer->user_id, 'back' => url()->full()]) }}" class="reviews-link text-xs font-bold text-[#1D46A4] hover:underline inline-flex items-center gap-1.5 mt-0.5" data-employer-id="{{ $employer->user_id }}">
                    <i class="fas fa-comment-dots"></i> Reviews (<span class="reviews-count">{{ $companyRatingCount }}</span>)
                </a>
                @endif

                <p class="text-gray-500 text-sm">
                    {{ $job->job_posting_address }}
                </p>

                @if ($employer?->user?->user_number)
                <p class="text-gray-500 text-sm flex items-center gap-1.5">
                    <i class="fas fa-phone text-xs"></i> {{ $employer->user->user_number }}
                </p>
                @endif

            </div>

            <div class="text-right">
                <p class="text-xs text-gray-400 flex items-center justify-end">
                    <i class="far fa-calendar-alt mr-1"></i> {{ $job->created_at->diffForHumans() }}
                </p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4 text-sm font-semibold rp-job-meta">
            <div>
                <p class="text-[#0E0F3B]">Job Type: <span class="font-normal">{{ $job->job_posting_employment_type }}</span></p>
                <p class="text-[#0E0F3B]">Job Setup: <span class="font-normal">{{ $job->job_posting_setup }}</span></p>
            </div>

            <div>
                <p>
                <p class="text-blue-900">Recommended Course/Program: <span class="font-normal ph"></span></p>
                @foreach ($job->programs as $program)
                {{ $program->labeledName() }}
                <br><br>
                @endforeach
                </p>
            </div>
        </div>

        <div class="mt-4">
            <p class="font-bold text-sm text-[#0E0F3B]">Job Description:</p>
            {{-- Rich text HTML (sanitized server-side on save, see
                 JobPostingController) — a <div> here, not <p>,
                 since the content can include block elements
                 (headings, lists) that aren't valid inside <p>.
                 Height is capped so a long description can't
                 balloon the whole card — it fades out at the
                 bottom instead of hard-clipping mid-line; the
                 "VIEW DETAILS" button shows it in full. --}}
            <div class="relative max-h-[2.6rem] overflow-hidden">
                <div class="text-xs text-gray-500 job-description-content">{!! $job->job_posting_description !!}</div>
                <div class="absolute bottom-0 left-0 right-0 h-4 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-between rp-job-actions">
            <p class="text-xs text-gray-400 flex items-center">
                <i class="far fa-calendar-check mr-1"></i> Valid until: {{ $job->job_closing_date }}
            </p>
            <div class="flex items-center space-x-3 rp-job-actions-buttons">
                @if ($isAlumni)
                @if($hasApplied)
                <button disabled class="bg-green-600 cursor-not-allowed text-white px-8 py-2 rounded-md font-bold text-sm flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> APPLIED
                </button>
                @else
                {{-- data-apply-job-id/data-applied-class: markJobApplied()
                     (partials/job-detail-modal) swaps this for the APPLIED
                     badge above once the application goes through, no reload. --}}
                <button
                    type="button"
                    data-apply-job-id="{{ $job->job_posting_id }}"
                    data-applied-class="bg-green-600 cursor-not-allowed text-white px-8 py-2 rounded-md font-bold text-sm flex items-center gap-2"
                    onclick="openApplyModal({{ $job->job_posting_id }}, {{ $user->alumnus->hasUploadedResumeFile() ? 'true' : 'false' }}, {{ $user->alumnus->hasBuilderResume() ? 'true' : 'false' }}, {{ !empty($user->alumnus->alumnus_cover_letter_file_path) ? 'true' : 'false' }})"
                    class="bg-[#1D46A4] hover:bg-[#0E0F3B] text-white px-8 py-2 rounded-md font-bold text-sm transition-colors">
                    APPLY
                </button>
                @endif
                @elseif (!$user)
                <a href="{{ route('auth.login') }}" class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white px-6 py-2 rounded-md font-bold text-sm transition-colors">
                    LOGIN TO APPLY
                </a>
                @endif

                <button
                    @foreach ($cardData as $attr => $value)
                    data-{{ $attr }}="{{ $value }}"
                    @endforeach
                    onclick="openJobModal(this)"
                    class="border border-[#1D46A4] text-[#1D46A4] px-6 py-2 rounded-md font-bold text-sm hover:bg-[#1D46A4] hover:border-none hover:text-white transition-colors">VIEW DETAILS</button>

                @if ($isAlumni)
                <div class="relative flex items-center group">
                    <div class="bookmark-tooltip invisible opacity-0 absolute bottom-full left-1/2 -translate-x-1/2 mb-2 pointer-events-none transition-all duration-300 z-50">
                        <div class="bg-blue-900 text-white text-[10px] py-1 px-3 rounded shadow-xl whitespace-nowrap relative">
                            <span class="bookmark-flash-text">{{ $isBookmarked ? 'Removed from bookmarks' : 'Job post saved!' }}</span>
                            <div class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px] border-t-blue-900"></div>
                        </div>
                    </div>
                    <span class="hover-tooltip opacity-0 group-hover:opacity-100 absolute bottom-full left-1/2 -translate-x-1/2 mb-2 pointer-events-none transition-opacity duration-200 z-40">
                        <span class="bg-gray-800 text-white text-[10px] py-1 px-2 rounded shadow-lg whitespace-nowrap relative block">
                            <span class="bookmark-hover-label">{{ $isBookmarked ? 'Remove bookmark' : 'Bookmark this job' }}</span>
                            <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></span>
                        </span>
                    </span>
                    {{-- Submitted in the background by partials/job-detail-modal's
                         [data-bookmark-form] handler, so bookmarking doesn't reload the page. --}}
                    <form action="{{ route('jobBookmark.toggle', $job->job_posting_id) }}" method="POST"
                        data-bookmark-form data-bookmark-job-id="{{ $job->job_posting_id }}">
                        @csrf
                        <button type="submit" class="bookmark-btn {{ $isBookmarked ? 'text-blue-900' : 'text-gray-400 hover:text-blue-900' }} text-2xl transition-colors">
                            <i class="{{ $isBookmarked ? 'fas' : 'far' }} fa-bookmark"></i>
                        </button>
                    </form>
                </div>
                @endif

                <div class="relative flex items-center group">
                    <button type="button" class="share-btn text-gray-500 hover:text-black transition-colors p-2" aria-label="Share this job"
                        onclick="copyJobLink(this, {{ $job->job_posting_id }})">
                        <i class="fas fa-share-nodes"></i>
                    </button>
                    <div class="share-tooltip invisible opacity-0 absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-3 rounded shadow-lg transition-all duration-300">
                        Link Copied!
                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-8 border-transparent border-t-gray-800"></div>
                    </div>
                    <span class="hover-tooltip opacity-0 group-hover:opacity-100 absolute bottom-full left-1/2 -translate-x-1/2 mb-2 pointer-events-none transition-opacity duration-200 z-40">
                        <span class="bg-gray-800 text-white text-[10px] py-1 px-2 rounded shadow-lg whitespace-nowrap relative block">
                            Share this job
                            <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></span>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between text-xs text-gray-500 border-t pt-4 flex-wrap gap-2">
            <div class="flex items-center">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($job->user->user_first_name . ' ' . $job->user->user_last_name) }}&background=random"
                    class="w-6 h-6 rounded-full mr-2">
                <span>Posted by <span class="font-bold text-black">{{ $job->user->user_first_name }} {{ $job->user->user_last_name }}</span></span>
            </div>

            <div class="flex items-center gap-3 rp-job-status-row">
                @if ($employer && $isAlumni)
                <div class="flex items-center gap-2">
                    {{-- Up/downvote — per THIS job posting (see JobPostingVote),
                         open to any alumnus regardless of hire status, so the
                         same alumnus can vote on more than one posting from
                         the same company. --}}
                    <button type="button" class="vote-btn flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border transition-colors {{ $myCompanyVote?->vote === 'upvote' ? 'bg-green-600 text-white border-green-600' : 'border-gray-300 text-gray-500 hover:border-green-500 hover:text-green-600' }}"
                        data-employer-id="{{ $employer->user_id }}" data-job-id="{{ $job->job_posting_id }}" data-vote-type="upvote"
                        onclick="castCompanyVote(this)">
                        <i class="fas fa-thumbs-up"></i> <span class="vote-count">{{ $companyUpvotes }}</span>
                    </button>
                    <button type="button" class="vote-btn flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border transition-colors {{ $myCompanyVote?->vote === 'downvote' ? 'bg-red-600 text-white border-red-600' : 'border-gray-300 text-gray-500 hover:border-red-500 hover:text-red-600' }}"
                        data-employer-id="{{ $employer->user_id }}" data-job-id="{{ $job->job_posting_id }}" data-vote-type="downvote"
                        onclick="castCompanyVote(this)">
                        <i class="fas fa-thumbs-down"></i> <span class="vote-count">{{ $companyDownvotes }}</span>
                    </button>

                    {{-- 5-star rating — a separate, per-COMPANY row (see
                         EmployerReview). Picking a star opens the review
                         modal so the alumnus can optionally explain the
                         rating. Only alumni this company actually hired can
                         rate it — see Alumnus::wasHiredByEmployer();
                         everyone else sees the same control, just disabled,
                         rather than it disappearing with no explanation. --}}
                    <div class="star-rating flex items-center gap-0.5 border rounded-full px-2 py-1.5 {{ $canRateCompany ? 'border-gray-300' : 'border-gray-200 opacity-50' }}" data-employer-id="{{ $employer->user_id }}" data-my-rating="{{ $myCompanyRating?->rating ?? 0 }}" data-review-body="{{ $myCompanyRating?->review_body ?? '' }}" @unless($canRateCompany) title="Only alumni this company has hired can rate it" @endunless>
                        @for ($i = 1; $i <= 5; $i++)
                        <button type="button" class="star-btn text-xs {{ ($myCompanyRating?->rating ?? 0) >= $i ? 'text-[#ED7A07]' : 'text-gray-300' }} {{ $canRateCompany ? 'hover:text-[#ED7A07] transition-colors' : 'cursor-not-allowed' }}"
                            data-star="{{ $i }}" @if ($canRateCompany) onclick="castCompanyRating(this)" @else disabled @endif>
                            <i class="fas fa-star"></i>
                        </button>
                        @endfor
                        @if ($employer->ratingCount() > 0)
                        <span class="text-[10px] text-gray-400 ml-1">{{ $employer->averageRating() }}</span>
                        @endif
                    </div>
                </div>
                @endif

                @if ($hasApplied)
                @php $status = $appliedJobs[$job->job_posting_id]->pivot->application_status; @endphp
                <span class="inline-block text-xs font-bold px-3 py-1 rounded-full
                    {{ $status === 'hired' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $status === 'declined' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                    {{ strtoupper($status) }}
                </span>
                @endif
            </div>
        </div>
    </div>

</div>
