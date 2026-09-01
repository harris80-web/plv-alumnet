{{--
    Shared styled "are you sure?" modal — replaces the browser's native
    confirm()/alert() dialogs. One instance per page; call
    openConfirmModal({title, message, iconName, iconBg, iconColor, btnBg,
    btnText, onConfirm}) from any button instead of confirm(...).
    Originated in superAdmin/testimonialManagement.blade.php; extracted here
    so every management page can share the exact same look and behavior
    instead of each hand-rolling (or skipping) its own version.
--}}
<div id="confirmModal"
    class="fixed inset-0 z-[100] flex items-center justify-center invisible transition-all duration-300">
    <div class="absolute inset-0 bg-[#0E0F3B]/40 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
    <div id="confirmContent"
        class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300">
        <div class="p-8 text-center">
            <div id="confirmIconContainer"
                class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <i id="confirmIcon" data-lucide="alert-triangle" class="w-8 h-8"></i>
            </div>
            <h3 id="confirmTitle" class="text-[#0E0F3B] text-xl font-bold mb-2">Confirmation</h3>
            <p id="confirmMessage" class="text-slate-500 text-sm leading-relaxed">Are you sure you want to proceed?
            </p>
        </div>
        <div class="px-8 pb-8 flex gap-3">
            <button onclick="closeConfirmModal()"
                class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                Cancel
            </button>
            <button id="confirmYesBtn"
                class="flex-1 py-2.5 text-white rounded-lg text-xs font-bold transition-all uppercase hover:brightness-110">
                Yes, Proceed
            </button>
        </div>
    </div>
</div>

<script>
    /* ── Confirm Modal ────────────────────────────────── */
    function openConfirmModal({
        title,
        message,
        iconName,
        iconBg,
        iconColor,
        btnBg,
        btnText,
        onConfirm
    }) {
        const modal = document.getElementById('confirmModal');
        const content = document.getElementById('confirmContent');
        document.getElementById('confirmTitle').innerText = title;
        document.getElementById('confirmMessage').innerHTML = message;
        document.getElementById('confirmIconContainer').className =
            `w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 ${iconBg}`;
        const icon = document.getElementById('confirmIcon');
        icon.setAttribute('data-lucide', iconName);
        icon.className = `w-8 h-8 ${iconColor}`;
        const yesBtn = document.getElementById('confirmYesBtn');
        yesBtn.className = `flex-1 py-2.5 ${btnBg} text-white rounded-lg text-xs font-bold transition-all uppercase hover:brightness-110`;
        yesBtn.innerText = btnText;
        yesBtn.onclick = () => {
            onConfirm();
            closeConfirmModal();
        };
        if (window.lucide) lucide.createIcons();
        modal.classList.remove('invisible');
        setTimeout(() => content.classList.remove('scale-95'), 10);
    }

    function closeConfirmModal() {
        const content = document.getElementById('confirmContent');
        content.classList.add('scale-95');
        setTimeout(() => document.getElementById('confirmModal').classList.add('invisible'), 200);
    }
</script>
