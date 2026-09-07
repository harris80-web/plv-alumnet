{{--
    Floating error toast — the red counterpart to partials/success.blade.php,
    same position/animation/auto-dismiss, so a failed action (e.g. CSV
    import) reads consistently with a successful one instead of falling
    back to a static inline box.
--}}
@if ($errors->any())

    <div id="errorToast" class="fixed inset-0 z-[300] flex items-start justify-center pt-6 pointer-events-none">
        <div id="errorBox"
            class="pointer-events-auto flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 shadow-md transition-all duration-300 ease-out max-w-md w-full mx-4 opacity-0 -translate-y-2">
            <i class="fas fa-circle-exclamation text-red-500 text-base shrink-0 mt-0.5"></i>
            <ul class="text-sm font-medium flex-1 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button onclick="closeErrorToast()" class="text-red-400 hover:text-red-600 transition-colors shrink-0">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>

    <script>
        (function () {
            const box = document.getElementById('errorBox');
            // Force a layout flush before removing the hidden-state classes,
            // otherwise the browser can collapse both changes into one frame
            // and the toast just snaps in instead of fading/sliding in.
            box.getBoundingClientRect();
            requestAnimationFrame(() => box.classList.remove('opacity-0', '-translate-y-2'));
        })();

        function closeErrorToast() {
            const box = document.getElementById('errorBox');
            box.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => document.getElementById('errorToast')?.remove(), 300); // matches duration-300 above
        }

        setTimeout(closeErrorToast, 8000); // auto-dismiss after 8 seconds — longer than success since there's more to read
    </script>

@endif
