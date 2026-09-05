{{--
    "Before You Start Messaging" reminder - shows every time this account
    visits the Messaging page, unless they've checked "don't remind me
    again" (tracked client-side via localStorage, keyed per user id so a
    shared browser/device can't skip it for someone else). Styled after
    partials/post-job-modal.blade.php's #pendingModal /
    partials/job-apply-modal.blade.php's #jobApplySuccessModal (icon circle,
    gradient title, gradient supporting line, single full-width action
    button), plus a guideline list for the extra content this notice needs
    to carry. Uses the shared open/close animation from
    partials/ui-animations.blade.php (already loaded by header-alumni).
--}}
<div id="messagingGuidelinesModal" class="fixed inset-0 z-[300] hidden opacity-0 transition-opacity duration-200 bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div id="messagingGuidelinesModalPanel" class="bg-white rounded-lg shadow-xl p-8 max-w-lg w-full relative text-center opacity-0 scale-95 transition-all duration-200">
        <button type="button" onclick="closeMessagingGuidelinesModal()" class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors">
            <i class="fa-solid fa-circle-xmark text-2xl"></i>
        </button>

        <div class="flex justify-center mb-6">
            <div class="bg-orange-100 rounded-full p-4">
                <i class="fa-solid fa-comments text-[#C73D1A] text-2xl"></i>
            </div>
        </div>

        <h2 class="text-2xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent mb-2">
            Before You Start Messaging
        </h2>
        <p class="text-gray-500 text-sm mb-6 leading-relaxed">
            This space is for real alumni networking. Please keep conversations respectful and on topic.
        </p>

        <div class="text-left space-y-3 bg-slate-50 border border-slate-100 rounded-xl p-5 mb-6">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-green-600 mt-0.5 shrink-0"></i>
                <p class="text-sm text-gray-600">Use this for networking, job leads, mentorship, and school-related conversations.</p>
            </div>
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-lock text-[#1D46A4] mt-0.5 shrink-0"></i>
                <p class="text-sm text-gray-600">Conversations may be reviewed by admins to keep everyone safe, in line with the Data Privacy Act.</p>
            </div>
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-[#C73D1A] mt-0.5 shrink-0"></i>
                <p class="text-sm text-gray-600">Please avoid spam, unnecessary messages, or sharing sensitive personal details.</p>
            </div>
        </div>

        <button type="button" onclick="closeMessagingGuidelinesModal()"
            class="w-full bg-[#0E0F3B] text-white py-3 rounded-md font-bold hover:bg-blue-900 transition-colors uppercase tracking-wider">
            I Understand
        </button>

        <label class="flex items-center justify-center gap-2 mt-4 text-xs text-gray-500 cursor-pointer">
            <input type="checkbox" id="messagingGuidelinesDontRemind" class="accent-[#0E0F3B] w-3.5 h-3.5">
            I understand and don't remind me again
        </label>
    </div>
</div>

<script>
    (function () {
        // Keyed per user id, not just a flat flag, so a shared browser or
        // device logging in as someone else still gets the notice once.
        const storageKey = 'plv_messaging_guidelines_seen_{{ Auth::id() }}';

        document.addEventListener('DOMContentLoaded', function () {
            let seen = false;
            try {
                seen = localStorage.getItem(storageKey) === '1';
            } catch (e) {
                // Storage blocked (private mode, disabled site data, etc.)
                // Fail open and just show the notice instead of erroring.
            }

            if (!seen) {
                openAnimatedModal(
                    document.getElementById('messagingGuidelinesModal'),
                    document.getElementById('messagingGuidelinesModalPanel')
                );
            }
        });

        window.closeMessagingGuidelinesModal = function () {
            closeAnimatedModal(
                document.getElementById('messagingGuidelinesModal'),
                document.getElementById('messagingGuidelinesModalPanel')
            );

            // Only suppress future reminders if they actually checked the
            // box - closing (X or "I Understand") on its own still shows
            // the notice again next visit.
            const dontRemind = document.getElementById('messagingGuidelinesDontRemind');
            if (dontRemind && dontRemind.checked) {
                try {
                    localStorage.setItem(storageKey, '1');
                } catch (e) {
                    // Nothing to persist to - the modal just shows again
                    // next visit, which is fine as a fallback.
                }
            }
        };
    })();
</script>
