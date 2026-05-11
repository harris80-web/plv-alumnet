<!--<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body>
    <h1>MANAGE TESTIMONIALS</h1>
    <div>
        @foreach($testimonials as $testimonial)
        @if (!$testimonial->testimonial_post)
        <div>
            <p>{{ $testimonial->testimonial_body }}</p>
            <p>Submitted by: {{ $testimonial->alumnus->user->user_first_name }}</p>
        </div>
        <form action="{{ route('testimonials.post', $testimonial->testimonial_id) }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit">Post</button>
        </form>
        @endif
        @endforeach
    </div>
</body>

</html>-->

@php
    $current_page = 'testimonials';
    $total_testimonials = $testimonials->count();
    $published_count = $testimonials->where('testimonial_post', 1)->count();
    $hidden_count = $testimonials->where('testimonial_post', 0)->count();
    $programs_count = $testimonials->map(fn($t) => $t->alumnus->program->program_id ?? null)->filter()->unique()->count();
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonial Management | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/PLV-AlumNet LOGO.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.open {
            display: flex;
        }

        .filter-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 50;
            min-width: 160px;
        }

        .filter-dropdown-menu.open {
            display: block;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        * {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        input[type="checkbox"] {
            appearance: none;
            width: 1rem;
            height: 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background-color: white;
            cursor: pointer;
            transition: all 0.10s;
        }

        input[type="checkbox"]:checked {
            background-color: #ED7A07;
            border: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M13.485 1.431a1.473 1.473 0 0 0-2.072 0L6.133 7.952 4.342 5.985a1.473 1.473 0 1 0-2.172 1.992l2.86 3.06a1.473 1.473 0 0 0 2.134.018l6.366-6.77a1.473 1.473 0 0 0-.045-2.834z'/%3E%3C/svg%3E");
            background-size: 10px;
            background-position: center;
            background-repeat: no-repeat;
        }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">
        @include('partials.super-admin-side-bar')
        <main class="flex-1 flex flex-col overflow-hidden">
            @include('partials.super-admin-header')
            <div class="flex-1 overflow-y-auto p-8">
                @include('partials.success')
                <!-- Stat Cards -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-slate-800">{{ $total_testimonials }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Total Testimonials</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-green-500">{{ $published_count }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Published</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-slate-400">{{ $hidden_count }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Hidden</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-amber-400">{{ $programs_count }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Programs Represented</p>
                    </div>
                </div>

                <!-- Search / Filter Row -->
                <div class="flex justify-between items-center mb-6">
                    <!-- Left: Search + Filters -->
                    <div class="flex gap-2 items-center">
                        <!-- Search -->
                        <div class="relative w-64">
                            <i data-lucide="search"
                                class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input id="search-input" type="text" placeholder="Search by Name"
                                class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-[#C73D1A] transition-all">
                        </div>

                        <!-- Program Filter -->
                        <div class="relative">
                            <button onclick="toggleFilterDropdown('program-dropdown', event)"
                                class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:border-[#C73D1A] transition-all max-w-[180px]">
                                <span id="program-label" class="truncate">Program</span>
                                <i data-lucide="chevron-down" class="w-3 h-3 shrink-0"></i>
                            </button>
                            <div id="program-dropdown"
                                class="filter-dropdown-menu bg-white border border-slate-200 rounded-lg shadow-lg py-1 mt-1 w-72 max-h-72 overflow-y-auto"
                                style="scrollbar-width:thin">
                                <button onclick="setFilter('program','All','program-label','program-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">All</button>
                                <button
                                    onclick="setFilter('program','Bachelor of Arts in Communication','program-label','program-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">Bachelor
                                    of Arts in Communication</button>
                                <button
                                    onclick="setFilter('program','Bachelor of Early Childhood Education','program-label','program-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">Bachelor
                                    of Early Childhood Education</button>
                                <button
                                    onclick="setFilter('program','Bachelor of Science in Accountancy','program-label','program-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">Bachelor
                                    of Science in Accountancy</button>
                                <p class="px-4 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    BS in Business Administration</p>
                                <button
                                    onclick="setFilter('program','BSBA - Major in Financial Management','program-label','program-dropdown')"
                                    class="w-full text-left px-6 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">BSBA
                                    - Major in Financial Management</button>
                                <button
                                    onclick="setFilter('program','BSBA - Major in Human Resource Management','program-label','program-dropdown')"
                                    class="w-full text-left px-6 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">BSBA
                                    - Major in Human Resource Management</button>
                                <button
                                    onclick="setFilter('program','BSBA - Major in Marketing Management','program-label','program-dropdown')"
                                    class="w-full text-left px-6 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">BSBA
                                    - Major in Marketing Management</button>
                                <button
                                    onclick="setFilter('program','BSCE - Bachelor of Science in Civil Engineering','program-label','program-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">BSCE
                                    - Bachelor of Science in Civil Engineering</button>
                                <button
                                    onclick="setFilter('program','BSEE - Bachelor of Science in Electrical Engineering','program-label','program-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">BSEE
                                    - Bachelor of Science in Electrical Engineering</button>
                                <button
                                    onclick="setFilter('program','BSIT - Bachelor of Science in Information Technology','program-label','program-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">BSIT
                                    - Bachelor of Science in Information Technology</button>
                                <button
                                    onclick="setFilter('program','Bachelor of Science in Psychology','program-label','program-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">Bachelor
                                    of Science in Psychology</button>
                                <button
                                    onclick="setFilter('program','Bachelor of Public Administration','program-label','program-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">Bachelor
                                    of Public Administration</button>
                                <button
                                    onclick="setFilter('program','Bachelor of Science in Social Work','program-label','program-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">Bachelor
                                    of Science in Social Work</button>
                                <p class="px-4 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    Bachelor of Secondary Education</p>
                                <button
                                    onclick="setFilter('program','BSEd - Major in English','program-label','program-dropdown')"
                                    class="w-full text-left px-6 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">BSEd
                                    - Major in English</button>
                                <button
                                    onclick="setFilter('program','BSEd - Major in Filipino','program-label','program-dropdown')"
                                    class="w-full text-left px-6 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">BSEd
                                    - Major in Filipino</button>
                                <button
                                    onclick="setFilter('program','BSEd - Major in Mathematics','program-label','program-dropdown')"
                                    class="w-full text-left px-6 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">BSEd
                                    - Major in Mathematics</button>
                                <button
                                    onclick="setFilter('program','BSEd - Major in Science','program-label','program-dropdown')"
                                    class="w-full text-left px-6 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">BSEd
                                    - Major in Science</button>
                                <button
                                    onclick="setFilter('program','BSEd - Major in Social Studies','program-label','program-dropdown')"
                                    class="w-full text-left px-6 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">BSEd
                                    - Major in Social Studies</button>
                            </div>
                        </div>

                        <!-- Batch Filter -->
                        <div class="relative">
                            <button onclick="toggleFilterDropdown('batch-dropdown', event)"
                                class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:border-[#C73D1A] transition-all">
                                <span id="batch-label">Batch</span>
                                <i data-lucide="chevron-down" class="w-3 h-3"></i>
                            </button>
                            <div id="batch-dropdown"
                                class="filter-dropdown-menu bg-white border border-slate-200 rounded-lg shadow-lg py-1 mt-1">
                                <button onclick="setFilter('batch','All','batch-label','batch-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">All</button>

                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="relative">
                            <button onclick="toggleFilterDropdown('status-dropdown', event)"
                                class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:border-[#C73D1A] transition-all">
                                <span id="status-label">Status</span>
                                <i data-lucide="chevron-down" class="w-3 h-3"></i>
                            </button>
                            <div id="status-dropdown"
                                class="filter-dropdown-menu bg-white border border-slate-200 rounded-lg shadow-lg py-1 mt-1">
                                <button onclick="setFilter('status','All','status-label','status-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">All</button>
                                <button onclick="setFilter('status','Published','status-label','status-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">Published</button>
                                <button onclick="setFilter('status','Hidden','status-label','status-dropdown')"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-orange-50 hover:text-[#C73D1A]">Hidden</button>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Bulk Actions (visible only when rows are checked) -->
                    <div id="bulk-actions" class="hidden items-center gap-1">
                        <span id="bulk-count" class="text-xs font-medium text-slate-500 mr-1"></span>
                        <button onclick="bulkAction('publish')"
                            class="flex items-center gap-1.5 px-3 py-2 bg-transparent hover:bg-green-600 text-green-600 hover:text-white text-xs font-medium rounded-lg border border-green-600 transition-all uppercase">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i> Publish
                        </button>
                        <button onclick="bulkAction('hide')"
                            class="flex items-center gap-1.5 px-3 py-2 bg-transparent hover:bg-[#0E0F3B] text-[#0E0F3B] hover:text-white text-xs font-medium rounded-lg border border-[#0E0F3B] transition-all uppercase">
                            <i data-lucide="eye-off" class="w-3.5 h-3.5"></i> Hide
                        </button>
                        <button onclick="bulkAction('delete')"
                            class="flex items-center gap-1.5 px-3 py-2 bg-transparent hover:bg-red-600 text-red-600 hover:text-white text-xs font-medium rounded-lg border border-red-600 transition-all uppercase">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                        </button>
                    </div>

                </div><!-- end Search / Filter Row -->

                <!-- Table -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                    <table class="w-full text-left text-[10px]">
                        <thead class="bg-[#0E0F3B] text-white uppercase tracking-wider text-center">
                            <tr>
                                <th class="px-4 py-4 font-semibold border-r border-slate-700">
                                    <input type="checkbox" id="select-all" class="rounded"
                                        onclick="toggleAllCheckboxes(this)">
                                </th>
                                <th class="px-4 py-4 font-semibold border-r border-slate-700">Name</th>
                                <th class="px-4 py-4 font-semibold border-r border-slate-700">Program <i
                                        data-lucide="chevron-down" class="inline w-3 h-3 ml-1 opacity-50"></i></th>
                                <th class="px-4 py-4 font-semibold border-r border-slate-700">Batch <i
                                        data-lucide="chevron-down" class="inline w-3 h-3 ml-1 opacity-50"></i></th>
                                <th class="px-4 py-4 font-semibold border-r border-slate-700">Message</th>
                                <th class="px-4 py-4 font-semibold border-r border-slate-700">Status</th>
                                <th class="px-4 py-4 font-semibold text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="testimonials-tbody">
                            @forelse ($testimonials as $t)
                                @php
                                    $statusLabel = $t->testimonial_post ? 'Published' : 'Hidden';
                                    $statusClass = $t->testimonial_post
                                        ? 'bg-green-100 text-green-700 border-green-200'
                                        : 'bg-slate-100 text-slate-500 border-slate-200';
                                    $msgPreview = mb_strlen($t->testimonial_body) > 50
                                        ? '"' . mb_substr($t->testimonial_body, 0, 50) . '..."'
                                        : '"' . $t->testimonial_body . '"';
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors text-center"
                                    data-id="{{ $t->testimonial_id }}" data-name="{{ $t->alumnus->user->user_first_name }}"
                                    data-program="{{ $t->alumnus->program->program_name ?? '' }}"
                                    data-batch="{{ $t->alumnus->alumnus_batch ?? '' }}" data-status="{{ $statusLabel }}"
                                    data-message="{{ $t->testimonial_body }}">
                                    <td class="px-4 py-3 border-r border-slate-100">
                                        <input type="checkbox" class="row-checkbox rounded"
                                            data-id="{{ $t->testimonial_id }}">
                                    </td>
                                    <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $t->alumnus->user->user_first_name }}</td>
                                    <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $t->alumnus->program->program_name ?? '—' }}</td>
                                    <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $t->alumnus->alumnus_batch ?? '—' }}</td>
                                    <td class="px-4 py-3 font-medium text-black border-r border-slate-100 text-left">
                                        {{ $msgPreview }}</td>
                                    <td class="px-4 py-3 border-r border-slate-100">
                                        <span class="px-2 py-1 rounded-full border text-[9px] font-bold {{ $statusClass }}">
                                            {{ strtoupper($statusLabel) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center relative">
                                        <div class="inline-block text-left">
                                            <button
                                                class="menu-button p-2 hover:bg-slate-100 rounded-full transition-colors">
                                                <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                            </button>
                                            <div
                                                class="action-dropdown absolute right-4 mt-2 w-44 origin-top-right rounded-md bg-white shadow-xl ring-1 ring-black ring-opacity-5 z-50 hidden">
                                                <div class="py-1">
                                                    <button onclick="openView({{ $t->testimonial_id }})"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-blue-50 transition-colors">
                                                        <i data-lucide="eye" class="w-4 h-4 mr-3 text-blue-500"></i> View
                                                    </button>
                                                    @if ($t->testimonial_post)
                                                        <button
                                                            onclick="openToggleConfirm({{ $t->testimonial_id }}, 'Published', '{{ $t->alumnus->user->user_first_name }}')"
                                                            class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-slate-50 transition-colors">
                                                            <i data-lucide="eye-off" class="w-4 h-4 mr-3 text-slate-400"></i>
                                                            Hide
                                                        </button>
                                                    @else
                                                        <button
                                                            onclick="openToggleConfirm({{ $t->testimonial_id }}, 'Hidden', '{{ $t->alumnus->user->user_first_name }}')"
                                                            class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-green-50 transition-colors">
                                                            <i data-lucide="send" class="w-4 h-4 mr-3 text-green-500"></i>
                                                            Publish
                                                        </button>
                                                    @endif
                                                    <hr class="my-1 border-slate-100">
                                                    <button
                                                        onclick="openDeleteConfirm({{ $t->testimonial_id }}, '{{ $t->alumnus->user->user_first_name }}')"
                                                        class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                        <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 text-sm">No testimonials found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div id="empty-state" class="hidden text-center py-12 text-slate-400 text-sm">No testimonials found.
                    </div>
                </div><!-- end Table -->

            </div><!-- end flex-1 overflow-y-auto p-8 -->
        </main>
    </div><!-- end flex h-screen -->


    <!-- ===== VIEW MODAL ===== -->
    <div id="view-modal" class="modal-overlay" onclick="if(event.target===this) closeModal('view-modal')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden max-h-[90vh] flex flex-col">

            <!-- Header-->
            <div class="bg-[#0E0F3B] px-8 pt-5 pb-14 flex justify-between items-start relative">
                <h2 class="text-white font-[Montserrat] text-m font-semibold">View Testimonial</h2>
                <button onclick="closeModal('view-modal')"
                    class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Avatar -->
            <div class="flex justify-center -mt-10 relative z-10">
                <div
                    class="w-20 h-20 rounded-full bg-orange-500 flex items-center justify-center shadow-md ring-4 ring-white">
                    <i data-lucide="user-round" class="w-10 h-10 text-white"></i>
                </div>
            </div>

            <!-- Fields -->
            <div class="px-8 py-4 space-y-3 text-sm flex-1 overflow-y-auto mt-2">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-[#C73D1A] w-24 shrink-0">Name:</span>
                    <span id="view-name" class="text-slate-800 font-medium"></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-[#C73D1A] w-24 shrink-0">Program:</span>
                    <span id="view-program" class="text-slate-800 font-medium"></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-[#C73D1A] w-24 shrink-0">Batch:</span>
                    <span id="view-batch" class="text-slate-800 font-medium"></span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="font-bold text-[#C73D1A] w-24 shrink-0 pt-0.5">Message:</span>
                    <span id="view-message" class="text-slate-700 italic leading-relaxed"></span>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 pb-7 pt-3 flex justify-end gap-3">
                <button onclick="closeModal('view-modal')"
                    class="px-6 py-2 border-2 border-[#0E0F3B] text-[#0E0F3B] rounded-lg text-sm font-bold hover:bg-[#0E0F3B] hover:text-white transition-all uppercase">
                    Cancel
                </button>
                <button id="view-toggle-btn"
                    class="px-6 py-2 text-white rounded-lg text-sm font-bold transition-all uppercase">
                </button>
            </div>

        </div>
    </div>

    <!-- ===== CONFIRM MODAL ===== -->
    <div id="confirmModal"
        class="fixed inset-0 z-[100] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-[#0E0F3B]/40 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
        <div id="confirmContent"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300">
            <div class="p-8 text-center">
                <div id="confirmIconContainer"
                    class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i id="confirmIcon" data-lucide="alert-triangle" class="w-8 h-8"></i>
                </div>
                <h3 id="confirmTitle" class="text-[#0E0F3B] text-xl font-bold mb-2">Confirmation</h3>
                <p id="confirmMessage" class="text-slate-500 text-sm leading-relaxed">Are you sure you want to proceed?
                </p>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeConfirmModal()"
                    class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button id="confirmYesBtn"
                    class="flex-1 py-2.5 text-white rounded-lg text-xs font-bold transition-all uppercase hover:brightness-110">
                    Yes, Proceed
                </button>
            </div>
        </div>
    </div>


    <script>
        const testimonialData = <?= json_encode($testimonials->map(fn($t) => [
    'id' => $t->testimonial_id,
    'name' => $t->alumnus->user->user_first_name,
    'program' => $t->alumnus->program->program_name ?? '—',
    'batch' => $t->alumnus->alumnus_batch ?? '—',
    'message' => $t->testimonial_body,
    'status' => $t->testimonial_post ? 'Published' : 'Hidden',
])->values()) ?>;

        let activeFilters = {
            program: 'All',
            batch: 'All',
            status: 'All'
        };

        /* ── Filter dropdowns ─────────────────────────────── */
        function toggleFilterDropdown(id, e) {
            e.stopPropagation();
            const el = document.getElementById(id);
            const isOpen = el.classList.contains('open');
            closeAllFilterDropdowns();
            if (!isOpen) el.classList.add('open');
        }

        function closeAllFilterDropdowns() {
            document.querySelectorAll('.filter-dropdown-menu.open').forEach(d => d.classList.remove('open'));
        }

        function setFilter(key, val, labelId, dropdownId) {
            activeFilters[key] = val;
            const label = document.getElementById(labelId);
            label.textContent = val === 'All' ? key.charAt(0).toUpperCase() + key.slice(1) : val;
            document.getElementById(dropdownId).classList.remove('open');
            applyFilters();
        }

        function applyFilters() {
            const search = document.getElementById('search-input').value.toLowerCase();
            let visible = 0;
            document.querySelectorAll('#testimonials-tbody tr').forEach(row => {
                const name = row.dataset.name?.toLowerCase() || '';
                const program = row.dataset.program || '';
                const batch = row.dataset.batch || '';
                const status = row.dataset.status || '';
                const show =
                    (activeFilters.program === 'All' || program === activeFilters.program) &&
                    (activeFilters.batch === 'All' || batch === activeFilters.batch) &&
                    (activeFilters.status === 'All' || status === activeFilters.status) &&
                    name.includes(search);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            document.getElementById('empty-state').classList.toggle('hidden', visible > 0);
        }

        document.addEventListener('click', closeAllFilterDropdowns);
        document.getElementById('search-input').addEventListener('input', applyFilters);

        /* ── Action dropdowns (3-dot menu) ───────────────── */
        function initDropdowns() {
            document.querySelectorAll('.menu-button').forEach(button => {
                const newBtn = button.cloneNode(true);
                button.parentNode.replaceChild(newBtn, button);
                newBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const dropdown = newBtn.nextElementSibling;
                    document.querySelectorAll('.action-dropdown').forEach(m => {
                        if (m !== dropdown) m.classList.add('hidden');
                    });
                    dropdown.classList.toggle('hidden');
                });
            });
        }

        document.addEventListener('click', () => {
            document.querySelectorAll('.action-dropdown').forEach(m => m.classList.add('hidden'));
        });

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            initDropdowns();

            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.addEventListener('change', updateBulkBar);
            });
            document.getElementById('select-all').addEventListener('change', updateBulkBar);
        });

        /* ── Checkbox ─────────────────────────────────────── */
        function toggleAllCheckboxes(master) {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = master.checked);
            updateBulkBar();
        }

        /* ── Modal helpers ────────────────────────────────── */
        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        /* ── View Modal ───────────────────────────────────── */
        function openView(id) {
            const t = testimonialData.find(x => x.id == id);
            document.getElementById('view-name').textContent = t.name;
            document.getElementById('view-program').textContent = t.program;
            document.getElementById('view-batch').textContent = t.batch;
            document.getElementById('view-message').textContent = '"' + t.message + '"';

            const toggleBtn = document.getElementById('view-toggle-btn');
            if (t.status === 'Published') {
                toggleBtn.textContent = 'Hide';
                toggleBtn.className = 'px-6 py-2 bg-[#0E0F3B] hover:bg-[#1a1b5e] text-white rounded-lg text-sm font-bold transition-all uppercase';
                toggleBtn.onclick = () => {
                    closeModal('view-modal');
                    openToggleConfirm(t.id, 'Published', t.name);
                };
            } else {
                toggleBtn.textContent = 'Publish';
                toggleBtn.className = 'px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-bold transition-all uppercase';
                toggleBtn.onclick = () => {
                    closeModal('view-modal');
                    openToggleConfirm(t.id, 'Hidden', t.name);
                };
            }

            document.querySelectorAll('.action-dropdown').forEach(m => m.classList.add('hidden'));
            document.getElementById('view-modal').classList.add('open');
        }

        /* ── Confirm Modal ────────────────────────────────── */
        function openConfirmModal({
            title,
            message,
            iconName,
            iconBg,
            iconColor,
            btnBg,
            btnText,
            onConfirm
        }) {
            const modal = document.getElementById('confirmModal');
            const content = document.getElementById('confirmContent');
            document.getElementById('confirmTitle').innerText = title;
            document.getElementById('confirmMessage').innerHTML = message;
            document.getElementById('confirmIconContainer').className =
                `w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 ${iconBg}`;
            const icon = document.getElementById('confirmIcon');
            icon.setAttribute('data-lucide', iconName);
            icon.className = `w-8 h-8 ${iconColor}`;
            const yesBtn = document.getElementById('confirmYesBtn');
            yesBtn.className = `flex-1 py-2.5 ${btnBg} text-white rounded-lg text-xs font-bold transition-all uppercase hover:brightness-110`;
            yesBtn.innerText = btnText;
            yesBtn.onclick = () => {
                onConfirm();
                closeConfirmModal();
            };
            lucide.createIcons();
            modal.classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
        }

        function closeConfirmModal() {
            const content = document.getElementById('confirmContent');
            content.classList.add('scale-95');
            setTimeout(() => document.getElementById('confirmModal').classList.add('invisible'), 200);
        }

        function openDeleteConfirm(id, name) {
            document.querySelectorAll('.action-dropdown').forEach(m => m.classList.add('hidden'));
            openConfirmModal({
                title: 'Delete Testimonial',
                message: `Are you sure you want to <span class="font-bold text-red-600">delete</span> the testimonial of <b>${name}</b>?`,
                iconName: 'trash-2',
                iconBg: 'bg-red-100',
                iconColor: 'text-red-600',
                btnBg: 'bg-red-600',
                btnText: 'Yes, Delete',
                onConfirm: () => {
                    window.location.href = 'admin_delete_testimonial.php?id=' + id;
                }
            });
        }

        function openToggleConfirm(id, currentStatus, name) {
            document.querySelectorAll('.action-dropdown').forEach(m => m.classList.add('hidden'));
            const isPublished = currentStatus === 'Published';
            openConfirmModal({
                title: isPublished ? 'Hide Testimonial' : 'Publish Testimonial',
                message: isPublished ?
                    `Are you sure you want to <span class="font-bold text-slate-600">hide</span> the testimonial of <b>${name}</b>?` : `Are you sure you want to <span class="font-bold text-green-600">publish</span> the testimonial of <b>${name}</b>?`,
                iconName: isPublished ? 'eye-off' : 'send',
                iconBg: isPublished ? 'bg-slate-100' : 'bg-green-100',
                iconColor: isPublished ? 'text-slate-500' : 'text-green-600',
                btnBg: isPublished ? 'bg-[#0E0F3B]' : 'bg-green-600',
                btnText: isPublished ? 'Yes, Hide' : 'Yes, Publish',
                onConfirm: () => {
                    // Create and submit a form dynamically for single toggle
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/testimonials/${id}/post`; // adjust to your actual route
                    form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="PUT">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        /* ── Bulk Actions ─────────────────────────────────── */
        function getCheckedIds() {
            return [...document.querySelectorAll('.row-checkbox:checked')].map(cb => cb.dataset.id);
        }

        function updateBulkBar() {
            const ids = getCheckedIds();
            const bar = document.getElementById('bulk-actions');
            const count = document.getElementById('bulk-count');
            if (ids.length > 0) {
                bar.classList.remove('hidden');
                bar.classList.add('flex');
                count.textContent = ids.length + ' selected';
                lucide.createIcons();
            } else {
                bar.classList.remove('flex');
                bar.classList.add('hidden');
            }
        }

        function bulkAction(action) {
            const ids = getCheckedIds();
            if (ids.length === 0) return;
            const configs = {
                publish: {
                    title: 'Bulk Publish',
                    message: `Are you sure you want to <span class="font-bold text-green-600">publish</span> <b>${ids.length}</b> selected testimonial(s)?`,
                    iconName: 'send',
                    iconBg: 'bg-green-100',
                    iconColor: 'text-green-600',
                    btnBg: 'bg-green-600',
                    btnText: 'Yes, Publish',
                    onConfirm: () => {
                        document.getElementById('bulk-ids').value = ids.join(',');
                        document.getElementById('bulk-publish-form').submit();
                    }
                },
                hide: {
                    title: 'Bulk Hide',
                    message: `Are you sure you want to <span class="font-bold text-slate-600">hide</span> <b>${ids.length}</b> selected testimonial(s)?`,
                    iconName: 'eye-off',
                    iconBg: 'bg-slate-100',
                    iconColor: 'text-slate-500',
                    btnBg: 'bg-[#0E0F3B]',
                    btnText: 'Yes, Hide',
                    onConfirm: () => {
                        window.location.href = 'admin_bulk_testimonial.php?action=hide&ids=' + ids.join(',');
                    }
                },
                delete: {
                    title: 'Bulk Delete',
                    message: `Are you sure you want to <span class="font-bold text-red-600">delete</span> <b>${ids.length}</b> selected testimonial(s)?`,
                    iconName: 'trash-2',
                    iconBg: 'bg-red-100',
                    iconColor: 'text-red-600',
                    btnBg: 'bg-red-600',
                    btnText: 'Yes, Delete',
                    onConfirm: () => {
                        window.location.href = 'admin_bulk_testimonial.php?action=delete&ids=' + ids.join(',');
                    }
                }
            };
            const c = configs[action];
            openConfirmModal({
                title: c.title,
                message: c.message,
                iconName: c.iconName,
                iconBg: c.iconBg,
                iconColor: c.iconColor,
                btnBg: c.btnBg,
                btnText: c.btnText,
                onConfirm: c.onConfirm // <-- THIS was the bug, it was using c.href before
            });
        }
    </script>
    <!-- Place this just before </body> -->
    <form id="bulk-publish-form" action="{{ route('testimonials.bulkPost') }}" method="POST" class="hidden">
        @csrf
        @method('PUT')
        <input type="hidden" name="ids" id="bulk-ids">
    </form>
</body>

</html>