{{--
    Floating toast for a single flashed session('error') string (e.g.
    `back()->with('error', 'Hiring limit reached...')`) — the counterpart to
    partials/success.blade.php. Distinct element ids from
    partials/error-toast.blade.php (which renders $errors->any(), Laravel's
    validation bag) on purpose: a page can legitimately have both a flashed
    error AND validation errors at once, and sharing ids would break one
    toast's close button/auto-dismiss via duplicate-id lookups.
--}}
@if(session('error'))

    <div id="sessionErrorToast" class="fixed inset-0 z-[300] flex items-start justify-center pt-6 pointer-events-none">
        <div id="sessionErrorBox"
            class="pointer-events-auto flex items-center gap-3 bg-red-50 border border-red-200 text-[#C73D1A] rounded-xl px-5 py-4 shadow-md transition-all duration-300 ease-out max-w-md w-full mx-4 opacity-0 -translate-y-2">
            <i class="fas fa-circle-exclamation text-red-500 text-base shrink-0"></i>
            <p class="text-sm font-medium flex-1">{{ session('error') }}</p>
            <button onclick="closeSessionErrorToast()" class="text-red-400 hover:text-red-600 transition-colors shrink-0">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>

    <script>
        (function () {
            const box = document.getElementById('sessionErrorBox');
            // Force a layout flush before removing the hidden-state classes,
            // otherwise the browser can collapse both changes into one frame
            // and the toast just snaps in instead of fading/sliding in.
            box.getBoundingClientRect();
            requestAnimationFrame(() => box.classList.remove('opacity-0', '-translate-y-2'));
        })();

        function closeSessionErrorToast() {
            const box = document.getElementById('sessionErrorBox');
            box.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => document.getElementById('sessionErrorToast')?.remove(), 300); // matches duration-300 above
        }

        setTimeout(closeSessionErrorToast, 7000); // slightly longer than the success toast — an error is worth reading
    </script>

@endif
