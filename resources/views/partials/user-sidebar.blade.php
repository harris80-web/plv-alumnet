<div id="menuOverlay" class="fixed inset-0 bg-black/50 z-[60] hidden transition-opacity duration-300"></div>

<div id="notificationPopup" class=" absolute  top-20 right-[20px] md:right-[250px] w-72 bg-white rounded-xl shadow-2xl z-[100] hidden transform origin-top-right transition-all duration-300 scale-95 opacity-0">

    <div class="p-4 border-b flex justify-between items-center">
        <h3 class="text-[#0E0F3B] font-bold">Notifications</h3>
        <button onclick="toggleNotifications(event)" class="text-gray-400 hover:text-gray-600">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="p-10 flex flex-col items-center justify-center text-center">
        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
            <i class="fa-regular fa-bell text-gray-400 text-xl"></i>
        </div>
        <p class="text-gray-500 text-sm">No notifications</p>
    </div>
</div>

<div id="userSidebar" class="fixed top-0 right-0 h-full w-80 bg-white z-[70] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out">
    <div class="p-6 relative flex flex-col h-full">
        <button onclick="toggleSidebar()" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>

        <div class="flex flex-col items-center mt-8 mb-6">
            <div class="w-24 h-24 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-4 overflow-hidden">
                <i class="fa-solid fa-user text-5xl text-white"></i>
            </div>
            <h2 class="text-[#ED7A07] font-semibold text-xl uppercase tracking-wider">
                <?php echo (strpos($current_page, 'employer') !== false) ? 'Employer' : 'Alumni'; ?>
            </h2>
            <div class="w-full border-b mt-4"></div>
        </div>

        <nav class="space-y-4 flex-grow">
            <?php if (strpos($current_page, 'employer') !== false): ?>
                <a href="employer_profile.php" class="flex items-center gap-4 p-3 w-full transition <?php echo ($current_page == 'employer_profile') ? 'bg-[#ED7A07] text-white font-bold' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white'; ?>">
                    <i class="fa-solid fa-building w-6"></i> Company Profile
                </a>
                <a href="employer_edit.php" class="flex items-center gap-4 p-3 w-full transition <?php echo ($current_page == 'employer_edit') ? 'bg-[#ED7A07] text-white font-bold' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white'; ?>">
                    <i class="fa-solid fa-pen-to-square w-6"></i> Edit Details
                </a>
                <a href="employer_change_password.php" class="flex items-center gap-4 p-3 w-full transition <?php echo ($current_page == 'employer_change_password') ? 'bg-[#ED7A07] text-white font-bold' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white'; ?>">
                    <i class="fa-solid fa-lock w-6"></i> Change Password
                </a>

            <?php else: ?>
                <a href="alumni_profile.php" class="flex items-center gap-4 p-3 w-full transition <?php echo ($current_page == 'alumni_profile') ? 'bg-[#ED7A07] text-white font-bold' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white'; ?>">
                    <i class="fa-solid fa-address-card w-6"></i> View Profile
                </a>
                <a href="alumni_edit.php" class="flex items-center gap-4 p-3 w-full transition <?php echo ($current_page == 'alumni_edit') ? 'bg-[#ED7A07] text-white font-bold' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white'; ?>">
                    <i class="fa-solid fa-user-pen w-6"></i> Edit Profile
                </a>
                <a href="alumni_change_password.php" class="flex items-center gap-4 p-3 w-full transition <?php echo ($current_page == 'alumni_change_password') ? 'bg-[#ED7A07] text-white font-bold' : 'text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white'; ?>">
                    <i class="fa-solid fa-lock w-6"></i> Change Password
                </a>
            <?php endif; ?>
        </nav>

        <div class="border-t pt-6">
            <a href="logout.php" class="flex items-center gap-4 text-[#0E0F3B] hover:text-[#ED7A07] p-3 transition font-bold">
                <i class="fa-solid fa-right-from-bracket"></i> Log out
            </a>
        </div>
    </div>
</div>

<script>
    const sidebar = document.getElementById('userSidebar');
    const notification = document.getElementById('notificationPopup');
    const overlay = document.getElementById('menuOverlay');

    function toggleSidebar() {
        if (sidebar && overlay) {
            const isHidden = sidebar.classList.contains('translate-x-full');
            sidebar.classList.toggle('translate-x-full');
            overlay.classList.toggle('hidden');
            // Hide notification if sidebar opens
            if (notification) {
                notification.classList.add('hidden', 'scale-95', 'opacity-0');
            }
        }
    }

    function toggleNotifications(event) {
        if (event) {
            event.stopPropagation(); // Stops the window click from firing
            event.preventDefault(); // Prevents any jumping if it's an 'a' tag
        }

        if (!notification) return;
        const isHidden = notification.classList.contains('hidden');

        if (isHidden) {
            notification.classList.remove('hidden');
            // Small delay to allow 'hidden' to be removed before animating
            setTimeout(() => {
                notification.classList.remove('scale-95', 'opacity-0');
            }, 10);
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

    // 1. Close when clicking the overlay (for sidebar)
    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.add('translate-x-full');
            overlay.classList.add('hidden');
            closeNotifications();
        });
    }

    // 2. Close when clicking anywhere outside the popup
    window.addEventListener('click', function(e) {
        const isClickInside = notification.contains(e.target);
        // Add the ID or class of your Bell/Notification button here to avoid instant closing
        const isNotificationBtn = e.target.closest('[onclick="toggleNotifications()"]');

        if (!isClickInside && !isNotificationBtn && !notification.classList.contains('hidden')) {
            closeNotifications();
        }
    });
    
</script>