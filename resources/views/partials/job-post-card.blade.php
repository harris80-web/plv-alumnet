@php
    $isAlumni = $user && $user->user_role === 'alumni';
    $hasApplied = $isAlumni && isset($appliedJobs) && $appliedJobs->has($job->job_posting_id);
    $isBookmarked = $isAlumni && in_array($job->job_posting_id, $bookmarkedIds ?? [], true);
    // match_score comes from JobPostingController::filteredJobPostingsQuery()'s
    // correlated subquery (alumni only) — same blended score / threshold the
    // board sorts by, so this badge always agrees with the ordering.
    $isRecommended = $isAlumni && isset($job->match_score) && $job->match_score !== null
        && (float) $job->match_score >= ($recommendedThreshold ?? 50);
@endphp

<div class="bg-white rounded-3xl shadow-md flex flex-col md:flex-row relative hover:shadow-lg transition-shadow md:min-h-[340px]">

    <div class="md:w-1/4 h-48 md:h-auto bg-gray-300 relative rounded-t-3xl md:rounded-l-3xl md:rounded-tr-none overflow-hidden group cursor-pointer"
        role="button" tabindex="0" aria-label="View job details"
        data-title="{{ $job->job_posting_title }}"
        data-company="{{ $job->job_posting_company }}"
        data-address="{{ $job->job_posting_address }}"
        data-date="{{ $job->created_at->diffForHumans() }}"
        data-description="{{ $job->job_posting_description }}"
        data-type="{{ $job->job_posting_employment_type }}"
        data-setup="{{ $job->job_posting_setup }}"
        data-valid="{{ $job->job_closing_date }}"
        data-image="{{ asset('storage/' . $job->job_posting_image) }}"
        data-programs="{{ $job->programs->pluck('program_name')->implode(', ') }}"
        data-industry="{{ $job->industry->industry_name ?? 'Not specified' }}"
        onclick="openJobModal(this)"
        onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openJobModal(this);}">
        <img src="{{ asset('storage/'.$job->job_posting_image) }}"
            class="object-cover w-full h-full opacity-60 group-hover:opacity-80 group-hover:scale-105 transition-all duration-300">
        <div class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>
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

    <div class="p-6 flex-1 relative">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold uppercase text-[#0E0F3B]">
                    {{ $job->job_posting_title }}
                </h2>

                <p class="text-gray-600">
                    {{ $job->job_posting_company }}
                </p>

                <p class="text-gray-500 text-sm">
                    {{ $job->job_posting_address }}
                </p>

            </div>

            <div class="text-right">
                @if ($isAlumni)
                <div class="relative flex flex-col items-end">
                    <div class="bookmark-tooltip invisible opacity-0 absolute bottom-full right-0 mb-2 pointer-events-none transition-all duration-300 z-50">
                        <div class="bg-blue-900 text-white text-[10px] py-1 px-3 rounded shadow-xl whitespace-nowrap relative">
                            {{ $isBookmarked ? 'Removed from bookmarks' : 'Job post saved!' }}
                            <div class="absolute top-full right-2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px] border-t-blue-900"></div>
                        </div>
                    </div>
                    <form action="{{ route('jobBookmark.toggle', $job->job_posting_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bookmark-btn {{ $isBookmarked ? 'text-blue-900' : 'text-gray-400 hover:text-blue-900' }} text-2xl transition-colors">
                            <i class="{{ $isBookmarked ? 'fas' : 'far' }} fa-bookmark"></i>
                        </button>
                    </form>
                </div>
                @endif
                <p class="text-xs text-gray-400 mt-2 flex items-center justify-end">
                    <i class="far fa-calendar-alt mr-1"></i> {{ $job->created_at->diffForHumans() }}
                </p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4 text-sm font-semibold">
            <div>
                <p class="text-[#0E0F3B]">Job Type: <span class="font-normal">{{ $job->job_posting_employment_type }}</span></p>
                <p class="text-[#0E0F3B]">Job Setup: <span class="font-normal">{{ $job->job_posting_setup }}</span></p>
            </div>

            <div>
                <p>
                <p class="text-blue-900">Recommended Course/Program: <span class="font-normal ph"></span></p>
                @foreach ($job->programs as $program)
                {{ $program->program_name }}
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

        <div class="mt-6 flex items-center justify-between">
            <p class="text-xs text-gray-400 flex items-center">
                <i class="far fa-calendar-check mr-1"></i> Valid until: {{ $job->job_closing_date }}
            </p>
            <div class="flex items-center space-x-3">
                @if ($isAlumni)
                @if($hasApplied)
                <button disabled class="bg-green-600 cursor-not-allowed text-white px-8 py-2 rounded-md font-bold text-sm flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> APPLIED
                </button>
                @else
                <form action="{{ route('jobApplication.apply', $job->job_posting_id) }}" method="POST">
                    @csrf
                    <button
                    type="submit"
                    class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white px-8 py-2 rounded-md font-bold text-sm transition-colors">
                    APPLY
                </button>
                </form>

                @endif
                @elseif (!$user)
                <a href="{{ route('auth.login') }}" class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white px-6 py-2 rounded-md font-bold text-sm transition-colors">
                    LOGIN TO APPLY
                </a>
                @endif

                @php $jobImageUrl = asset('storage/' . $job->job_posting_image); @endphp
                <button
                    data-title="{{ $job->job_posting_title }}"
                    data-company="{{ $job->job_posting_company }}"
                    data-address="{{ $job->job_posting_address }}"
                    data-date="{{ $job->created_at->diffForHumans() }}"
                    data-description="{{ $job->job_posting_description }}"
                    data-type="{{ $job->job_posting_employment_type }}"
                    data-setup="{{ $job->job_posting_setup }}"
                    data-valid="{{ $job->job_closing_date }}"
                    data-image="{{ asset('storage/' . $job->job_posting_image) }}"
                    data-programs="{{ $job->programs->pluck('program_name')->implode(', ') }}"
        data-industry="{{ $job->industry->industry_name ?? 'Not specified' }}"
                    onclick="openJobModal(this)"
                    class="border border-[#1D46A4] text-[#1D46A4] px-6 py-2 rounded-md font-bold text-sm hover:bg-[#1D46A4] hover:border-none hover:text-white transition-colors">VIEW DETAILS</button>
                <div class="relative flex items-center">
                    <button class="share-btn text-gray-500 hover:text-black transition-colors p-2" onclick="copyJobLink(this)">
                        <i class="fas fa-share-nodes"></i>
                    </button>
                    <div class="share-tooltip invisible opacity-0 absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-3 rounded shadow-lg transition-all duration-300">
                        Link Copied!
                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-8 border-transparent border-t-gray-800"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between text-xs text-gray-500 border-t pt-4">
            <div class="flex items-center">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($job->user->user_first_name . ' ' . $job->user->user_last_name) }}&background=random"
                    class="w-6 h-6 rounded-full mr-2">
                <span>Posted by <span class="font-bold text-black">{{ $job->user->user_first_name }} {{ $job->user->user_last_name }}</span></span>
            </div>

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
