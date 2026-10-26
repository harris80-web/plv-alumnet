{{--
    Optional: @include('partials.success', ['deferred' => true]) keeps the toast hidden
    until the page calls window.showSuccessToast() (e.g. after a modal is dismissed).
    Without it the toast shows on load, as before.
--}}
@php $deferred = $deferred ?? false; @endphp
@if(session('success'))

    <div id="successToast" class="fixed inset-0 z-[300] flex items-start justify-center pt-6 pointer-events-none">
        <div id="successBox"
            class="{{ $deferred ? 'invisible ' : '' }}pointer-events-auto flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-4 shadow-md transition-all duration-300 ease-out max-w-md w-full mx-4 opacity-0 -translate-y-2">
            <i class="fas fa-circle-check text-green-500 text-base shrink-0"></i>
            <p class="text-sm font-medium flex-1">{{ session('success') }}</p>
            <button onclick="closeSuccessToast()" class="text-green-400 hover:text-green-600 transition-colors shrink-0">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>

    <script>
        window.showSuccessToast = function () {
            const box = document.getElementById('successBox');
            if (!box) return;
            box.classList.remove('invisible');
            // Force a layout flush before removing the hidden-state classes,
            // otherwise the browser can collapse both changes into one frame
            // and the toast just snaps in instead of fading/sliding in.
            box.getBoundingClientRect();
            requestAnimationFrame(() => box.classList.remove('opacity-0', '-translate-y-2'));
            setTimeout(closeSuccessToast, 5000); // auto-dismiss after 5 seconds
        };

        function closeSuccessToast() {
            const box = document.getElementById('successBox');
            if (!box) return;
            box.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => document.getElementById('successToast')?.remove(), 300); // matches duration-300 above
        }

        @unless($deferred)
            window.showSuccessToast();
        @endunless
    </script>

@endif