{{--
    Shared "View Details" modal for job cards — used by the job board
    (partials.job-post-card) and the alumni dashboard's "Job Matches For
    You" cards, so clicking either kind of card opens the identical modal.
    openJobModal() reads everything from the triggering element's data-*
    attributes (see partials/job-post-card.blade.php's $cardData), so the
    modal always mirrors the exact card that was clicked — badges, vote
    buttons, bookmark/apply state, "posted by", skill tags, everything.

    Desktop side panel (Job Board only): a page that also renders
    #jobDetailPanel (general/jobBoard.blade.php) shows these SAME details in a
    docked panel beside the job list on desktop (>= 1024px, the site's
    desktop breakpoint) instead of this modal. There is only one copy of the
    details markup — #jobDetailsContent — which is moved between the modal
    shell and the panel; openJobModal() fills it the same way either way.
    Pages without #jobDetailPanel (alumni dashboard) always use the modal.
--}}
<style>
    /* Long titles / words / URLs and embedded media never widen the details. */
    #jobDetailsContent .job-description-content { overflow-wrap: anywhere; }
    #jobDetailsContent .job-description-content img,
    #jobDetailsContent .job-description-content video,
    #jobDetailsContent .job-description-content iframe,
    #jobDetailsContent .job-description-content table { max-width: 100%; height: auto; }
    #jobDetailsContent .job-description-content pre { white-space: pre-wrap; }

    @media (max-width: 1023px) {
        /* Mobile modal: comfortable 44px tap targets for the CTAs. */
        #jobModal :is(#modal-apply-btn, #modal-applied-btn, #modal-login-link) { min-height: 2.75rem; }
        /* Keep the sheet inside the notch / home-indicator safe area. */
        #jobModal { padding: max(0.5rem, env(safe-area-inset-top)) max(0.5rem, env(safe-area-inset-right)) max(0.5rem, env(safe-area-inset-bottom)) max(0.5rem, env(safe-area-inset-left)); }
        #jobModalShell { max-height: 100%; }
    }
</style>

<div id="jobModal" class="fixed inset-0 z-[100] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-2 md:p-4"
    role="dialog" aria-modal="true" aria-labelledby="modal-title">

    <div id="jobModalShell" class="bg-white w-full max-w-6xl h-[95vh] md:h-[92vh] rounded-3xl shadow-2xl relative flex flex-col overflow-hidden">

        {{-- The details themselves: one copy, moved into #jobDetailPanel on the Job Board's desktop view. --}}
        <div id="jobDetailsContent" class="flex-1 overflow-y-auto overscroll-contain">
            <div class="h-56 md:h-72 w-full relative shrink-0 jd-hero">
                <img id="modal-img" src="" alt="" class="hidden w-full h-full object-cover">
                <div id="modal-img-fallback" class="hidden w-full h-full bg-gradient-to-br from-[#0E0F3B] to-[#1D264F] items-center justify-center">
                    <i class="fas fa-building text-white/30 text-7xl"></i>
                </div>
                <div id="modal-img-overlay" class="absolute inset-0 bg-[#0E0F3B]/60"></div>

                <span id="modal-recommended-badge" class="hidden absolute top-4 left-4 z-10 bg-[#ED7A07] text-white text-[10px] font-bold uppercase px-3 py-1 rounded-full shadow-md">
                    <i class="fas fa-star mr-1"></i> Recommended
                </span>

                <button onclick="toggleModal()" aria-label="Close job details" class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>
            </div>

            <div class="p-6 md:p-10 jd-body">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                    <div class="min-w-0">
                        <h2 id="modal-title" class="text-2xl md:text-3xl font-bold text-[#1D264F] uppercase tracking-tighter break-words">JOB TITLE</h2>
                        <div class="flex flex-wrap items-center text-gray-600 mt-1 gap-x-4 gap-y-1">
                            <p id="modal-company" class="font-semibold text-lg break-words min-w-0">Company Name</p>
                            <span id="modal-date" class="flex items-center text-sm"><i class="far fa-calendar-alt mr-2"></i> Posted 2 days ago</span>
                        </div>
                        <p id="modal-address" class="text-gray-500 font-medium break-words">Company Address</p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <form id="modal-bookmark-form" method="POST" action="" class="hidden relative group">
                            @csrf
                            <button type="submit" id="modal-bookmark-btn" class="text-gray-400 hover:text-blue-900 text-2xl transition-colors">
                                <i class="far fa-bookmark"></i>
                            </button>
                            <span class="hover-tooltip opacity-0 group-hover:opacity-100 absolute bottom-full left-1/2 -translate-x-1/2 mb-2 pointer-events-none transition-opacity duration-200 z-40">
                                <span class="bg-gray-800 text-white text-[10px] py-1 px-2 rounded shadow-lg whitespace-nowrap relative block">
                                    <span id="modal-bookmark-hover-label">Bookmark this job</span>
                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></span>
                                </span>
                            </span>
                        </form>
                        <div class="relative group">
                            <div id="modal-share-tooltip" class="invisible opacity-0 absolute bottom-full right-0 mb-2 pointer-events-none transition-all duration-300 z-50">
                                <div class="bg-gray-800 text-white text-[10px] py-1 px-3 rounded shadow-xl whitespace-nowrap relative">
                                    Link Copied!
                                    <div class="absolute top-full right-2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px] border-t-gray-800"></div>
                                </div>
                            </div>
                            <span class="hover-tooltip opacity-0 group-hover:opacity-100 absolute bottom-full right-0 mb-2 pointer-events-none transition-opacity duration-200 z-40">
                                <span class="bg-gray-800 text-white text-[10px] py-1 px-2 rounded shadow-lg whitespace-nowrap relative block">
                                    Share this job
                                    <span class="absolute top-full right-2 border-4 border-transparent border-t-gray-800"></span>
                                </span>
                            </span>
                            <button onclick="showModalTooltip('modal-share-tooltip')" aria-label="Share this job" class="text-[#1D264F] text-2xl hover:scale-110 transition-transform">
                                <i class="fas fa-share-nodes"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-col md:flex-row gap-8 jd-split">
                    <div class="md:w-3/5 min-w-0 jd-col">
                        <h3 class="font-bold text-[#0E0F3B] mb-3">Job Description:</h3>
                        <div id="modal-description" class="text-gray-600 text-sm leading-relaxed text-justify job-description-content">
                        </div>

                        <div id="modal-skills-wrap" class="hidden mt-6">
                            <h3 class="font-bold text-[#0E0F3B] mb-2 text-sm">Skills:</h3>
                            <div id="modal-skills" class="flex flex-wrap gap-2"></div>
                        </div>
                    </div>

                    <div class="md:w-2/5 space-y-3 text-[#1D264F] min-w-0 jd-col">
                        <p class="flex justify-between gap-3 text-sm"><span class="font-bold">Job Type:</span> <span id="modal-job-type" class="text-right">Full-Time</span></p>
                        <p class="flex justify-between gap-3 text-sm"><span class="font-bold">Job Setup:</span> <span id="modal-job-setup" class="text-right">Remote</span></p>
                        <div class="pt-3 border-t border-gray-100">
                            <p class="font-bold text-sm">Recommended Course/Program:</p>
                            <p id="modal-programs" class="text-sm leading-snug text-gray-600 mt-1 break-words">
                                BSIT - Bachelor of Science in Information Technology
                            </p>
                        </div>
                        <div class="pt-3 border-t border-gray-100">
                            <p class="font-bold text-sm">Industry / Sector:</p>
                            <p id="modal-industry" class="text-sm leading-snug text-gray-600 mt-1 break-words"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t flex flex-col md:flex-row md:items-center md:justify-between gap-4 jd-row">
                    <div class="flex flex-wrap items-center gap-4">
                        <div id="modal-valid" class="text-gray-500 text-sm flex items-center font-semibold">
                            <i class="far fa-calendar-check mr-2"></i> Valid until
                        </div>
                        <span id="modal-status-badge" class="hidden text-xs font-bold px-3 py-1 rounded-full"></span>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-500 min-w-0">
                        <img id="modal-posted-avatar" src="" alt="" class="w-6 h-6 rounded-full shrink-0">
                        <span class="break-words min-w-0">Posted by <span id="modal-posted-by" class="font-bold text-black"></span></span>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4 jd-row">
                    <div class="flex items-center gap-3 flex-wrap">
                        <div id="modal-vote-section" class="hidden flex items-center gap-2 flex-wrap">
                            <button type="button" class="vote-btn flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border transition-colors border-gray-300 text-gray-500 hover:border-green-500 hover:text-green-600"
                                data-vote-type="upvote" onclick="castCompanyVote(this)">
                                <i class="fas fa-thumbs-up"></i> <span class="vote-count">0</span>
                            </button>
                            <button type="button" class="vote-btn flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border transition-colors border-gray-300 text-gray-500 hover:border-red-500 hover:text-red-600"
                                data-vote-type="downvote" onclick="castCompanyVote(this)">
                                <i class="fas fa-thumbs-down"></i> <span class="vote-count">0</span>
                            </button>

                            {{-- 5-star rating — independent of the thumbs vote above, same as
                                 partials/job-post-card.blade.php's copy of this widget. Picking a
                                 star opens the review modal so the alumnus can optionally explain it. --}}
                            <div id="modal-star-rating" class="star-rating flex items-center gap-0.5 border border-gray-300 rounded-full px-2 py-1.5">
                                @for ($i = 1; $i <= 5; $i++)
                                <button type="button" class="star-btn text-xs text-gray-300 hover:text-[#ED7A07] transition-colors"
                                    data-star="{{ $i }}" onclick="castCompanyRating(this)">
                                    <i class="fas fa-star"></i>
                                </button>
                                @endfor
                                <span id="modal-star-avg-label" class="star-avg-label text-[10px] text-gray-400 ml-1"></span>
                            </div>
                        </div>

                        <a id="modal-reviews-link" href="#" class="reviews-link hidden inline-flex text-xs font-bold text-[#1D46A4] hover:underline items-center gap-1.5">
                            <i class="fas fa-comment-dots"></i> Reviews (<span class="reviews-count">0</span>)
                        </a>
                    </div>

                    {{-- Phones: the CTA goes full width (easy to tap); from sm up it keeps its own width. --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full sm:w-auto">
                        <button type="button" id="modal-apply-btn" class="hidden w-full sm:w-auto bg-[#1D46A4] hover:bg-[#0E0F3B] text-white px-8 py-2 rounded-md font-bold text-sm transition-colors">
                            APPLY
                        </button>
                        <button type="button" id="modal-applied-btn" disabled class="hidden w-full sm:w-auto justify-center bg-green-600 cursor-not-allowed text-white px-8 py-2 rounded-md font-bold text-sm items-center gap-2">
                            <i class="fas fa-check-circle"></i> APPLIED
                        </button>
                        <a id="modal-login-link" href="{{ route('auth.login') }}" class="hidden w-full sm:w-auto text-center bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white px-6 py-2 rounded-md font-bold text-sm transition-colors">
                            LOGIN TO APPLY
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Route templates — a sentinel id gets swapped for the real job/employer
    // id at open-time, since the modal is shared by every card and can't
    // hardcode which job it's showing until openJobModal() runs.
    const JOB_MODAL_BOOKMARK_URL_TMPL = @json(route('jobBookmark.toggle', 999999999));
    const JOB_MODAL_REVIEWS_URL_TMPL = @json(route('employerReviews.index', ['employer' => 999999999]));

    // ── Modal vs. desktop side panel ──────────────────────────────────────
    // Desktop = >= 1024px (Tailwind `lg`, the same line responsive-public.css
    // uses for its "compact" <= 1023px rules). Only pages that render
    // #jobDetailPanel get the panel; everywhere else stays a modal.
    const JOB_DETAILS_DESKTOP_MQ = window.matchMedia('(min-width: 1024px)');
    const jobDetailsState = { open: false, mode: null, trigger: null, title: '' };

    function jobDetailsPanelEl() { return document.getElementById('jobDetailPanel'); }
    function jobDetailsUsesPanel() { return !!jobDetailsPanelEl() && JOB_DETAILS_DESKTOP_MQ.matches; }

    // Moves the one #jobDetailsContent into the panel or back into the modal shell.
    function placeJobDetails(inPanel) {
        const content = document.getElementById('jobDetailsContent');
        const target = inPanel ? document.getElementById('jobDetailPanelBody') : document.getElementById('jobModalShell');
        if (content && target && content.parentElement !== target) target.appendChild(content);
    }

    function showJobDetailsModal() {
        placeJobDetails(false);
        document.getElementById('jobModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        jobDetailsState.mode = 'modal';
    }

    function hideJobDetailsModal() {
        document.getElementById('jobModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function showJobDetailsPanel() {
        const panel = jobDetailsPanelEl();
        placeJobDetails(true);
        window.jobBoardPanel?.open(jobDetailsState.title, jobDetailsState.trigger);
        panel.setAttribute('aria-label', 'Job details: ' + jobDetailsState.title);
        jobDetailsState.mode = 'panel';
    }

    function openJobModal(btn) {
        const d = btn.dataset;
        jobDetailsState.trigger = btn;
        jobDetailsState.title = d.title || '';

        const img = document.getElementById('modal-img');
        const fallback = document.getElementById('modal-img-fallback');
        const imgOverlay = document.getElementById('modal-img-overlay');
        if (d.image) {
            img.src = d.image;
            img.classList.remove('hidden');
            fallback.classList.add('hidden');
            fallback.classList.remove('flex');
            if (d.usesDefaultImage === '1') {
                // Stock job-post artwork — tint it with its own
                // color/opacity instead of the generic navy wash real uploads get.
                img.style.opacity = d.imageOpacity;
                imgOverlay.className = 'absolute inset-0';
                imgOverlay.style.backgroundColor = d.imageOverlayColor;
                imgOverlay.style.opacity = d.imageOverlayOpacity;
            } else {
                img.style.opacity = '';
                imgOverlay.className = 'absolute inset-0 bg-[#0E0F3B]/60';
                imgOverlay.style.backgroundColor = '';
                imgOverlay.style.opacity = '';
            }
        } else {
            img.classList.add('hidden');
            fallback.classList.remove('hidden');
            fallback.classList.add('flex');
        }

        document.getElementById('modal-recommended-badge').classList.toggle('hidden', d.recommended !== '1');

        document.getElementById('modal-title').textContent = d.title;
        document.getElementById('modal-company').textContent = d.company;
        document.getElementById('modal-date').innerHTML = '<i class="far fa-calendar-alt mr-2"></i> ' + d.date;
        document.getElementById('modal-address').textContent = d.address;
        document.getElementById('modal-description').innerHTML = d.description;
        document.getElementById('modal-job-type').textContent = d.type;
        document.getElementById('modal-job-setup').textContent = d.setup;
        document.getElementById('modal-programs').textContent = d.programs;
        document.getElementById('modal-industry').textContent = d.industry;
        document.getElementById('modal-valid').innerHTML = '<i class="far fa-calendar-check mr-2"></i> Valid until: ' + d.valid;

        // Skills tags
        const skillsWrap = document.getElementById('modal-skills-wrap');
        const skillsBox = document.getElementById('modal-skills');
        const skills = (d.skills || '').split('||').map(s => s.trim()).filter(Boolean);
        if (skills.length) {
            skillsBox.innerHTML = skills.map(s => '<span class="bg-gray-100 text-[#0E0F3B] text-xs font-semibold px-3 py-1 rounded-full">' + s + '</span>').join('');
            skillsWrap.classList.remove('hidden');
        } else {
            skillsBox.innerHTML = '';
            skillsWrap.classList.add('hidden');
        }

        // Posted by
        document.getElementById('modal-posted-by').textContent = d.postedBy || '';
        document.getElementById('modal-posted-avatar').src = d.postedByAvatar || '';

        // Reviews link — count is ratings, not votes (see castCompanyRating(); the reviews page/count are rating-driven now)
        const reviewsLink = document.getElementById('modal-reviews-link');
        if (d.reviewsVisible === '1' && d.employerId) {
            reviewsLink.href = JOB_MODAL_REVIEWS_URL_TMPL.replace('999999999', d.employerId);
            reviewsLink.dataset.employerId = d.employerId;
            reviewsLink.querySelector('.reviews-count').textContent = Number(d.ratingCount || 0);
            reviewsLink.classList.remove('hidden');
        } else {
            reviewsLink.classList.add('hidden');
        }

        // Company vote buttons — per THIS job posting (see JobPostingVote),
        // open to any alumnus regardless of hire status. The 5-star rating
        // below stays hire-gated; voting no longer is.
        const canRate = d.canRateCompany === '1';
        const voteSection = document.getElementById('modal-vote-section');
        if (d.voteVisible === '1' && d.employerId) {
            voteSection.title = '';
            voteSection.querySelectorAll('.vote-btn').forEach(function (btnEl) {
                const type = btnEl.dataset.voteType;
                btnEl.dataset.employerId = d.employerId;
                btnEl.dataset.jobId = d.jobId;
                btnEl.querySelector('.vote-count').textContent = type === 'upvote' ? (d.upvotes || 0) : (d.downvotes || 0);
                btnEl.disabled = false;

                const isActive = d.myVote === type;
                btnEl.classList.remove('bg-green-600', 'bg-red-600', 'text-white', 'border-green-600', 'border-red-600',
                    'border-gray-300', 'text-gray-500', 'hover:border-green-500', 'hover:text-green-600',
                    'hover:border-red-500', 'hover:text-red-600', 'opacity-50', 'cursor-not-allowed', 'border-gray-200', 'text-gray-400');
                if (isActive) {
                    btnEl.classList.add(...(type === 'upvote' ? ['bg-green-600', 'text-white', 'border-green-600'] : ['bg-red-600', 'text-white', 'border-red-600']));
                } else {
                    btnEl.classList.add('border-gray-300', 'text-gray-500', ...(type === 'upvote' ? ['hover:border-green-500', 'hover:text-green-600'] : ['hover:border-red-500', 'hover:text-red-600']));
                }
            });
            voteSection.classList.remove('hidden');
        } else {
            voteSection.classList.add('hidden');
        }

        // 5-star rating widget — hire-gated (see Alumnus::wasHiredByEmployer()) — item 4: alumni can rate the company right from the details modal, not just the compact card.
        const starRating = document.getElementById('modal-star-rating');
        if (d.voteVisible === '1' && d.employerId) {
            const myRating = Number(d.myRating || 0);
            starRating.dataset.employerId = d.employerId;
            starRating.dataset.myRating = myRating;
            starRating.dataset.reviewBody = d.myReviewBody || '';
            starRating.title = canRate ? '' : 'Only alumni this company has hired can rate it';
            starRating.classList.toggle('opacity-50', !canRate);
            starRating.querySelectorAll('.star-btn').forEach(function (starBtn) {
                const filled = Number(starBtn.dataset.star) <= myRating;
                starBtn.classList.toggle('text-[#ED7A07]', filled);
                starBtn.classList.toggle('text-gray-300', !filled);
                starBtn.classList.toggle('cursor-not-allowed', !canRate);
                starBtn.disabled = !canRate;
            });
            const avgLabel = document.getElementById('modal-star-avg-label');
            avgLabel.textContent = Number(d.ratingCount || 0) > 0 ? d.averageRating : '';
        }

        // Bookmark button
        const bookmarkForm = document.getElementById('modal-bookmark-form');
        const bookmarkBtn = document.getElementById('modal-bookmark-btn');
        if (d.isAlumni === '1' && d.jobId) {
            bookmarkForm.action = JOB_MODAL_BOOKMARK_URL_TMPL.replace('999999999', d.jobId);
            const bookmarked = d.isBookmarked === '1';
            bookmarkBtn.classList.toggle('text-blue-900', bookmarked);
            bookmarkBtn.classList.toggle('text-gray-400', !bookmarked);
            bookmarkBtn.querySelector('i').className = (bookmarked ? 'fas' : 'far') + ' fa-bookmark';
            document.getElementById('modal-bookmark-hover-label').textContent = bookmarked ? 'Remove bookmark' : 'Bookmark this job';
            bookmarkForm.classList.remove('hidden');
        } else {
            bookmarkForm.classList.add('hidden');
        }

        // Apply / Applied / Login — opening the Apply review modal closes
        // the details MODAL first (see partials/job-apply-modal.blade.php)
        // rather than stacking a second modal on top of it. The desktop
        // side panel isn't a modal, so it simply stays open underneath.
        const applyBtn = document.getElementById('modal-apply-btn');
        const appliedBtn = document.getElementById('modal-applied-btn');
        const loginLink = document.getElementById('modal-login-link');
        applyBtn.classList.add('hidden');
        appliedBtn.classList.add('hidden');
        appliedBtn.classList.remove('flex');
        loginLink.classList.add('hidden');

        if (d.isAlumni === '1') {
            if (d.hasApplied === '1') {
                appliedBtn.classList.remove('hidden');
                appliedBtn.classList.add('flex');
            } else {
                const jobId = d.jobId,
                    hasUploadedResumeFile = d.hasUploadedResumeFile === '1',
                    hasBuilderResume = d.hasBuilderResume === '1',
                    hasProfileCoverLetter = d.hasProfileCoverLetter === '1';
                applyBtn.onclick = function () {
                    if (jobDetailsState.mode !== 'panel') toggleModal();
                    openApplyModal(jobId, hasUploadedResumeFile, hasBuilderResume, hasProfileCoverLetter);
                };
                applyBtn.classList.remove('hidden');
            }
        } else if (d.isGuest === '1') {
            loginLink.classList.remove('hidden');
        }

        // Application status badge
        const statusBadge = document.getElementById('modal-status-badge');
        if (d.hasApplied === '1' && d.applicationStatus) {
            statusBadge.textContent = d.applicationStatus.toUpperCase();
            statusBadge.className = 'text-xs font-bold px-3 py-1 rounded-full '
                + (d.applicationStatus === 'hired' ? 'bg-green-100 text-green-700'
                    : d.applicationStatus === 'declined' ? 'bg-red-100 text-red-700'
                    : 'bg-yellow-100 text-yellow-700');
        } else {
            statusBadge.classList.add('hidden');
        }

        // Each newly opened job starts at the top of its details.
        document.getElementById('jobDetailsContent').scrollTop = 0;
        jobDetailsState.open = true;
        if (jobDetailsUsesPanel()) {
            showJobDetailsPanel();
        } else {
            showJobDetailsModal();
        }
    }

    // Closes the details — the modal, or the desktop side panel (name kept: every caller already uses it).
    function toggleModal() {
        if (jobDetailsState.mode === 'panel') {
            window.jobBoardPanel?.close();
        } else {
            hideJobDetailsModal();
        }
        jobDetailsState.open = false;
        jobDetailsState.mode = null;
        const trigger = jobDetailsState.trigger;
        if (trigger && document.contains(trigger)) trigger.focus({ preventScroll: true });
    }

    // Resizing across the desktop breakpoint while a job is open: switch to the
    // other presentation for the SAME job (panel <-> modal), nothing re-filled.
    JOB_DETAILS_DESKTOP_MQ.addEventListener('change', function () {
        if (!jobDetailsState.open || !jobDetailsPanelEl()) return;
        if (jobDetailsUsesPanel() && jobDetailsState.mode === 'modal') {
            hideJobDetailsModal();
            showJobDetailsPanel();
        } else if (!jobDetailsUsesPanel() && jobDetailsState.mode === 'panel') {
            window.jobBoardPanel?.close({ instant: true });
            showJobDetailsModal();
        }
    });

    // Optional: Close modal if user clicks outside the white box
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('jobModal');
        if (event.target == modal) {
            toggleModal();
        }
    });

    function showModalTooltip(tooltipId) {
        const tooltip = document.getElementById(tooltipId);

        tooltip.classList.remove('invisible', 'opacity-0');
        tooltip.classList.add('opacity-100');

        if (tooltipId === 'modal-share-tooltip') {
            navigator.clipboard.writeText(window.location.href);
        }

        setTimeout(() => {
            tooltip.classList.add('invisible', 'opacity-0');
            tooltip.classList.remove('opacity-100');
        }, 2000);
    }
</script>
