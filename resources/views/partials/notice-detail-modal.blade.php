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
<div id="noticeDetailModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden mx-4 max-h-[90vh] overflow-y-auto">
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

    function openNoticeDetailModal(card) {
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
            const isInterested = data.interested === '1';
            const btn = document.getElementById('ndm-interest-btn');
            btn.className = 'w-full text-sm font-bold uppercase py-2.5 rounded-lg transition-colors ' +
                (isInterested ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-[#1D264F] hover:bg-[#0E0F3B] text-white');
            btn.innerHTML = '<i class="fa-solid ' + (isInterested ? 'fa-circle-check' : 'fa-star') + ' mr-1"></i> ' +
                (isInterested ? "You're Interested" : 'Interested');
        } else {
            interestRow.classList.add('hidden');
        }

        document.getElementById('noticeDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeNoticeDetailModal() {
        document.getElementById('noticeDetailModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    window.addEventListener('click', function (event) {
        if (event.target === document.getElementById('noticeDetailModal')) closeNoticeDetailModal();
    });
</script>
