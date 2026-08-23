{{--
    Shared "leave a review" modal used by the company up/down vote buttons on
    job cards (see partials/job-post-card.blade.php). A bare vote click
    (upvote/downvote button) posts to employerReviews.vote with no
    review_body — if that click set/switched an active vote (as opposed to
    toggling it off), this modal opens so the alumnus can optionally attach
    a written review to the vote they just cast. One instance serves every
    card on the page; which employer/vote it's currently acting on is
    tracked in crmEmployerId/crmVoteType.
--}}
<div id="companyReviewModal" class="fixed inset-0 z-[120] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 relative">
        <button type="button" onclick="closeCompanyReviewModal()" class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors">
            <i class="fas fa-times-circle text-2xl"></i>
        </button>
        <h2 id="crm-title" class="text-lg font-bold text-[#0E0F3B] mb-1 pr-8"></h2>
        <p class="text-sm text-gray-500 mb-4">Optional — share why, to help other alumni.</p>
        <form id="crm-form" onsubmit="submitCompanyReview(event)">
            @csrf
            <textarea id="crm-review-body" name="review_body" rows="4" maxlength="1000"
                placeholder="Write a review (optional)..."
                class="w-full border rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1D46A4]"></textarea>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="closeCompanyReviewModal()" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Skip</button>
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
    let crmVoteType = null;

    function companyVoteCsrfToken() {
        return document.querySelector('#crm-form input[name="_token"]').value;
    }

    function castCompanyVote(btn) {
        const employerId = btn.dataset.employerId;
        const voteType = btn.dataset.voteType;
        btn.disabled = true;

        fetch(COMPANY_VOTE_BASE_URL + '/' + employerId + '/vote', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': companyVoteCsrfToken(),
            },
            body: 'vote=' + encodeURIComponent(voteType),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                updateCompanyVoteUI(employerId, data);
                if (data.myVote === voteType) {
                    openCompanyReviewModal(employerId, voteType, data.reviewBody);
                }
            })
            .finally(function () { btn.disabled = false; });
    }

    function updateCompanyVoteUI(employerId, data) {
        document.querySelectorAll('.vote-btn[data-employer-id="' + employerId + '"]').forEach(function (b) {
            const type = b.dataset.voteType;
            const isActive = data.myVote === type;

            b.querySelector('.vote-count').textContent = type === 'upvote' ? data.upvotes : data.downvotes;

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

        document.querySelectorAll('.reviews-link[data-employer-id="' + employerId + '"] .reviews-count').forEach(function (el) {
            el.textContent = data.upvotes + data.downvotes;
        });
    }

    function openCompanyReviewModal(employerId, voteType, existingBody) {
        crmEmployerId = employerId;
        crmVoteType = voteType;
        document.getElementById('crm-title').textContent = voteType === 'upvote'
            ? 'Thanks for the upvote! Want to add a review?'
            : 'Thanks for the feedback — want to explain why?';
        document.getElementById('crm-review-body').value = existingBody || '';
        document.getElementById('companyReviewModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCompanyReviewModal() {
        document.getElementById('companyReviewModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function submitCompanyReview(e) {
        e.preventDefault();
        const body = document.getElementById('crm-review-body').value;
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        fetch(COMPANY_VOTE_BASE_URL + '/' + crmEmployerId + '/vote', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': companyVoteCsrfToken(),
            },
            body: 'vote=' + encodeURIComponent(crmVoteType) + '&review_body=' + encodeURIComponent(body),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                updateCompanyVoteUI(crmEmployerId, data);
                closeCompanyReviewModal();
            })
            .finally(function () { submitBtn.disabled = false; });
    }

    window.addEventListener('click', function (event) {
        if (event.target === document.getElementById('companyReviewModal')) closeCompanyReviewModal();
    });
</script>
