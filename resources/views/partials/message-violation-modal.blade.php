{{--
    Floating warning shown the moment MessageController::store() reports a
    just-sent message was flagged by MessageAuditor (see its REASONS/
    reasonLabels on App\Models\MessageFlag). The message still goes through —
    flagging only queues it for admin review — this just tells the sender
    why, right away, instead of them only finding out if/when an admin acts
    on it. Styled after partials/messaging-guidelines-modal.blade.php (same
    icon-circle/gradient-title/single-button shape, same shared open/close
    animation from partials/ui-animations.blade.php), swapped to a red/
    warning tone since this is a "you did something" notice, not a
    "here's how this works" one.
--}}
<div id="messageViolationModal" class="fixed inset-0 z-[300] hidden opacity-0 transition-opacity duration-200 bg-black/50 flex items-center justify-center p-4">
    <div id="messageViolationModalPanel" class="bg-white rounded-lg shadow-xl p-8 max-w-lg w-full relative text-center opacity-0 scale-95 transition-all duration-200">
        <button type="button" onclick="closeMessageViolationModal()" class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors">
            <i class="fa-solid fa-circle-xmark text-2xl"></i>
        </button>

        <div class="flex justify-center mb-6">
            <div class="bg-red-100 rounded-full p-4">
                <i class="fa-solid fa-triangle-exclamation text-red-600 text-2xl"></i>
            </div>
        </div>

        <h2 class="text-2xl font-bold text-red-600 mb-2">
            Message Flagged for Review
        </h2>
        <p class="text-gray-500 text-sm mb-6 leading-relaxed">
            Your message was sent, but it was automatically flagged because it may violate our messaging guidelines.
        </p>

        <div class="text-left space-y-2 bg-red-50 border border-red-100 rounded-xl p-5 mb-6">
            <p class="text-xs font-bold text-red-700 uppercase tracking-wide mb-2">Detected concern(s)</p>
            <ul id="messageViolationReasonsList" class="space-y-1.5"></ul>
        </div>

        <p class="text-xs text-gray-400 mb-6 leading-relaxed">
            Conversations may be reviewed by admins to keep everyone safe, in line with the Data Privacy Act. Repeated violations may result in restricted messaging access.
        </p>

        <button type="button" onclick="closeMessageViolationModal()"
            class="w-full bg-red-600 text-white py-3 rounded-md font-bold hover:bg-red-700 transition-colors uppercase tracking-wider">
            I Understand
        </button>
    </div>
</div>

<script>
    // reasonLabels come from MessageFlag::reasonLabels() — a fixed,
    // server-defined whitelist, never raw user input — so building this
    // list via innerHTML carries no XSS risk.
    window.openMessageViolationModal = function (reasonLabels) {
        const list = document.getElementById('messageViolationReasonsList');
        const items = (reasonLabels && reasonLabels.length) ? reasonLabels : ['Potential guideline violation'];
        list.innerHTML = items.map(function (label) {
            return '<li class="flex items-center gap-2 text-sm text-red-700">' +
                '<i class="fa-solid fa-circle-exclamation text-red-500 text-xs"></i> ' + label +
                '</li>';
        }).join('');

        openAnimatedModal(
            document.getElementById('messageViolationModal'),
            document.getElementById('messageViolationModalPanel')
        );
    };

    window.closeMessageViolationModal = function () {
        closeAnimatedModal(
            document.getElementById('messageViolationModal'),
            document.getElementById('messageViolationModalPanel')
        );
    };
</script>
