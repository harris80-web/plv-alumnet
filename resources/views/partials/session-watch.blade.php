{{--
    Included by every logged-in header. Asks the server once a minute (and
    whenever the tab regains focus) who this browser is signed in as. The
    check itself counts as activity, so the session stays alive while a page
    is open. If the answer is no longer the account this page was rendered
    for — the session ended, or another account signed in on this browser —
    the page is blocked by a modal whose only way out is the login page,
    instead of carrying on with stale content until a click hits a 403.
--}}
@auth
@once
<div id="sessionExpiredModal" class="hidden fixed inset-0 flex items-center justify-center bg-black/70 p-4" style="z-index: 2147483647;" role="alertdialog" aria-modal="true" aria-labelledby="sessionExpiredTitle">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 text-center">
        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-clock text-amber-600 text-2xl"></i>
        </div>
        <h2 id="sessionExpiredTitle" class="text-xl font-bold text-[#0E0F3B] mb-2">Session Expired</h2>
        <p class="text-gray-500 text-sm mb-6">Your session has ended. Please log in again to continue.</p>
        <a id="sessionExpiredLogin" href="{{ route('auth.login') }}" class="block w-full bg-[#0E0F3B] text-white py-3 rounded-lg font-bold text-sm hover:bg-[#1D46A4] transition-colors uppercase tracking-wider">
            Go to Login
        </a>
    </div>
</div>
<script>
    (function () {
        const STATUS_URL = @json(route('session.status'));
        const PAGE_USER_ID = @json(auth()->id());
        let ended = false;
        let timer = null;

        function blockPage() {
            if (ended) return;
            ended = true;
            clearInterval(timer);
            const modal = document.getElementById('sessionExpiredModal');
            // Moved out of <body> so everything else can be made inert: no
            // clicking, typing or tabbing behind the modal.
            document.documentElement.appendChild(modal);
            document.body.inert = true;
            modal.classList.remove('hidden');
            document.getElementById('sessionExpiredLogin').focus();
        }

        async function check() {
            if (ended) return;
            try {
                const res = await fetch(STATUS_URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                if (!res.ok) return; // server hiccup, not proof the session ended
                const data = await res.json();
                if (data.userId !== PAGE_USER_ID) blockPage();
            } catch (e) { /* offline — try again on the next tick */ }
        }

        timer = setInterval(check, 60000);
        document.addEventListener('visibilitychange', function () { if (!document.hidden) check(); });
        window.addEventListener('focus', check);
        window.addEventListener('pageshow', check);
    })();
</script>
@endonce
@endauth
