<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Alumni Directory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSectionBackground.png') }}");
        background-size: cover;
        background-position: center;
    }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
    }

    html,
    body {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<body>
    @php $current_page = Route::currentRouteName(); @endphp
    @include('partials.header-alumni')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Alumni Directory</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    @include('partials.success')

    <main class="max-w-6xl mx-auto p-6 pb-16">

        <!-- SEARCH & FILTER -->
        <form method="GET" action="{{ route('alumni.index') }}" class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 mb-8 flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <button type="submit" class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 hover:text-[#C73D1A]">
                    <i class="fas fa-search"></i>
                </button>
                <input type="text" name="search" id="alumniSearchInput" value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search by Last Name, First Name, or Middle Name"
                    class="w-full h-10 pl-11 pr-4 border rounded-full focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
            </div>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-graduation-cap"></i>
                </span>
                <select name="program" onchange="this.form.submit()"
                    class="h-10 pl-11 pr-10 border rounded-full bg-white appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A] w-full md:w-56">
                    <option value="">Course</option>
                    @foreach ($programs as $program)
                    <option value="{{ $program->program_id }}" {{ (string) ($filters['program'] ?? '') === (string) $program->program_id ? 'selected' : '' }}>
                        {{ $program->program_name }}
                    </option>
                    @endforeach
                </select>
                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </div>

            <div class="relative">
                <select name="batch" onchange="this.form.submit()"
                    class="h-10 pl-4 pr-10 border rounded-full bg-white appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A] w-full md:w-36">
                    <option value="">Batch</option>
                    @foreach ($batches as $batch)
                    <option value="{{ $batch }}" {{ (string) ($filters['batch'] ?? '') === (string) $batch ? 'selected' : '' }}>{{ $batch }}</option>
                    @endforeach
                </select>
                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </div>

            @if (array_filter($filters))
            <a href="{{ route('alumni.index') }}" title="Clear filters"
                class="shrink-0 w-10 h-10 flex items-center justify-center border rounded-lg text-gray-400 hover:text-[#C73D1A] hover:border-[#C73D1A] transition-colors">
                <i class="fas fa-filter-circle-xmark"></i>
            </a>
            @endif
        </form>

        <div id="alumniDirectoryResults">
            @include('partials.alumni-directory-table')
        </div>
        @include('partials.table-sort')

    </main>

    <!-- VIEW PROFILE MODAL -->
    <div id="profileModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="bg-[#0E0F3B] px-8 pt-6 pb-16 relative">
                <h2 class="text-white font-bold text-lg">Alumni Profile</h2>
                <button type="button" onclick="closeProfileModal()" class="absolute top-5 right-6 text-white/80 hover:text-white">
                    <i class="fas fa-times-circle text-xl"></i>
                </button>
            </div>
            <div class="flex justify-center -mt-12 relative z-10">
                <div class="w-24 h-24 rounded-full bg-orange-500 flex items-center justify-center shadow-md ring-4 ring-white overflow-hidden">
                    <img id="pm-photo" src="" class="hidden w-full h-full object-cover">
                    <i id="pm-photo-fallback" class="fas fa-user text-3xl text-white"></i>
                </div>
            </div>
            <div class="px-8 pt-4 pb-8 text-center">
                <h3 id="pm-name" class="text-xl font-bold text-[#0E0F3B]"></h3>
                <p id="pm-program" class="text-sm text-gray-500"></p>

                <div class="mt-6 text-left space-y-3 text-sm">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="font-bold text-[#C73D1A]">Batch</span>
                        <span id="pm-batch" class="text-gray-700"></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="font-bold text-[#C73D1A]">Section</span>
                        <span id="pm-section" class="text-gray-700"></span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                        <span class="font-bold text-[#C73D1A]">Employment</span>
                        <span class="text-right">
                            <span id="pm-employment" class="text-gray-700"></span>
                            <span id="pm-aligned" class="hidden ml-1 inline-flex items-center gap-1 bg-green-100 text-green-700 text-[9px] font-bold uppercase px-2 py-0.5 rounded-full align-middle">
                                <i class="fa-solid fa-circle-check"></i> Aligned with Course
                            </span>
                        </span>
                    </div>
                    <div id="pm-skills-row" class="border-b border-gray-100 pb-2">
                        <span class="font-bold text-[#C73D1A] block mb-1">Skills</span>
                        <span id="pm-skills" class="text-gray-700"></span>
                    </div>
                    <div id="pm-email-row" class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="font-bold text-[#C73D1A]">Email</span>
                        <a id="pm-email" href="#" class="text-blue-600 hover:underline"></a>
                    </div>
                    <div id="pm-contact-row" class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="font-bold text-[#C73D1A]">Contact</span>
                        <span id="pm-contact" class="text-gray-700"></span>
                    </div>
                    <div id="pm-linkedin-row" class="flex justify-between">
                        <span class="font-bold text-[#C73D1A]">LinkedIn</span>
                        <a id="pm-linkedin" href="#" target="_blank" class="text-blue-600 hover:underline">View Profile</a>
                    </div>
                </div>

                <button type="button" id="pm-message-btn"
                    class="mt-6 w-full bg-[#1D264F] hover:bg-[#0E0F3B] text-white text-sm font-bold py-2.5 rounded-lg transition-colors uppercase">
                    <i class="fas fa-comment mr-1"></i> Message
                </button>
            </div>
        </div>
    </div>

    @include('partials.footer-alumni')

    <script>
        // ── Live search: fetches the results fragment as the user types
        // (debounced) instead of requiring Enter/a page reload. Reuses the
        // pvSearchAjax()/ajax-mode machinery already built into
        // partials/table-pagination-bar.blade.php — same fetch, swap, and
        // history.replaceState it already does for an ajax-mode search box. ──
        (function () {
            const searchInput = document.getElementById('alumniSearchInput');
            if (!searchInput) return;

            let debounceTimer;
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                const value = this.value;
                debounceTimer = setTimeout(function () {
                    const root = document.querySelector('[data-pv-pagination][data-pv-id="alumniDirectory"]');
                    if (root) window.pvSearchAjax('search', value, root);
                }, 350);
            });
        })();

        function messageAlumnus(alumnusUserId) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('messages.start') }}";
            form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                '<input type="hidden" name="recipient_id" value="' + alumnusUserId + '">';
            document.body.appendChild(form);
            form.submit();
        }

        function toggleDirectoryDropdown(btn) {
            const dropdown = btn.nextElementSibling;
            const isHidden = dropdown.classList.contains('hidden');
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));
            if (isHidden) dropdown.classList.remove('hidden');
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.relative.inline-block')) {
                document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));
            }
        });

        function openProfileModal(data) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));

            document.getElementById('pm-name').textContent = data.name;
            document.getElementById('pm-program').textContent = data.program;
            document.getElementById('pm-batch').textContent = data.batch;
            document.getElementById('pm-section').textContent = data.section;
            document.getElementById('pm-employment').textContent = data.employment + (data.industry ? ' — ' + data.industry : '');
            document.getElementById('pm-aligned').classList.toggle('hidden', !data.aligned);
            document.getElementById('pm-message-btn').onclick = function () { messageAlumnus(data.id); };

            const skillsRow = document.getElementById('pm-skills-row');
            if (data.skills) {
                skillsRow.classList.remove('hidden');
                document.getElementById('pm-skills').textContent = data.skills;
            } else {
                skillsRow.classList.add('hidden');
            }

            const emailRow = document.getElementById('pm-email-row');
            if (data.email) {
                emailRow.classList.remove('hidden');
                document.getElementById('pm-email').textContent = data.email;
                document.getElementById('pm-email').href = 'mailto:' + data.email;
            } else {
                emailRow.classList.add('hidden');
            }

            const photo = document.getElementById('pm-photo');
            const fallback = document.getElementById('pm-photo-fallback');
            if (data.photo) {
                photo.src = data.photo;
                photo.classList.remove('hidden');
                fallback.classList.add('hidden');
            } else {
                photo.classList.add('hidden');
                fallback.classList.remove('hidden');
            }

            const contactRow = document.getElementById('pm-contact-row');
            if (data.contact) {
                contactRow.classList.remove('hidden');
                document.getElementById('pm-contact').textContent = data.contact;
            } else {
                contactRow.classList.add('hidden');
            }

            const linkedinRow = document.getElementById('pm-linkedin-row');
            if (data.linkedin) {
                linkedinRow.classList.remove('hidden');
                document.getElementById('pm-linkedin').href = data.linkedin;
            } else {
                linkedinRow.classList.add('hidden');
            }

            document.getElementById('profileModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeProfileModal() {
            document.getElementById('profileModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        window.addEventListener('click', function (event) {
            if (event.target === document.getElementById('profileModal')) closeProfileModal();
        });
    </script>
</body>

</html>
