<?php
$currentPage = basename(request()->path()) . '.blade.php';

// "Super Admin" only for an actual super_admin — a plain `admin` viewing
// their own profile/this header shouldn't see a title implying a role they
// don't have.
$roleLabel = (auth()->user()?->user_role === 'super_admin') ? 'Super Admin' : 'Admin';

$pageTitles = [
    'dashboard.blade.php'       => 'Dashboard',
    'profile.blade.php'         => $roleLabel . ' Profile',
    'userManagement.blade.php' => 'User Management',
    'jobManagement.blade.php'   => 'Job Placement Management',
    'alumniIdManagement.blade.php'     => 'Alumni ID & Yearbook Management',
    'notices.blade.php'  => 'Notices & Events',
    'chatbotMessaging.blade.php' => 'Chatbot & Messaging',
    'testimonialManagement.blade.php'    => 'Testimonial Management',
    'faqManagement.blade.php'            => 'Manage FAQs'
];

// notifications.all's URL (/notifications/all) doesn't fit the single-segment
// "basename of the path" lookup every other page above uses (it'd resolve to
// the ambiguous key "all.blade.php"), so it's matched by route name instead.
$title = request()->routeIs('notifications.all')
    ? 'Notifications'
    : ($pageTitles[$currentPage] ?? $roleLabel . ' Panel');
?>

<style>
    /* Thin, visible scrollbar for the notification dropdown's list — same
       treatment as .chat-scroll in alumni/messages.blade.php and
       #notificationList in partials/user-sidebar.blade.php, since the
       site-wide reset hides default scrollbars on html/body but this inner
       list still needs a visible one so it's clear there's more to scroll. */
    #notif-list::-webkit-scrollbar {
        width: 6px;
    }

    #notif-list::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 3px;
    }

    #notif-list {
        scrollbar-width: thin;
        scrollbar-color: #e2e8f0 transparent;
    }

    /* Bell & Settings button base */
    .icon-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Resting icon color */
    .icon-btn svg {
        stroke: #0E0F3B;
        transition: stroke 0.2s ease, transform 0.2s ease;
    }

    /* Active state: filled look via stroke color change + scale */
    .icon-btn.active svg {
        stroke: #C73D1A;
        transform: scale(1.15);
    }

    /* Smooth dropdown fade-in */
    #notification-menu,
    #header-settings-menu {
        transform: translateY(-6px);
        opacity: 0;
        transition: opacity 0.18s ease, transform 0.18s ease;
        pointer-events: none;
    }

    #notification-menu.open,
    #header-settings-menu.open {
        transform: translateY(0);
        opacity: 1;
        pointer-events: auto;
    }
</style>

<!-- Error Toast Container -->
<div id="toastContainer" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2 w-[90%] max-w-sm pointer-events-none"></div>

<header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center shrink-0 shadow-md z-10">

    <h1 class="text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
        <?php echo $title; ?>
    </h1>

    <div class="flex items-center gap-3 relative">

        <!--  Notifications -->
        <button id="notif-btn"
            class="icon-btn relative p-2 rounded-full hover:bg-slate-100 transition">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span id="notif-badge"
                class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-[10px] px-1.5 rounded-full">
                0
            </span>
        </button>

        <div id="notification-menu"
            class="hidden absolute right-10 top-14 w-80 bg-white rounded-xl shadow-2xl border-b border-slate-200 z-50 overflow-hidden">

            <div class="flex items-center px-4 py-3 text-sm font-semibold border-b border-slate-200">
                <span class="flex-1 text-[#0E0F3B]">Notifications</span>
                <button type="button" onclick="markAllNotificationsRead()" class="text-[10px] font-semibold text-[#0E0F3B] hover:text-[#ED7A07] transition-colors mr-2">
                    Mark all as read
                </button>
                <button onclick="closeAllMenus()"
                    class="p-1 rounded hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-4 h-4 text-slate-500"></i>
                </button>
            </div>

            <div class="flex border-b border-slate-200 text-xs font-bold">
                <button type="button" onclick="setNotifTab('all')" data-notif-tab-btn="all"
                    class="flex-1 py-2 text-center border-b-2 transition-colors border-[#ED7A07] text-[#ED7A07]">
                    All
                </button>
                <button type="button" onclick="setNotifTab('unread')" data-notif-tab-btn="unread"
                    class="flex-1 py-2 text-center border-b-2 transition-colors border-transparent text-slate-400 hover:text-[#0E0F3B]">
                    Unread
                </button>
            </div>

            <div id="notif-list" class="max-h-64 overflow-y-auto text-sm"></div>

            <div id="notif-empty"
                class="flex flex-col items-center justify-center py-10 text-slate-400">
                <i data-lucide="bell" class="w-8 h-8 mb-2"></i>
                <span class="text-sm font-medium">No notifications</span>
                <span class="text-xs text-slate-400">You're all caught up</span>
            </div>

            <a href="{{ route('notifications.all') }}"
                class="block text-center text-xs py-2 border-t border-slate-200 hover:bg-slate-50 transition">
                View all notifications
            </a>
        </div>

        <!-- Settings -->
        <button id="settings-btn"
            class="icon-btn p-2 rounded-full hover:bg-slate-100 transition">
            <i data-lucide="settings" class="w-5 h-5"></i>
        </button>

        <div id="header-settings-menu"
            class="hidden absolute right-0 top-14 w-56 bg-white rounded-xl shadow-2xl border border-slate-100 z-50 overflow-hidden">

            <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-200">
                <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center">
                    <i data-lucide="user" class="w-5 h-5 text-slate-500"></i>
                </div>
                <a href="{{ route('superAdmin.profile') }}" class="block">
                    <div class="hover:bg-slate-50 px-2 py-1 rounded-md transition cursor-pointer">
                        <div class="text-sm font-semibold text-slate-700">{{ auth()->user()->user_first_name }} {{ auth()->user()->user_last_name }}</div>
                        <div class="text-xs text-slate-400">View Profile</div>
                    </div>
                </a>
            </div>

            <a href="javascript:void(0)" id="pw-modal-link"
                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                <i data-lucide="lock" class="w-4 h-4"></i>
                Change Password
            </a>

            <form id="logout-form" method="POST" action="{{ route('user.logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 w-full">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Log out
                </button>
            </form>
        </div>
    </div>
    <!-- LOGOUT MODAL -->
    <div id="logout-modal" class="fixed inset-0 z-[9999] bg-black/50 hidden items-center justify-center">
        <div class="bg-white w-[350px] rounded-2xl shadow-2xl p-6 text-center animate-fadeIn">

            <h2 class="text-lg font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent inline-block">
                Log out Confirmation
            </h2>

            <p class="text-sm text-[#0E0F3B] mt-2">
                Are you sure you want to log out?
            </p>

            <div class="flex justify-center gap-3 mt-6">
                <button id="cancel-logout"
                    class="px-4 py-2 rounded-lg border text-[#0E0F3B] border-[#0E0F3B] text-sm hover:bg-[#0E0F3B] hover:text-white transition">
                    CANCEL
                </button>

                <button id="confirm-logout"
                    class="px-4 py-2 rounded-lg bg-[#0E0F3B] text-white text-sm hover:bg-[#1a1c5a] transition">
                    CONFIRM
                </button>
            </div>

        </div>
    </div>
    
</header>

<!-- CHANGE PASSWORD MODAL -->
<div id="password-modal" class="fixed inset-0 z-[9999] bg-black/40 hidden items-center justify-center">
    <div class="bg-[#F3F4F6] w-[380px] rounded-[30px] shadow-2xl p-8 relative animate-fadeIn">

        <button id="close-pw-modal" class="absolute top-5 right-6 text-slate-400 hover:text-slate-600 transition">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>

        <div class="text-center mb-5">
            <h2 class="text-2xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent inline-block">
                Change Password
            </h2>
            <p class="text-[11px] text-[#C73D1A] mt-2 font-medium leading-tight">
                Your new password must be different from previously used passwords.
            </p>
        </div>

        <form id="change-password-form" method="POST" action="{{ route('users.changePassword') }}" class="space-y-4 text-left">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-[#0E0F3B] text-xs font-bold mb-1 ml-1">Old Password:</label>
                <div class="relative">
                    <input type="password" id="old_password" name="current_password" required
                        class="w-full pl-3 pr-10 py-2 text-sm rounded-xl border focus:border-[#ED7A07] outline-none shadow-sm bg-white {{ $errors->has('current_password') ? 'border-red-600' : 'border-[#ED7A07]/30' }}">
                    <button type="button" onclick="togglePassword('old_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <i id="old_password_icon" data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-[#0E0F3B] text-xs font-bold mb-1 ml-1">New Password:</label>
                <div class="relative">
                    <input type="password" id="new_password" name="new_password" required
                        class="w-full pl-3 pr-10 py-2 text-sm rounded-xl border focus:border-[#ED7A07] outline-none shadow-sm bg-white {{ $errors->has('new_password') ? 'border-red-600' : 'border-[#ED7A07]/30' }}">
                    <button type="button" onclick="togglePassword('new_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <i id="new_password_icon" data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-[9px] text-[#C73D1A] mt-1 ml-1 leading-tight">Minimum 8 characters, including a number and a symbol.</p>
            </div>

            <div>
                <label class="block text-[#0E0F3B] text-xs font-bold mb-1 ml-1">Confirm New Password:</label>
                <div class="relative">
                    <input type="password" id="confirm_password" name="new_password_confirmation" required
                        class="w-full pl-3 pr-10 py-2 text-sm rounded-xl border focus:border-[#ED7A07] outline-none shadow-sm bg-white {{ $errors->has('new_password_confirmation') ? 'border-red-600' : 'border-[#ED7A07]/30' }}">
                    <button type="button" onclick="togglePassword('confirm_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <i id="confirm_password_icon" data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full mt-2 bg-[#0E0F3B] text-white text-xs font-bold py-3 rounded-xl hover:bg-[#1a1c5a] transition-all tracking-wider">
                UPDATE PASSWORD
            </button>
        </form>
    </div>
</div>

@include('partials.ui-animations')
@include('partials.bulk-checkbox')
@include('partials.table-sort')
<script src="https://unpkg.com/lucide@latest"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script>
    // Error toasts (red), populated from server-side validation errors —
    // e.g. wrong current password on the Change Password modal above,
    // which back()'s to whatever admin page this header happened to be on.
    function showToast(message) {
        const container = document.getElementById('toastContainer');

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto flex items-start gap-2 bg-red-50 text-red-700 border border-red-500 text-[12px] font-medium px-4 py-3 rounded-md shadow-lg opacity-0 -translate-y-2 transition-all duration-300 ease-out';

        const text = document.createElement('span');
        text.className = 'flex-1';
        text.textContent = message;

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'text-red-500 hover:text-red-700 leading-none text-lg';
        closeBtn.innerHTML = '&times;';
        closeBtn.onclick = () => dismissToast(toast);

        toast.appendChild(text);
        toast.appendChild(closeBtn);
        container.appendChild(toast);

        toast.getBoundingClientRect();
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', '-translate-y-2');
        });

        setTimeout(() => dismissToast(toast), 8000);
    }

    function dismissToast(toast) {
        if (!toast.isConnected) return;
        toast.classList.add('opacity-0', '-translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }

    @if ($errors->any())
        window.addEventListener('DOMContentLoaded', () => {
            const messages = @json($errors->all());
            messages.forEach(message => showToast(message));
            // The error means the modal's submit failed — reopen it so the
            // admin sees the message in context instead of just a toast.
            document.getElementById('password-modal')?.classList.remove('hidden');
            document.getElementById('password-modal')?.classList.add('flex');
        });
    @endif

    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
        attachHeaderListeners();
        fetchNotifications();
    });

    function attachHeaderListeners() {
        document.getElementById('notif-btn').addEventListener('click', toggleNotifications);
        document.getElementById('settings-btn').addEventListener('click', toggleHeaderSettings);
        window.addEventListener('click', closeAllMenus);
    }

    /* ── Open / close helpers ─────────────────────────────────── */
    function openMenu(menuId) {
        const menu = document.getElementById(menuId);
        menu.classList.remove('hidden');
        requestAnimationFrame(() => menu.classList.add('open'));
    }

    function closeMenu(menuId) {
        const menu = document.getElementById(menuId);
        menu.classList.remove('open');
        menu.addEventListener('transitionend', () => menu.classList.add('hidden'), {
            once: true
        });
    }

    function closeAllMenus() {
        ['notification-menu', 'header-settings-menu'].forEach(id => {
            const menu = document.getElementById(id);
            if (menu && !menu.classList.contains('hidden')) closeMenu(id);
        });
        document.getElementById('notif-btn')?.classList.remove('active');
        document.getElementById('settings-btn')?.classList.remove('active');
    }

    /* ── Toggle Notifications ─────────────────────────────────── */
    function toggleNotifications(e) {
        e.stopPropagation();
        const menu = document.getElementById('notification-menu');
        const isOpen = !menu.classList.contains('hidden');
        closeAllMenus();
        if (!isOpen) {
            openMenu('notification-menu');
            document.getElementById('notif-btn').classList.add('active');
        }
    }

    /* ── Toggle Settings ──────────────────────────────────────── */
    function toggleHeaderSettings(e) {
        e.stopPropagation();
        const menu = document.getElementById('header-settings-menu');
        const isOpen = !menu.classList.contains('hidden');
        closeAllMenus();
        if (!isOpen) {
            openMenu('header-settings-menu');
            document.getElementById('settings-btn').classList.add('active');
        }
    }

    /* ── Notification bell: fetch, render, tabs, click-through ──
       Same NotificationController endpoints/contract as the alumni/employer
       bell (see partials/user-sidebar.blade.php) — this header just renders
       the JSON differently to match its own markup (#notif-list/#notif-badge
       instead of #notificationList/#notifBadge). */
    const NOTIFICATIONS_URL = {!! json_encode(route('notifications.index')) !!};
    const NOTIFICATIONS_MARK_READ_URL = {!! json_encode(route('notifications.markAllRead')) !!};
    const NOTIFICATION_OPEN_URL_TEMPLATE = {!! json_encode(route('notifications.open', ['notification' => '__ID__'])) !!};
    const NOTIF_CSRF_TOKEN = '{{ csrf_token() }}';

    let currentNotifications = [];
    let activeNotifTab = 'all';

    function escapeHtmlForNotif(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function setNotifTab(tab) {
        activeNotifTab = tab;
        document.querySelectorAll('[data-notif-tab-btn]').forEach(btn => {
            const active = btn.dataset.notifTabBtn === tab;
            btn.classList.toggle('border-[#ED7A07]', active);
            btn.classList.toggle('text-[#ED7A07]', active);
            btn.classList.toggle('border-transparent', !active);
            btn.classList.toggle('text-slate-400', !active);
        });
        renderNotifications();
    }

    function renderNotifications() {
        const container = document.getElementById('notif-list');
        const empty = document.getElementById('notif-empty');
        if (!container || !empty) return;

        const list = activeNotifTab === 'unread'
            ? currentNotifications.filter(n => !n.read)
            : currentNotifications;

        const hasItems = list.length > 0;
        container.classList.toggle('hidden', !hasItems);
        empty.classList.toggle('hidden', hasItems);
        empty.classList.toggle('flex', !hasItems);
        if (!hasItems) {
            empty.querySelector('span.text-sm').textContent = activeNotifTab === 'unread' ? "You're all caught up" : 'No notifications';
        }

        container.innerHTML = list.map(n => `
            <a href="${NOTIFICATION_OPEN_URL_TEMPLATE.replace('__ID__', n.id)}" class="flex items-start gap-2 px-4 py-3 hover:bg-slate-50 border-b border-slate-200 ${n.read ? '' : 'bg-blue-50'}">
                ${n.read ? '<span class="w-2 h-2 shrink-0 mt-1.5"></span>' : '<span class="w-2 h-2 rounded-full bg-blue-600 shrink-0 mt-1.5"></span>'}
                <div class="min-w-0 flex-1">
                    <div class="font-medium text-slate-700">${escapeHtmlForNotif(n.title)}</div>
                    <div class="text-xs text-slate-500 mt-0.5">${escapeHtmlForNotif(n.body)}</div>
                    <div class="text-xs text-slate-400 mt-1">${escapeHtmlForNotif(n.timeLabel)}</div>
                </div>
            </a>
        `).join('');
    }

    function updateNotifBadge(count) {
        const badge = document.getElementById('notif-badge');
        if (!badge) return;
        badge.textContent = count > 9 ? '9+' : String(count);
        badge.classList.toggle('hidden', count === 0);
    }

    async function fetchNotifications() {
        try {
            const res = await fetch(NOTIFICATIONS_URL);
            if (!res.ok) return;
            const data = await res.json();
            currentNotifications = data.notifications;
            renderNotifications();
            updateNotifBadge(data.unreadCount);
        } catch (e) { /* transient network hiccup — next poll retries */ }
    }

    async function markAllNotificationsRead() {
        updateNotifBadge(0); // optimistic — next poll confirms
        try {
            await fetch(NOTIFICATIONS_MARK_READ_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': NOTIF_CSRF_TOKEN, 'Accept': 'application/json' },
            });
            fetchNotifications();
        } catch (e) { /* badge will resync on the next regular poll */ }
    }

    let notifPollTimer = setInterval(fetchNotifications, 20000);
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            clearInterval(notifPollTimer);
        } else {
            fetchNotifications();
            notifPollTimer = setInterval(fetchNotifications, 20000);
        }
    });

    //log out confirmation modal js
    // Replace the entire logout modal JS block with this:
    const logoutModal = document.getElementById('logout-modal');
    const logoutForm = document.getElementById('logout-form');
    const confirmBtn = document.getElementById('confirm-logout');
    const cancelBtn = document.getElementById('cancel-logout');

    logoutForm?.querySelector('button[type="submit"]')?.addEventListener('click', function(e) {
        e.preventDefault();
        logoutModal.classList.remove('hidden');
        logoutModal.classList.add('flex');
    });

    confirmBtn?.addEventListener('click', function() {
        logoutForm.submit();
    });

    cancelBtn?.addEventListener('click', function() {
        logoutModal.classList.add('hidden');
        logoutModal.classList.remove('flex');
    });

    logoutModal?.addEventListener('click', function(e) {
        if (e.target === logoutModal) {
            logoutModal.classList.add('hidden');
            logoutModal.classList.remove('flex');
        }
    });

    // Change Password modal
    const pwModalLink = document.getElementById('pw-modal-link');
    const pwModal = document.getElementById('password-modal');
    const closePwBtn = document.getElementById('close-pw-modal');

    if (pwModalLink) {
        pwModalLink.addEventListener('click', function(e) {
            e.preventDefault();
            closeAllMenus();
            pwModal.classList.remove('hidden');
            pwModal.classList.add('flex');
        });
    }

    function hidePwModal() {
        pwModal.classList.add('hidden');
        pwModal.classList.remove('flex');
    }

    closePwBtn?.addEventListener('click', hidePwModal);

    pwModal?.addEventListener('click', function(e) {
        if (e.target === pwModal) hidePwModal();
    });

    document.getElementById('change-password-form')?.addEventListener('submit', function() {
        hidePwModal(); // hide the modal while submitting
    });

    function togglePassword(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '_icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }
</script>