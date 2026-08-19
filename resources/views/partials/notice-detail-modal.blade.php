{{--
    Shared read-only "notice details" modal for the alumni-facing Events &
    Seminars and Announcements pages — each card carries its data as
    data-* attributes (see openNoticeDetailModal below), so one modal
    instance serves every card on the page without a Quill-style
    per-record re-render problem (this view never edits, only displays).

    A card includes data-toggle-url only when it can be "Interested" in
    (events/seminars) — its absence is how this modal knows to hide that
    section entirely (announcements).
--}}
<div id="noticeDetailModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div id="noticeDetailModalPanel" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden mx-4 max-h-[90vh] overflow-y-auto opacity-0 scale-95 transition-all duration-200">
        <div class="relative h-48 w-full">
            <img id="ndm-thumbnail" src="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-[#0E0F3B]/30"></div>
            <span id="ndm-category" class="absolute top-4 left-4 text-[10px] font-bold uppercase px-3 py-1 rounded-full"></span>
            <button type="button" onclick="closeNoticeDetailModal()"
                class="absolute top-4 right-4 bg-white/20 hover:bg-white/40 text-white rounded-full p-1 transition-colors">
                <i class="fas fa-times-circle text-2xl"></i>
            </button>
        </div>

        <div class="p-6 space-y-4 text-sm">
            <h2 id="ndm-title" class="text-2xl font-bold text-[#0E0F3B] leading-snug"></h2>

            <p class="text-gray-500 flex items-center gap-2">
                <i class="fa-regular fa-calendar"></i> <span id="ndm-datetime"></span>
            </p>

            <p id="ndm-speaker-row" class="hidden text-gray-500 flex items-center gap-2">
                <i class="fa-solid fa-microphone"></i> <span id="ndm-speaker"></span>
            </p>

            <p id="ndm-location-row" class="hidden text-gray-500 flex items-center gap-2">
                <i class="fa-solid fa-location-dot"></i> <span id="ndm-location"></span>
            </p>

            <div class="pt-2 border-t border-gray-100">
                <div id="ndm-description" class="notice-description-content text-gray-600 leading-relaxed"></div>
            </div>

            <div id="ndm-interest-row" class="hidden pt-4 border-t border-gray-100">
                <form id="ndm-interest-form" method="POST">
                    @csrf
                    <button type="submit" id="ndm-interest-btn"
                        class="w-full text-sm font-bold uppercase py-2.5 rounded-lg transition-colors">
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===== "Interested" confirmation — shown instantly after either the card-grid
     or this modal's own Interested button is clicked, no page reload needed ===== --}}
<div id="interestConfirmModal" class="fixed inset-0 z-[80] hidden opacity-0 transition-opacity duration-200 bg-black/60 flex items-center justify-center p-4">
    <div id="interestConfirmModalPanel" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden opacity-0 scale-95 transition-all duration-200 text-center p-8">
        <div id="interestConfirmIcon" class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
            <i id="interestConfirmIconGlyph" class="fa-solid text-2xl"></i>
        </div>
        <p id="interestConfirmText" class="text-[#0E0F3B] font-semibold"></p>
        <button type="button"
            onclick="closeAnimatedModal(document.getElementById('interestConfirmModal'), document.getElementById('interestConfirmModalPanel'))"
            class="mt-6 text-xs font-bold uppercase tracking-widest text-white bg-[#0E0F3B] hover:bg-[#1D46A4] px-8 py-2.5 rounded-full transition-colors">
            Close
        </button>
    </div>
</div>

<script>
    const noticeCategoryLabels = {
        event: 'Event',
        seminar: 'Seminar',
        announcement: 'Announcement',
    };
    const noticeCategoryBadgeClasses = {
        event: 'bg-blue-100 text-blue-700',
        seminar: 'bg-purple-100 text-purple-700',
        announcement: 'bg-amber-100 text-amber-700',
    };

    let currentNoticeCard = null;

    function toggleInterestState(btn, isInterested) {
        // Card-grid and modal buttons share the same conditional classes,
        // just a different base text size — keep whichever it already has.
        const sizeClass = btn.classList.contains('text-sm') ? 'text-sm' : 'text-xs';
        btn.className = 'w-full ' + sizeClass + ' font-bold uppercase py-2.5 rounded-lg transition-colors ' +
            (isInterested ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-[#1D264F] hover:bg-[#0E0F3B] text-white');
        btn.innerHTML = '<i class="fa-solid ' + (isInterested ? 'fa-circle-check' : 'fa-star') + ' mr-1"></i> ' +
            (isInterested ? "You're Interested" : 'Interested');
    }

    function showInterestConfirmation(isInterested, title) {
        const icon = document.getElementById('interestConfirmIcon');
        const glyph = document.getElementById('interestConfirmIconGlyph');
        icon.className = 'w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4 ' + (isInterested ? 'bg-green-100' : 'bg-slate-100');
        glyph.className = 'fa-solid text-2xl ' + (isInterested ? 'fa-circle-check text-green-600' : 'fa-circle-xmark text-slate-400');

        // Built via textContent/DOM nodes rather than innerHTML — the notice
        // title is admin-entered content, so this avoids ever interpreting
        // it as markup.
        const textEl = document.getElementById('interestConfirmText');
        textEl.textContent = '';
        const strong = document.createElement('strong');
        strong.textContent = title;
        if (isInterested) {
            textEl.append("You're interested in ", strong, '!');
        } else {
            textEl.append('Marked ', strong, ' as not interested.');
        }

        const confirmOverlay = document.getElementById('interestConfirmModal');
        const confirmPanel = document.getElementById('interestConfirmModalPanel');
        openAnimatedModal(confirmOverlay, confirmPanel);
        setTimeout(function () { closeAnimatedModal(confirmOverlay, confirmPanel); }, 2500);
    }

    function submitInterestToggle(form, sourceCard) {
        const btn = form.querySelector('button[type="submit"]');
        const csrfInput = form.querySelector('input[name="_token"]');

        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfInput.value },
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (sourceCard) sourceCard.dataset.interested = data.interested ? '1' : '0';
                toggleInterestState(btn, data.interested);
                showInterestConfirmation(data.interested, data.title);
            });
    }

    document.getElementById('ndm-interest-form').addEventListener('submit', function (e) {
        e.preventDefault();
        submitInterestToggle(this, currentNoticeCard);
    });

    function openNoticeDetailModal(card) {
        currentNoticeCard = card;
        const data = card.dataset;

        document.getElementById('ndm-thumbnail').src = data.thumbnail;

        const categoryBadge = document.getElementById('ndm-category');
        categoryBadge.textContent = noticeCategoryLabels[data.category] ?? data.category;
        categoryBadge.className = 'absolute top-4 left-4 text-[10px] font-bold uppercase px-3 py-1 rounded-full ' + (noticeCategoryBadgeClasses[data.category] ?? 'bg-slate-100 text-slate-500');

        document.getElementById('ndm-title').textContent = data.title;
        document.getElementById('ndm-datetime').textContent = data.datetime;
        document.getElementById('ndm-description').innerHTML = data.description || '<p class="text-gray-400">No description provided.</p>';

        const locationRow = document.getElementById('ndm-location-row');
        if (data.location) {
            locationRow.classList.remove('hidden');
            document.getElementById('ndm-location').textContent = data.location;
        } else {
            locationRow.classList.add('hidden');
        }

        const speakerRow = document.getElementById('ndm-speaker-row');
        if (data.category === 'seminar' && data.speakerName) {
            speakerRow.classList.remove('hidden');
            document.getElementById('ndm-speaker').textContent = data.speakerName + (data.speakerTopic ? ' — ' + data.speakerTopic : '');
        } else {
            speakerRow.classList.add('hidden');
        }

        const interestRow = document.getElementById('ndm-interest-row');
        if (data.toggleUrl) {
            interestRow.classList.remove('hidden');
            document.getElementById('ndm-interest-form').action = data.toggleUrl;
            toggleInterestState(document.getElementById('ndm-interest-btn'), data.interested === '1');
        } else {
            interestRow.classList.add('hidden');
        }

        openAnimatedModal(document.getElementById('noticeDetailModal'), document.getElementById('noticeDetailModalPanel'));
    }

    function closeNoticeDetailModal() {
        closeAnimatedModal(document.getElementById('noticeDetailModal'), document.getElementById('noticeDetailModalPanel'));
    }

    window.addEventListener('click', function (event) {
        if (event.target === document.getElementById('noticeDetailModal')) closeNoticeDetailModal();
    });
</script>
