@if(session('success'))

    <div id="successToast" class="fixed inset-0 z-[300] flex items-start justify-center pt-6 pointer-events-none">
        <div id="successBox"
            class="pointer-events-auto flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-4 shadow-md transition-all duration-500 max-w-md w-full mx-4">
            <i class="fas fa-circle-check text-green-500 text-base shrink-0"></i>
            <p class="text-sm font-medium flex-1">{{ session('success') }}</p>
            <button onclick="closeSuccessToast()" class="text-green-400 hover:text-green-600 transition-colors shrink-0">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>

    <script>
        function closeSuccessToast() {
            const toast = document.getElementById('successToast');
            toast.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => toast.remove(), 500); // wait for fade-out animation to finish
        }

        setTimeout(closeSuccessToast, 5000); // auto-dismiss after 5 seconds
    </script>

@endif