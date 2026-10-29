<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Alumni Directory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSection.svg') }}");
        background-size: cover;
        background-position: center;
    }

    /* View Profile modal banner — same campus photo as the Alumni Profile page's card banner. */
    .pm-banner {
        background: url("{{ asset('assets/heroSection.svg') }}") center / cover;
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

<body class="min-h-screen flex flex-col">
    @php $current_page = Route::currentRouteName(); @endphp
    @include('partials.header-alumni')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Alumni Directory</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    @include('partials.success')

    @include('partials.error')
    <main class="max-w-6xl mx-auto p-6 pb-16 flex-1">

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

    <!-- VIEW PROFILE MODAL — same banner + overlapping photo card as the Alumni Profile page -->
    <div id="profileModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden max-h-[90vh] overflow-y-auto font-['Montserrat']" role="dialog" aria-modal="true" aria-labelledby="pm-name">
            <div class="pm-banner relative h-24 sm:h-28">
                <span class="absolute top-4 left-5 sm:left-6 text-[11px] font-bold uppercase tracking-widest text-white/90">Alumni Profile</span>
                <button type="button" onclick="closeProfileModal()" aria-label="Close"
                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 text-[#0E0F3B] hover:bg-white flex items-center justify-center shadow transition-colors">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <div class="px-5 sm:px-6 pb-6">
                <div class="flex flex-col items-center text-center">
                    <div class="relative z-10 -mt-12 w-24 h-24 rounded-full bg-[#0E0F3B] border-4 border-white shadow-md overflow-hidden flex items-center justify-center">
                        <img id="pm-photo" src="" alt="Profile photo" class="hidden w-full h-full object-cover">
                        <i id="pm-photo-fallback" class="fas fa-user text-3xl text-white mt-1"></i>
                    </div>
                    <h3 id="pm-name" class="mt-3 text-lg sm:text-xl font-bold text-[#0E0F3B] break-words"></h3>
                    <p id="pm-program" class="mt-1 text-xs sm:text-sm font-semibold uppercase text-[#ED7A07] break-words"></p>
                    <p id="pm-college" class="mt-0.5 text-xs text-slate-500 break-words"></p>
                    <div class="mt-3 flex flex-wrap justify-center gap-1.5">
                        <span id="pm-employment-badge" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wide"></span>
                        <span id="pm-aligned" class="hidden inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold uppercase tracking-wide">
                            <i class="fa-solid fa-circle-check"></i> Aligned with Course
                        </span>
                    </div>
                </div>

                <dl class="mt-5 pt-4 border-t border-slate-100 space-y-3.5 text-left">
                    <div class="flex items-start gap-3">
                        <span class="w-8 h-8 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center text-xs"><i class="fa-solid fa-calendar"></i></span>
                        <div class="min-w-0 flex-1 grid grid-cols-2 gap-3">
                            <div>
                                <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Batch</dt>
                                <dd id="pm-batch" class="text-sm font-semibold text-[#0E0F3B]"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Section</dt>
                                <dd id="pm-section" class="text-sm font-semibold text-[#0E0F3B]"></dd>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-8 h-8 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center text-xs"><i class="fa-solid fa-briefcase"></i></span>
                        <div class="min-w-0">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Employment</dt>
                            <dd id="pm-employment" class="text-sm font-semibold text-[#0E0F3B] break-words"></dd>
                        </div>
                    </div>
                    <div id="pm-skills-row" class="flex items-start gap-3">
                        <span class="w-8 h-8 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center text-xs"><i class="fa-solid fa-lightbulb"></i></span>
                        <div class="min-w-0 flex-1">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Skills</dt>
                            <dd id="pm-skills" class="mt-1 flex flex-wrap gap-1.5"></dd>
                        </div>
                    </div>
                    <div id="pm-email-row" class="flex items-start gap-3">
                        <span class="w-8 h-8 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center text-xs"><i class="fa-solid fa-envelope"></i></span>
                        <div class="min-w-0">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Email</dt>
                            <dd class="text-sm font-semibold break-all"><a id="pm-email" href="#" class="text-[#1D46A4] hover:underline"></a></dd>
                        </div>
                    </div>
                    <div id="pm-contact-row" class="flex items-start gap-3">
                        <span class="w-8 h-8 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center text-xs"><i class="fa-solid fa-phone"></i></span>
                        <div class="min-w-0">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Contact</dt>
                            <dd id="pm-contact" class="text-sm font-semibold text-[#0E0F3B] break-words"></dd>
                        </div>
                    </div>
                    <div id="pm-linkedin-row" class="flex items-start gap-3">
                        <span class="w-8 h-8 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center text-xs"><i class="fa-brands fa-linkedin-in"></i></span>
                        <div class="min-w-0">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">LinkedIn</dt>
                            <dd class="text-sm font-semibold"><a id="pm-linkedin" href="#" target="_blank" rel="noopener" class="text-[#1D46A4] hover:underline">View Profile <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-0.5"></i></a></dd>
                        </div>
                    </div>
                </dl>

                <button type="button" id="pm-message-btn"
                    class="mt-6 w-full inline-flex items-center justify-center gap-2 bg-[#1D264F] hover:bg-[#0E0F3B] text-white text-xs font-bold tracking-wider py-2.5 rounded-lg transition-colors uppercase">
                    <i class="fas fa-comment"></i> Message
                </button>
            </div>
        </div>
    </div>

    @include('partials.action-dropdown-fix')
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
            if (isHidden) {
                dropdown.classList.remove('hidden');
                positionFixedDropdown(btn, dropdown);
            }
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.relative.inline-block')) {
                document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));
            }
        });

        // Shows the value, or a muted "Not provided" when there isn't one.
        function setValue(id, value) {
            const el = document.getElementById(id);
            if (value) { el.textContent = value; el.classList.remove('italic', 'font-normal', 'text-slate-400'); }
            else { el.textContent = 'Not provided'; el.classList.add('italic', 'font-normal', 'text-slate-400'); }
        }

        function openProfileModal(data) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));

            document.getElementById('pm-name').textContent = data.name;
            document.getElementById('pm-program').textContent = data.program;
            const college = document.getElementById('pm-college');
            college.textContent = data.college && data.college !== 'Not specified' ? data.college : '';
            college.classList.toggle('hidden', !college.textContent);
            setValue('pm-batch', data.batch);
            setValue('pm-section', data.section);
            document.getElementById('pm-employment').textContent = data.employment + (data.industry ? ' — ' + data.industry : '');
            const employed = data.employment === 'Employed';
            const badge = document.getElementById('pm-employment-badge');
            badge.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wide '
                + (employed ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-50 text-slate-600 border-slate-200');
            badge.innerHTML = '<i class="fa-solid fa-briefcase"></i> ' + (employed ? 'Employed' : 'Unemployed');
            document.getElementById('pm-aligned').classList.toggle('hidden', !data.aligned);
            document.getElementById('pm-message-btn').onclick = function () { messageAlumnus(data.id); };

            const skillsRow = document.getElementById('pm-skills-row');
            if (data.skills) {
                skillsRow.classList.remove('hidden');
                // Comma list -> chips (textContent only — skill names are user-entered)
                const skillsEl = document.getElementById('pm-skills');
                skillsEl.replaceChildren(...String(data.skills).split(',').map(x => x.trim()).filter(Boolean).map(name => {
                    const chip = document.createElement('span');
                    chip.className = 'text-xs font-semibold text-[#1D264F] bg-[#1D264F]/5 border border-[#1D264F]/10 rounded-full px-3 py-1';
                    chip.textContent = name;
                    return chip;
                }));
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
