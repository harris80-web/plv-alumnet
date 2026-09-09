{{--
    Site-wide replacement for the native window.alert() popup — included
    once per top-level header (header-alumni, header-employer,
    super-admin-header) so every page gets this before any page script runs.
    Overrides window.alert itself, so every existing `alert('message')` call
    anywhere in the app (inline onclick, page scripts, shared partials like
    rich-text-editor/confirm-modal/chatbot-widget) automatically renders as
    this modal instead of a blocking browser popup — no call site needs to
    change.
--}}
<div id="siteAlertModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7 text-center">
        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-circle-info text-[#1D46A4] text-xl"></i>
        </div>
        <p id="siteAlertModalMessage" class="text-sm text-[#0E0F3B] font-medium mb-6 whitespace-pre-line"></p>
        <button type="button" onclick="closeSiteAlertModal()"
            class="w-full bg-[#0E0F3B] text-white py-2.5 rounded-lg font-bold text-sm hover:bg-blue-900 transition-colors uppercase tracking-wide">
            OK
        </button>
    </div>
</div>
<script>
    (function () {
        // Guards against double-binding if this partial is ever included
        // more than once on the same page.
        if (window.__siteAlertModalBound) return;
        window.__siteAlertModalBound = true;

        window.showAlertModal = function (message) {
            const modal = document.getElementById('siteAlertModal');
            const msgEl = document.getElementById('siteAlertModalMessage');
            if (!modal || !msgEl) return;
            msgEl.textContent = message;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        };

        window.closeSiteAlertModal = function () {
            const modal = document.getElementById('siteAlertModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        };

        // The actual override — every alert('...') call in the app now
        // opens this modal instead of the browser's native popup.
        window.alert = window.showAlertModal;

        document.getElementById('siteAlertModal')?.addEventListener('click', function (e) {
            if (e.target === this) window.closeSiteAlertModal();
        });
    })();
</script>
