<?php
$currentPage = basename($_SERVER['PHP_SELF']);

$pageTitles = [
    'super_admin_dashboard.php'       => 'Dashboard',
    'super_admin_profile.php'         => 'Profile',
    'super_admin_user_management.php' => 'User Management',
    'super_admin_job_placement.php'   => 'Job Management',
    'super_admin_id_yearbook.php'     => 'Alumni ID & Yearbook',
    'super_admin_notices_events.php'  => 'Notices & Events',
    'super_admin_chatbot_messaging.php' => 'Chatbot & Messaging',
    'super_admin_testimonials.php'    => 'Testimonials',
    'super_admin_faqs.php'            => 'Manage FAQs'
];

$title = $pageTitles[$currentPage] ?? 'Super Admin Panel';
?>

<style>
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

<header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center shrink-0 shadow-md z-10">

    <h1 class="text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
        <?php echo $title; ?>
    </h1>

    <div class="flex items-center gap-3 relative">

        <!-- 🔔 Notifications -->
        <button id="notif-btn"
            class="icon-btn relative p-2 rounded-full hover:bg-slate-100 transition">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span id="notif-badge"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] px-1.5 rounded-full">
                3
            </span>
        </button>

        <div id="notification-menu"
            class="hidden absolute right-10 top-14 w-80 bg-white rounded-xl shadow-2xl border border-slate-100 z-50 overflow-hidden">

            <div class="flex items-center px-4 py-3 text-sm font-semibold border-b">
                <span class="flex-1 text-[#0E0F3B]">Notifications</span>
                <div class="flex items-center gap-3">
                    <button onclick="clearNotifications()"
                        class="text-xs text-slate-400 hover:text-slate-600 transition-colors">
                        Clear
                    </button>
                    <button onclick="closeAllMenus()"
                        class="p-1 rounded hover:bg-slate-100 transition">
                        <i data-lucide="x" class="w-4 h-4 text-slate-500"></i>
                    </button>
                </div>
            </div>

            <div id="notif-list" class="max-h-64 overflow-y-auto text-sm">
                <div class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b">
                    <div class="font-medium text-slate-700">New employer registered</div>
                    <div class="text-xs text-slate-400">2 mins ago</div>
                </div>
                <div class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b">
                    <div class="font-medium text-slate-700">New Job post is now pending for approval</div>
                    <div class="text-xs text-slate-400">10 mins ago</div>
                </div>
                <div class="px-4 py-3 hover:bg-slate-50 cursor-pointer">
                    <div class="font-medium text-slate-700">New testimonial submitted</div>
                    <div class="text-xs text-slate-400">1 hour ago</div>
                </div>
            </div>

            <div id="notif-empty"
                class="hidden flex-col items-center justify-center py-10 text-slate-400">
                <i data-lucide="bell" class="w-8 h-8 mb-2"></i>
                <span class="text-sm font-medium">No notifications</span>
                <span class="text-xs text-slate-400">You're all caught up</span>
            </div>

            <div onclick="expandNotifications(event)"
                class="text-center text-xs py-2 border-t hover:bg-slate-50 cursor-pointer transition">
                View all notifications
            </div>
        </div>

        <!-- ⚙️ Settings -->
        <button id="settings-btn"
            class="icon-btn p-2 rounded-full hover:bg-slate-100 transition">
            <i data-lucide="settings" class="w-5 h-5"></i>
        </button>

        <div id="header-settings-menu"
            class="hidden absolute right-0 top-14 w-56 bg-white rounded-xl shadow-2xl border border-slate-100 z-50 overflow-hidden">

            <div class="flex items-center gap-3 px-4 py-3 border-b">
                <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center">
                    <i data-lucide="user" class="w-5 h-5 text-slate-500"></i>
                </div>
                <a href="super_admin_profile.php" class="block">
                    <div class="hover:bg-slate-50 px-2 py-1 rounded-md transition cursor-pointer">
                        <div class="text-sm font-semibold text-slate-700">Miguel Santos</div>
                        <div class="text-xs text-slate-400">View Profile</div>
                    </div>
                </a>
            </div>

            <a href="super_admin_change_password.php"
                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                <i data-lucide="lock" class="w-4 h-4"></i>
                Change Password
            </a>

            <form method="POST" action="{{ route('user.logout') }}">
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
                <button id="confirm-logout"
                    class="px-4 py-2 rounded-lg bg-[#0E0F3B] text-white text-sm hover:bg-[#1a1c5a] transition">
                    CONFIRM
                </button>

                <button id="cancel-logout"
                    class="px-4 py-2 rounded-lg border text-[#0E0F3B] border-[#0E0F3B] text-sm hover:bg-[#0E0F3B] hover:text-white transition">
                    CANCEL
                </button>
            </div>

        </div>
    </div>
</header>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
        updateNotificationState();
        attachHeaderListeners();
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

    /* ── Expand Notifications ─────────────────────────────────── */
    function expandNotifications(e) {
        e.stopPropagation();
        const list = document.getElementById('notif-list');
        if (list) {
            list.classList.remove('max-h-64');
            list.classList.add('max-h-[500px]');
        }
    }

    /* ── Notifications state ──────────────────────────────────── */
    function updateNotificationState() {
        const list = document.getElementById('notif-list');
        const empty = document.getElementById('notif-empty');
        const badge = document.getElementById('notif-badge');
        if (!list || !empty) return;

        const hasItems = list.children.length > 0;
        list.classList.toggle('hidden', !hasItems);
        empty.classList.toggle('hidden', hasItems);

        if (!hasItems) empty.classList.add('flex');
        else empty.classList.remove('flex');

        if (badge) badge.style.display = hasItems ? '' : 'none';
    }

    function clearNotifications() {
        const list = document.getElementById('notif-list');
        if (!list) return;
        list.innerHTML = '';
        updateNotificationState();
    }

    //log out confirmation modal js
    const logoutLink = document.getElementById('logout-link');
    const logoutModal = document.getElementById('logout-modal');
    const confirmBtn = document.getElementById('confirm-logout');
    const cancelBtn = document.getElementById('cancel-logout');

    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            logoutModal.classList.remove('hidden');
            logoutModal.classList.add('flex');
        });
    }

    confirmBtn?.addEventListener('click', function() {
        window.location.href = logoutLink.href;
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
</script>