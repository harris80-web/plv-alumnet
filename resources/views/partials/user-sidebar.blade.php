<div id="menuOverlay" class="fixed inset-0 bg-black/50 z-[60] hidden transition-opacity duration-300"></div>

<div id="notificationPopup" class="absolute top-20 right-[20px] md:right-[250px] w-80 bg-white rounded-xl shadow-2xl z-[100] hidden transform origin-top-right transition-all duration-300 scale-95 opacity-0">
    <div class="p-4 border-b flex justify-between items-center">
        <h3 class="text-[#0E0F3B] font-bold">Notifications</h3>
        <button onclick="toggleNotifications(event)" class="text-gray-400 hover:text-gray-600">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <div id="notificationList" class="max-h-96 overflow-y-auto">
        <div class="p-10 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <i class="fa-regular fa-bell text-gray-400 text-xl"></i>
            </div>
            <p class="text-gray-500 text-sm">No notifications</p>
        </div>
    </div>
    <a href="{{ route('notifications.all') }}"
        class="block text-center text-xs font-semibold text-[#0E0F3B] py-2.5 border-t hover:bg-gray-50 transition-colors">
        View all notifications
    </a>
</div>

<div id="userSidebar" class="fixed top-0 right-0 h-full w-80 bg-white z-[70] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <button onclick="toggleSidebar()" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark text-lg"></i>
    </button>

    <div class="p-6 flex flex-col flex-1 overflow-y-auto">
        {{-- Logo --}}
        <div class="flex items-center justify-center gap-2 mb-6">
            <img src="{{ asset('assets/PLV-AlumNet LOGOMARK_BLUE 1.png') }}" alt="" class="h-14 w-auto">
            <img src="{{ asset('assets/PLV-AlumNet LETTERMARK_COLORED 2.png') }}" alt="PLV-AlumNet" class="h-9 w-auto">
        </div>

        <div class="border-t border-gray-100 mb-4"></div>

        {{-- Avatar + name row --}}
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 mb-4">
            <div class="w-12 h-12 shrink-0 bg-[#0E0F3B] rounded-full flex items-center justify-center overflow-hidden">
                <i class="fa-solid fa-user text-xl text-white"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[#C73D1A] font-bold uppercase text-sm truncate">
                    {{ auth()->user()->user_first_name }} {{ auth()->user()->user_last_name }}
                </p>
                <p class="text-xs text-gray-400 truncate">
                    @if(auth()->user()->user_role === 'employer')
                        {{ auth()->user()->employer->employer_company_name ?? 'Company' }}
                    @else
                        Alumni Batch {{ auth()->user()->alumnus->alumnus_batch?->format('Y') ?? '—' }}
                    @endif
                </p>
            </div>
        </div>

        {{-- Account settings --}}
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Account Settings</p>
        <nav class="space-y-1 flex-grow">
            @if(auth()->user()->user_role === 'employer')
                <a href="{{ route('user.profile') }}"
                    class="group flex items-center gap-3 -mx-6 px-6 py-2.5 text-sm transition {{ $current_page == 'employer_profile' ? 'text-[#ED7A07] font-bold bg-orange-50' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white' }}">
                    <span class="w-7 h-7 shrink-0 flex items-center justify-center rounded-md bg-gray-50 group-hover:bg-transparent">
                        <i class="fa-solid fa-building text-xs"></i>
                    </span>
                    Company Profile
                </a>
                <a href="{{ route('users.editProfile') }}"
                    class="group flex items-center gap-3 -mx-6 px-6 py-2.5 text-sm transition {{ $current_page == 'employer_edit' ? 'text-[#ED7A07] font-bold bg-orange-50' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white' }}">
                    <span class="w-7 h-7 shrink-0 flex items-center justify-center rounded-md bg-gray-50 group-hover:bg-transparent">
                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                    </span>
                    Edit Details
                </a>
                <a href="{{ route('users.showChangePassword') }}"
                    class="group flex items-center gap-3 -mx-6 px-6 py-2.5 text-sm transition {{ $current_page == 'employer_change_password' ? 'text-[#ED7A07] font-bold bg-orange-50' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white' }}">
                    <span class="w-7 h-7 shrink-0 flex items-center justify-center rounded-md bg-gray-50 group-hover:bg-transparent">
                        <i class="fa-solid fa-lock text-xs"></i>
                    </span>
                    Change Password
                </a>
            @else
                <a href="{{ route('user.profile') }}"
                    class="group flex items-center gap-3 -mx-6 px-6 py-2.5 text-sm transition {{ $current_page == 'alumni_profile' ? 'text-[#ED7A07] font-bold bg-orange-50' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white' }}">
                    <span class="w-7 h-7 shrink-0 flex items-center justify-center rounded-md bg-gray-50 group-hover:bg-transparent">
                        <i class="fa-solid fa-address-card text-xs"></i>
                    </span>
                    View Profile
                </a>
                <a href="{{ route('users.editProfile') }}"
                    class="group flex items-center gap-3 -mx-6 px-6 py-2.5 text-sm transition {{ $current_page == 'alumni_edit' ? 'text-[#ED7A07] font-bold bg-orange-50' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white' }}">
                    <span class="w-7 h-7 shrink-0 flex items-center justify-center rounded-md bg-gray-50 group-hover:bg-transparent">
                        <i class="fa-solid fa-user-pen text-xs"></i>
                    </span>
                    Edit Profile
                </a>
                <a href="{{ route('users.showChangePassword') }}"
                    class="group flex items-center gap-3 -mx-6 px-6 py-2.5 text-sm transition {{ $current_page == 'alumni_change_password' ? 'text-[#ED7A07] font-bold bg-orange-50' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white' }}">
                    <span class="w-7 h-7 shrink-0 flex items-center justify-center rounded-md bg-gray-50 group-hover:bg-transparent">
                        <i class="fa-solid fa-lock text-xs"></i>
                    </span>
                    Change Password
                </a>
            @endif
        </nav>
    </div>

    <div class="border-t border-gray-100 px-6 py-4 shrink-0">
        <form method="POST" action="{{ route('user.logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-3 text-[#0E0F3B] hover:text-[#ED7A07] transition font-bold text-sm">
                <i class="fa-solid fa-right-from-bracket"></i> Log out
            </button>
        </form>
    </div>
</div>

<script>
    const sidebar = document.getElementById('userSidebar');
    const notification = document.getElementById('notificationPopup');
    const overlay = document.getElementById('menuOverlay');

    function toggleSidebar() {
        if (sidebar && overlay) {
            sidebar.classList.toggle('translate-x-full');
            overlay.classList.toggle('hidden');
            if (notification) {
                notification.classList.add('hidden', 'scale-95', 'opacity-0');
            }
        }
    }

    function toggleNotifications(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }

        if (!notification) return;
        const isHidden = notification.classList.contains('hidden');

        if (isHidden) {
            notification.classList.remove('hidden');
            setTimeout(() => {
                notification.classList.remove('scale-95', 'opacity-0');
            }, 10);
            markNotificationsRead();
        } else {
            notification.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 300);
        }
    }

    function closeNotifications() {
        notification.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 300);
    }

    // ── Notification bell: fetch, render, and mark-as-read-on-open ──
    const NOTIFICATIONS_URL = {!! json_encode(route('notifications.index')) !!};
    const NOTIFICATIONS_MARK_READ_URL = {!! json_encode(route('notifications.markAllRead')) !!};
    const NOTIF_CSRF_TOKEN = '{{ csrf_token() }}';

    function escapeHtmlForNotif(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function renderNotifications(list) {
        const container = document.getElementById('notificationList');
        if (!container) return;

        if (list.length === 0) {
            container.innerHTML = `
                <div class="p-10 flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fa-regular fa-bell text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-gray-500 text-sm">No notifications</p>
                </div>
            `;
            return;
        }

        container.innerHTML = list.map(n => `
            <div class="p-4 border-b border-gray-50 ${n.read ? '' : 'bg-orange-50/60'}">
                <div class="flex items-start gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-[#C73D1A] text-xs mt-0.5"></i>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-[#0E0F3B]">${escapeHtmlForNotif(n.title)}</p>
                        <p class="text-xs text-gray-500 mt-0.5">${escapeHtmlForNotif(n.body)}</p>
                        <p class="text-[10px] text-gray-400 mt-1">${escapeHtmlForNotif(n.timeLabel)}</p>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function updateNotifBadge(count) {
        document.querySelectorAll('#notifBadge').forEach(badge => {
            badge.textContent = count > 9 ? '9+' : String(count);
            badge.classList.toggle('hidden', count === 0);
        });
    }

    async function fetchNotifications() {
        try {
            const res = await fetch(NOTIFICATIONS_URL);
            if (!res.ok) return;
            const data = await res.json();
            renderNotifications(data.notifications);
            updateNotifBadge(data.unreadCount);
        } catch (e) { /* transient network hiccup — next poll retries */ }
    }

    async function markNotificationsRead() {
        updateNotifBadge(0); // optimistic — next poll confirms
        try {
            await fetch(NOTIFICATIONS_MARK_READ_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': NOTIF_CSRF_TOKEN, 'Accept': 'application/json' },
            });
            fetchNotifications();
        } catch (e) { /* badge will resync on the next regular poll */ }
    }

    fetchNotifications();
    let notifPollTimer = setInterval(fetchNotifications, 20000);
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            clearInterval(notifPollTimer);
        } else {
            fetchNotifications();
            notifPollTimer = setInterval(fetchNotifications, 20000);
        }
    });

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.add('translate-x-full');
            overlay.classList.add('hidden');
            closeNotifications();
        });
    }

    window.addEventListener('click', function(e) {
        const isClickInside = notification.contains(e.target);
        const isNotificationBtn = e.target.closest('[onclick="toggleNotifications()"]');
        if (!isClickInside && !isNotificationBtn && !notification.classList.contains('hidden')) {
            closeNotifications();
        }
    });
</script>