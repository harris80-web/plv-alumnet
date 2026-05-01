<?php
function active_class($page_name, $current_page)
{
    if ($page_name === $current_page) {
        return 'bg-white/10 border-l-4 border-orange-500 pl-4';
    }
    return 'hover:bg-white/10 px-5 group';
}

function icon_class($page_name, $current_page)
{
    if ($page_name === $current_page) {
        return 'text-orange-500';
    }
    return 'text-slate-400 group-hover:text-orange-500';
}
?>

<aside id="sidebar" class="relative flex flex-col bg-slate-900 text-white w-16 shrink-0 z-20 transition-all duration-300 ease-in-out" style="overflow: visible;">
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('assets/Admin/Side Bar.png') }}" class="object-cover h-full w-full opacity-100" alt="background">
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900 via-slate-900/80 to-orange-900/40"></div>
    </div>

    <div class="relative z-10 flex flex-col h-full py-4 text-[11px]" style="overflow: visible;">

        <div class="px-5 mb-5 flex items-center gap-3">
            <div class="min-w-[28px] h-7 flex items-center justify-center">
                <img src="{{ asset('assets/PLV-AlumNet LOGOMARK_WHITE.svg') }}" alt="Logo" class="w-7 h-7 object-contain">
            </div>
            <span class="sidebar-text hidden opacity-0 font-bold text-sm tracking-tight whitespace-nowrap transition-opacity duration-200">
                PLV-AlumNet
            </span>
        </div>

        <hr class="border-gray-700/50 mx-4 mb-4">

        <!-- Nav: NO overflow-y-auto here — it clips tooltips -->
        <nav class="flex-1 space-y-0" style="overflow: visible;">
            <a href="{{ route('superAdmin.dashboard') }}"
                data-tooltip="Dashboard"
                class="nav-link relative group flex items-center h-10 transition-all {{ $current_page === 'dashboard' ? 'bg-white/10 border-l-4 border-orange-500 pl-4' : 'hover:bg-white/10 px-5 group' }}">
                <div class="w-8 shrink-0 flex items-center">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 transition-colors {{ $current_page === 'dashboard' ? 'text-orange-500' : 'text-slate-400 group-hover:text-orange-500' }}"></i>
                </div>
                <span class="sidebar-text hidden opacity-0 whitespace-nowrap">Dashboard</span>
            </a>

            <a href="{{ route('superAdmin.userManagement') }}"
                data-tooltip="User Management"
                class="nav-link relative group flex items-center h-10 transition-all {{ $current_page === 'user_management' ? 'bg-white/10 border-l-4 border-orange-500 pl-4' : 'hover:bg-white/10 px-5 group' }}">
                <div class="w-8 shrink-0 flex items-center">
                    <i data-lucide="users" class="w-4 h-4 transition-colors {{ $current_page === 'user_management' ? 'text-orange-500' : 'text-slate-400 group-hover:text-orange-500' }}"></i>
                </div>
                <span class="sidebar-text hidden opacity-0 whitespace-nowrap">User Management</span>
            </a>

            <a href="{{ route('jobPosting.jobManagement') }}"
                data-tooltip="Job Placement Management"
                class="nav-link relative group flex items-center h-10 transition-all {{ $current_page === 'job_placement' ? 'bg-white/10 border-l-4 border-orange-500 pl-4' : 'hover:bg-white/10 px-5 group' }}">
                <div class="w-8 shrink-0 flex items-center">
                    <i data-lucide="briefcase" class="w-4 h-4 transition-colors {{ $current_page === 'job_placement' ? 'text-orange-500' : 'text-slate-400 group-hover:text-orange-500' }}"></i>
                </div>
                <span class="sidebar-text hidden opacity-0 whitespace-nowrap">Job Placement Management</span>
            </a>

            <a href="super_admin_id_yearbook.php"
                data-tooltip="Alumni ID & Yearbook Management"
                class="nav-link relative group flex items-center h-10 transition-all {{ $current_page === 'yearbook' ? 'bg-white/10 border-l-4 border-orange-500 pl-4' : 'hover:bg-white/10 px-5 group' }}">
                <div class="w-8 shrink-0 flex items-center">
                    <i data-lucide="book-open" class="w-4 h-4 transition-colors {{ $current_page === 'yearbook' ? 'text-orange-500' : 'text-slate-400 group-hover:text-orange-500' }}"></i>
                </div>
                <span class="sidebar-text hidden opacity-0 whitespace-nowrap">Alumni ID & Yearbook Management</span>
            </a>

            <a href="super_admin_notices_events.php"
                data-tooltip="Notices & Events Management"
                class="nav-link relative group flex items-center h-10 transition-all {{ $current_page === 'notices' ? 'bg-white/10 border-l-4 border-orange-500 pl-4' : 'hover:bg-white/10 px-5 group' }}">
                <div class="w-8 shrink-0 flex items-center">
                    <i data-lucide="bell" class="w-4 h-4 transition-colors {{ $current_page === 'notices' ? 'text-orange-500' : 'text-slate-400 group-hover:text-orange-500' }}"></i>
                </div>
                <span class="sidebar-text hidden opacity-0 whitespace-nowrap">Notices & Events Management</span>
            </a>

            <a href="super_admin_chatbot_messaging.php"
                data-tooltip="Chatbot & Messaging Management"
                class="nav-link relative group flex items-center h-10 transition-all {{ $current_page === 'messaging' ? 'bg-white/10 border-l-4 border-orange-500 pl-4' : 'hover:bg-white/10 px-5 group' }}">
                <div class="w-8 shrink-0 flex items-center">
                    <i data-lucide="message-square" class="w-4 h-4 transition-colors {{ $current_page === 'messaging' ? 'text-orange-500' : 'text-slate-400 group-hover:text-orange-500' }}"></i>
                </div>
                <span class="sidebar-text hidden opacity-0 whitespace-nowrap">Chatbot & Messaging Management</span>
            </a>

            <a href="{{ route('testimonials.manage') }}"
                data-tooltip="Testimonial Management"
                class="nav-link relative group flex items-center h-10 transition-all {{ $current_page === 'testimonials' ? 'bg-white/10 border-l-4 border-orange-500 pl-4' : 'hover:bg-white/10 px-5 group' }}">
                <div class="w-8 shrink-0 flex items-center">
                    <i data-lucide="quote" class="w-4 h-4 transition-colors {{ $current_page === 'testimonials' ? 'text-orange-500' : 'text-slate-400 group-hover:text-orange-500' }}"></i>
                </div>
                <span class="sidebar-text hidden opacity-0 whitespace-nowrap">Testimonial Management</span>
            </a>

            <a href="super_admin_faqs.php"
                data-tooltip="Manage FAQs"
                class="nav-link relative group flex items-center h-10 transition-all {{ $current_page === 'faqs' ? 'bg-white/10 border-l-4 border-orange-500 pl-4' : 'hover:bg-white/10 px-5 group' }}">
                <div class="w-8 shrink-0 flex items-center">
                    <i data-lucide="help-circle" class="w-4 h-4 transition-colors {{ $current_page === 'faqs' ? 'text-orange-500' : 'text-slate-400 group-hover:text-orange-500' }}"></i>
                </div>
                <span class="sidebar-text hidden opacity-0 whitespace-nowrap">Manage FAQs</span>
            </a>
        </nav>

        <div class="mt-auto">
            <button onclick="toggleSidebar()" data-tooltip="Expand" id="collapse-btn"
                class="nav-btn-tooltip w-full flex items-center h-10 transition-all hover:bg-white/10 px-5 group">
                <div class="w-8 shrink-0 flex items-center">
                    <i data-lucide="panel-left-open" id="collapse-icon"
                        class="w-4 h-4 text-slate-400 group-hover:text-orange-500 transition-all duration-300"></i>
                </div>
                <span class="sidebar-text hidden opacity-0 whitespace-nowrap">Collapse</span>
            </button>
        </div>
    </div>
</aside>

<!-- Body-level floating tooltip — escapes ALL overflow/stacking contexts -->
<div id="sidebar-tooltip"
    style="
        position: fixed;
        display: none;
        background: #1e293b;
        color: #fff;
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 6px;
        white-space: nowrap;
        pointer-events: none;
        z-index: 99999;
        transition: opacity 0.15s ease;
        opacity: 0;
     ">
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const collapseIcon = document.getElementById('collapse-icon');
    const sidebarTexts = document.querySelectorAll('.sidebar-text');

    /* ── Sidebar expand/collapse ── */
    function toggleSidebar() {
        const collapseBtn = document.getElementById('collapse-btn');
        const isCollapsed = sidebar.classList.contains('w-16');

        if (isCollapsed) {
            sidebar.classList.replace('w-16', 'w-72');
            collapseBtn.dataset.tooltip = 'Collapse';
            collapseIcon.style.opacity = '0';
            setTimeout(() => {
                collapseIcon.setAttribute('data-lucide', 'panel-left-close');
                lucide.createIcons();
                collapseIcon.style.opacity = '1';
            }, 150);
            sidebarTexts.forEach(t => {
                t.classList.remove('hidden');
                setTimeout(() => t.style.opacity = '1', 10);
            });
        } else {
            sidebar.classList.replace('w-72', 'w-16');
            collapseBtn.dataset.tooltip = 'Expand';
            collapseIcon.style.opacity = '0';
            setTimeout(() => {
                collapseIcon.setAttribute('data-lucide', 'panel-left-open');
                lucide.createIcons();
                collapseIcon.style.opacity = '1';
            }, 150);
            sidebarTexts.forEach(t => {
                t.style.opacity = '0';
                setTimeout(() => t.classList.add('hidden'), 200);
            });
            hideTooltip();
        }
    }

    /* ── Body-level tooltip (bypasses all overflow clipping) ── */
    const tooltip = document.getElementById('sidebar-tooltip');
    let tooltipTimeout;

    function showTooltip(text, anchorEl) {
        const rect = anchorEl.getBoundingClientRect();
        tooltip.textContent = text;
        tooltip.style.display = 'block';
        tooltip.style.opacity = '0';

        // Position: right of the sidebar (sidebar is w-16 = 64px)
        tooltip.style.left = (rect.right + 8) + 'px';
        tooltip.style.top = (rect.top + rect.height / 2 - tooltip.offsetHeight / 2) + 'px';

        // Small delay so opacity transition is visible
        clearTimeout(tooltipTimeout);
        tooltipTimeout = setTimeout(() => tooltip.style.opacity = '1', 10);
    }

    function hideTooltip() {
        clearTimeout(tooltipTimeout);
        tooltip.style.opacity = '0';
        tooltipTimeout = setTimeout(() => tooltip.style.display = 'none', 150);
    }

    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('mouseenter', () => {
            // Only show tooltip when sidebar is collapsed
            if (sidebar.classList.contains('w-16')) {
                showTooltip(link.dataset.tooltip, link);
            }
        });
        link.addEventListener('mouseleave', hideTooltip);
    });

    // Tooltips for collapse + settings buttons
    document.querySelectorAll('.nav-btn-tooltip').forEach(btn => {
        btn.addEventListener('mouseenter', () => {
            if (sidebar.classList.contains('w-16')) {
                showTooltip(btn.dataset.tooltip, btn);
            }
        });
        btn.addEventListener('mouseleave', hideTooltip);
    });
</script>