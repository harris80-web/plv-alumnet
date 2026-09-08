{{--
    Shared "leave a review" modal — used by two independent triggers on a
    job card (see partials/job-post-card.blade.php): the up/down vote
    buttons and the 5-star rating picker. A bare vote click no longer opens
    this modal (see castCompanyVote() — voting stays a one-click action);
    picking a star always does, since a rating is the deliberate "I want to
    say something about this company" action now. Vote and rating are two
    independent fields on the same EmployerReview row — see that model's
    class doc — so this one modal/one endpoint (employerReviews.vote)
    happily carries either, both, or just a review-text update. Which
    employer/kind/value it's currently acting on is tracked in
    crmEmployerId/crmKind/crmValue.
--}}
<div id="companyReviewModal" class="fixed inset-0 z-[120] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 relative">
        <button type="button" onclick="cancelCompanyReview()" class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors">
            <i class="fas fa-times-circle text-2xl"></i>
        </button>
        <h2 id="crm-title" class="text-lg font-bold text-[#0E0F3B] mb-1 pr-8"></h2>
        <p class="text-sm text-gray-500 mb-4">Optional — share why, to help other alumni.</p>
        <form id="crm-form" onsubmit="submitCompanyReview(event)">
            @csrf
            <textarea id="crm-review-body" name="review_body" rows="4" maxlength="1000"
                oninput="document.getElementById('crmCharCount').textContent = this.value.length"
                placeholder="Write a review (optional)..."
                class="w-full border rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1D46A4]"></textarea>
            <p class="text-right text-[10px] text-gray-400 mt-1"><span id="crmCharCount">0</span>/1000</p>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="skipCompanyReview()" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Skip</button>
                <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-[#1D46A4] hover:bg-[#0E0F3B] rounded-lg transition-colors">Save Review</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Absolute base (e.g. http://host/plvAlumnet/public/companies) — the app
    // isn't necessarily served from the domain root, so a hardcoded
    // '/companies/...' path would miss the '/plvAlumnet/public' prefix and
    // 404 silently. url()/route() already account for it, so build off that.
    const COMPANY_VOTE_BASE_URL = @js(url('/companies'));

    let crmEmployerId = null;
    let crmKind = null;   // 'vote' | 'rating' — which field this modal instance is currently updating
    let crmValue = null;  // the vote string or the 1-5 rating number
    let crmWrap = null;   // the .star-rating element a rating-in-progress belongs to, so cancel can revert its preview

    function companyVoteCsrfToken() {
        return document.querySelector('#crm-form input[name="_token"]').value;
    }

    function castCompanyVote(btn) {
        const employerId = btn.dataset.employerId;
        const jobId = btn.dataset.jobId;
        const voteType = btn.dataset.voteType;
        btn.disabled = true;

        fetch(COMPANY_VOTE_BASE_URL + '/' + employerId + '/vote', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': companyVoteCsrfToken(),
            },
            body: 'vote=' + encodeURIComponent(voteType) + '&job_posting_id=' + encodeURIComponent(jobId),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                // Lightweight — a bare thumbs up/down click just updates the
                // counts in place. It no longer pops the review-message
                // modal; that's only reachable via the star-rating picker
                // now, so voting itself stays a one-click, no-prompt action.
                updateCompanyVoteUI(employerId, data, jobId);
            })
            .finally(function () { btn.disabled = false; });
    }

    // Picking a star is a PREVIEW, not a commit — nothing is sent to the
    // server until "Save Review" or "Skip" is clicked. The modal that opens
    // is the deliberate "I want to say something" follow-up, but closing it
    // via the X button or a backdrop click must be a genuine cancel: before
    // this, the rating was already saved the instant a star was clicked, so
    // dismissing the modal afterward looked (and felt) like it "auto
    // submitted" a rating the alumnus never confirmed.
    function previewStars(wrap, rating) {
        wrap.querySelectorAll('.star-btn').forEach(function (starBtn) {
            const filled = parseInt(starBtn.dataset.star, 10) <= rating;
            starBtn.classList.toggle('text-[#ED7A07]', filled);
            starBtn.classList.toggle('text-gray-300', !filled);
        });
    }

    function castCompanyRating(btn) {
        const wrap = btn.closest('.star-rating');
        const employerId = wrap.dataset.employerId;
        const rating = parseInt(btn.dataset.star, 10);

        crmWrap = wrap;
        previewStars(wrap, rating);
        openCompanyReviewModal(employerId, 'rating', rating, wrap.dataset.reviewBody || '');
    }

    function updateCompanyVoteUI(employerId, data, jobId) {
        // Aggregate up/down counts belong to the COMPANY, so every card for
        // this employer shows the same totals. Which button is highlighted
        // as "mine" is per JOB POSTING though (see JobPostingVote) — only
        // update the active state on cards for the specific posting just
        // voted on; other postings from the same company keep whatever
        // their own vote state already was.
        document.querySelectorAll('.vote-btn[data-employer-id="' + employerId + '"]').forEach(function (b) {
            const type = b.dataset.voteType;
            b.querySelector('.vote-count').textContent = type === 'upvote' ? data.upvotes : data.downvotes;

            // No jobId means this update came from a rating-only commit
            // (see commitCompanyReview) which never touches any vote — skip
            // re-deriving "active" from data.myVote there, since that field
            // is meaningless (always null) on a rating-only response.
            if (jobId === undefined || b.dataset.jobId !== jobId) return;

            const isActive = data.myVote === type;

            const activeClasses = type === 'upvote'
                ? ['bg-green-600', 'text-white', 'border-green-600']
                : ['bg-red-600', 'text-white', 'border-red-600'];
            const hoverClasses = type === 'upvote'
                ? ['hover:border-green-500', 'hover:text-green-600']
                : ['hover:border-red-500', 'hover:text-red-600'];

            b.classList.remove('bg-green-600', 'bg-red-600', 'text-white', 'border-green-600', 'border-red-600',
                'border-gray-300', 'text-gray-500', 'hover:border-green-500', 'hover:text-green-600',
                'hover:border-red-500', 'hover:text-red-600');

            if (isActive) {
                b.classList.add(...activeClasses);
            } else {
                b.classList.add('border-gray-300', 'text-gray-500', ...hoverClasses);
            }
        });

        document.querySelectorAll('.star-rating[data-employer-id="' + employerId + '"]').forEach(function (wrap) {
            const myRating = data.myRating || 0;
            wrap.dataset.myRating = myRating;
            wrap.dataset.reviewBody = data.reviewBody || '';
            wrap.querySelectorAll('.star-btn').forEach(function (starBtn) {
                const filled = parseInt(starBtn.dataset.star, 10) <= myRating;
                starBtn.classList.toggle('text-[#ED7A07]', filled);
                starBtn.classList.toggle('text-gray-300', !filled);
            });

            let avgLabel = wrap.querySelector('.star-avg-label');
            if (data.ratingCount > 0) {
                if (!avgLabel) {
                    avgLabel = document.createElement('span');
                    avgLabel.className = 'star-avg-label text-[10px] text-gray-400 ml-1';
                    wrap.appendChild(avgLabel);
                }
                avgLabel.textContent = data.averageRating;
            } else if (avgLabel) {
                avgLabel.remove();
            }
        });

        document.querySelectorAll('.reviews-link[data-employer-id="' + employerId + '"] .reviews-count').forEach(function (el) {
            el.textContent = data.ratingCount;
        });
    }

    function openCompanyReviewModal(employerId, kind, value, existingBody) {
        crmEmployerId = employerId;
        crmKind = kind;
        crmValue = value;
        document.getElementById('crm-title').textContent = kind === 'rating'
            ? `Thanks for the ${value}-star rating! Want to add a review?`
            : (value === 'upvote' ? 'Thanks for the upvote! Want to add a review?' : 'Thanks for the feedback — want to explain why?');
        document.getElementById('crm-review-body').value = existingBody || '';
        document.getElementById('crmCharCount').textContent = (existingBody || '').length;
        document.getElementById('companyReviewModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCompanyReviewModal() {
        document.getElementById('companyReviewModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        crmWrap = null;
    }

    // Actually sends rating/vote + whatever review text (possibly empty) to
    // the server — the one real commit point. Both "Save Review" and "Skip"
    // go through here; they only differ in whether the typed text comes
    // along, not in whether the rating itself gets saved.
    function commitCompanyReview(reviewBody, submitBtn) {
        const field = crmKind === 'rating' ? 'rating' : 'vote';
        if (submitBtn) submitBtn.disabled = true;

        return fetch(COMPANY_VOTE_BASE_URL + '/' + crmEmployerId + '/vote', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': companyVoteCsrfToken(),
            },
            body: field + '=' + encodeURIComponent(crmValue) + '&review_body=' + encodeURIComponent(reviewBody || ''),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                updateCompanyVoteUI(crmEmployerId, data);
                closeCompanyReviewModal();
            })
            .finally(function () { if (submitBtn) submitBtn.disabled = false; });
    }

    function submitCompanyReview(e) {
        e.preventDefault();
        const body = document.getElementById('crm-review-body').value;
        const submitBtn = e.target.querySelector('button[type="submit"]');
        commitCompanyReview(body, submitBtn);
    }

    // "Skip" still records the rating/vote picked — it only skips attaching
    // written feedback, matching this modal's "Optional — share why" copy.
    function skipCompanyReview() {
        commitCompanyReview('');
    }

    // A genuine cancel — the X button and clicking outside the modal. Since
    // castCompanyRating() above only PREVIEWS the star selection now
    // (nothing was ever sent to the server), this just restores the stars
    // to whatever's actually saved and closes — no request at all.
    function cancelCompanyReview() {
        if (crmWrap) {
            previewStars(crmWrap, parseInt(crmWrap.dataset.myRating || '0', 10));
        }
        closeCompanyReviewModal();
    }

    window.addEventListener('click', function (event) {
        if (event.target === document.getElementById('companyReviewModal')) cancelCompanyReview();
    });
</script>
