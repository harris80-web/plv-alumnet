@php
    $current_page = 'super_admin_profile';
    $office = $user->office;
    $isSuperAdmin = $user->user_role === 'super_admin';
    $roleLabel = ucwords(str_replace('_', ' ', $user->user_role));
    $fullName = trim(implode(' ', array_filter([$user->user_first_name, $user->user_middle_name, $user->user_last_name, $user->user_suffix && $user->user_suffix !== 'N/A' ? $user->user_suffix : null])));
    $birthDate = $office?->office_birth_date ? \Carbon\Carbon::parse($office->office_birth_date) : null;
    $notProvided = '<span class="font-normal italic text-slate-400">Not provided</span>';
    $permissionIcons = \App\Models\Office::permissionIcons();
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isSuperAdmin ? 'Super Admin' : 'Admin' }} Profile | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    {{-- The success / error toasts (partials.success, partials.error) use Font Awesome icons. --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .dash-scroll { overflow-y: auto; flex: 1; scrollbar-width: none; -ms-overflow-style: none; }
        .dash-scroll::-webkit-scrollbar { display: none; }

        /* Profile card banner: same campus photo as the Alumni / Employer profile cards. */
        .profile-banner { background: url("{{ asset('assets/heroSection.svg') }}") center / cover; }

        /* Edit-mode fields: same look as the Edit Employer / Alumni Profile forms. */
        .profile-field {
            width: 100%;
            padding: 0.5rem 0.8rem;
            background: #fff;
            border: 1px solid #0E0F3B;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #0E0F3B;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .profile-field:focus { border-color: #C73D1A; box-shadow: 0 0 0 3px rgba(199, 61, 26, 0.12); }
        .req-mark { margin-left: 2px; color: #C73D1A; }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">
        @include('partials.super-admin-side-bar')

        <main class="flex-1 flex flex-col overflow-hidden min-w-0">
            @include('partials.super-admin-header')

            <div class="dash-scroll bg-slate-100 px-4 py-6 sm:px-6 lg:px-10">

                @if ($errors->any())
                <div class="max-w-6xl mx-auto mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                    <i data-lucide="circle-alert" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    <div>@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
                </div>
                @endif
                @include('partials.success')
                @include('partials.error')

                <form id="profileForm" action="{{ route('offices.updateProfile', $user->user_id) }}" method="POST"
                    enctype="multipart/form-data" class="max-w-6xl mx-auto">
                    @csrf
                    @method('PUT')

                    {{-- Action row: EDIT PROFILE on the right (same button as the Alumni / Employer
                         profiles; the page title already lives in the header). Edit mode swaps it
                         for Cancel / Save Changes. Phones: full width. --}}
                    <div class="flex flex-wrap items-center justify-end gap-x-4 gap-y-3 mb-5 sm:mb-6">
                        <div id="viewActions" class="w-full sm:w-auto">
                            <button type="button" onclick="toggleEdit(true)"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#ED7A07] hover:bg-[#C73D1A] text-white px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg font-normal text-[11px] sm:text-xs tracking-wider sm:tracking-widest shadow-lg transition-colors whitespace-nowrap">
                                <i data-lucide="square-pen" class="w-4 h-4"></i> EDIT PROFILE
                            </button>
                        </div>
                        <div id="editActions" class="hidden w-full sm:w-auto flex items-center gap-2 sm:gap-3">
                            <button type="button" onclick="toggleEdit(false)"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg border border-[#0E0F3B] text-[#0E0F3B] font-bold text-[11px] sm:text-xs tracking-wider sm:tracking-widest hover:bg-[#0E0F3B] hover:text-white transition-colors whitespace-nowrap">
                                CANCEL
                            </button>
                            <button type="submit"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-[#1D264F] hover:bg-[#0E0F3B] text-white px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg font-bold text-[11px] sm:text-xs tracking-wider sm:tracking-widest shadow-lg transition-colors whitespace-nowrap">
                                <i data-lucide="save" class="w-4 h-4"></i> SAVE CHANGES
                            </button>
                        </div>
                    </div>

                    {{-- ===== Profile card ===== --}}
                    <section class="bg-white rounded-2xl shadow-sm border border-slate-100" aria-label="Administrator overview">
                        <div class="profile-banner h-24 sm:h-28 rounded-t-2xl"></div>
                        <div class="px-5 sm:px-8 pb-6">
                            <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-6">
                                <div class="relative z-10 -mt-12 w-24 h-24 sm:w-28 sm:h-28 shrink-0">
                                    <div class="w-full h-full rounded-full bg-[#0E0F3B] border-4 border-white shadow-md overflow-hidden flex items-center justify-center">
                                        <img id="profileImagePreview"
                                            src="{{ $user->user_profile_picture ? asset('storage/' . $user->user_profile_picture) : '' }}"
                                            alt="Profile Picture"
                                            class="w-full h-full object-cover {{ $user->user_profile_picture ? '' : 'hidden' }}">
                                        <i id="profileImagePlaceholderIcon" data-lucide="user"
                                            class="w-12 h-12 text-white {{ $user->user_profile_picture ? 'hidden' : '' }}"></i>
                                    </div>
                                    <!-- Camera button (edit mode) -->
                                    <button type="button" id="cameraBtn" aria-label="Change profile photo"
                                        class="hidden absolute bottom-0 right-0 w-8 h-8 rounded-full bg-[#C73D1A] text-white border-2 border-white shadow flex items-center justify-center hover:bg-[#a83215] transition">
                                        <i data-lucide="camera" class="w-4 h-4"></i>
                                    </button>
                                    <div id="photoMenu"
                                        class="hidden absolute top-full mt-2 left-0 w-52 bg-white border border-slate-100 rounded-xl shadow-xl p-2 z-20 text-[#0E0F3B] font-medium">
                                        <button type="button" onclick="openImageLightbox(document.getElementById('profileImagePreview').src)"
                                            class="flex items-center gap-3 w-full p-2.5 hover:bg-slate-100 rounded-lg text-sm transition-colors">
                                            <i data-lucide="image" class="w-4 h-4 text-slate-500"></i> See Profile Image
                                        </button>
                                        <label class="flex items-center gap-3 w-full p-2.5 hover:bg-slate-100 rounded-lg text-sm cursor-pointer transition-colors">
                                            <i data-lucide="upload" class="w-4 h-4 text-slate-500"></i> Choose Profile Image
                                            <input type="file" name="user_profile_picture" accept="image/*" class="hidden"
                                                onchange="previewImageInput(this, 'profileImagePreview', 'profileImagePlaceholderIcon')">
                                        </label>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0 sm:pt-4">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                                        <h3 class="text-lg sm:text-2xl font-bold text-[#0E0F3B] break-words">{{ $user->user_first_name }} {{ $user->user_last_name }}</h3>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $isSuperAdmin ? 'bg-orange-50 text-[#C73D1A] border-orange-200' : 'bg-[#1D264F]/5 text-[#1D264F] border-[#1D264F]/15' }}">
                                            <i data-lucide="shield-check" class="w-3 h-3"></i> {{ $roleLabel }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $user->user_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $user->user_active ? 'bg-green-500' : 'bg-slate-400' }}"></span> {{ $user->user_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <ul class="mt-2.5 flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-slate-500">
                                        <li class="inline-flex items-center gap-1.5 min-w-0"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i> <span class="break-words">{{ $office?->office_address ?: 'Address not provided' }}</span></li>
                                        <li class="inline-flex items-center gap-1.5 min-w-0"><i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i> <span class="break-all">{{ $user->user_email }}</span></li>
                                        @if ($user->user_number)
                                        <li class="inline-flex items-center gap-1.5"><i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i> {{ $user->user_number }}</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <dl class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-3 gap-2 sm:gap-3 text-center sm:text-left">
                                <div class="sm:flex sm:items-center sm:gap-3">
                                    <span class="hidden sm:flex w-10 h-10 shrink-0 rounded-xl bg-[#1D264F]/5 text-[#1D264F] items-center justify-center"><i data-lucide="shield-check" class="w-5 h-5"></i></span>
                                    <div>
                                        <dd class="text-base sm:text-xl font-bold text-[#0E0F3B] leading-tight">{{ $isSuperAdmin ? 'Full' : count($office?->permissions ?? []) . ' / ' . count(\App\Models\Office::PERMISSIONS) }}</dd>
                                        <dt class="text-[11px] font-semibold uppercase tracking-normal sm:tracking-wide text-slate-400">Feature Access</dt>
                                    </div>
                                </div>
                                <div class="sm:flex sm:items-center sm:gap-3">
                                    <span class="hidden sm:flex w-10 h-10 shrink-0 rounded-xl bg-amber-50 text-amber-500 items-center justify-center"><i data-lucide="activity" class="w-5 h-5"></i></span>
                                    <div>
                                        <dd class="text-base sm:text-xl font-bold text-[#0E0F3B] leading-tight">{{ $user->isOnline() ? 'Online' : ($user->last_active_at?->diffForHumans() ?? '—') }}</dd>
                                        <dt class="text-[11px] font-semibold uppercase tracking-normal sm:tracking-wide text-slate-400">Last Active</dt>
                                    </div>
                                </div>
                                <div class="sm:flex sm:items-center sm:gap-3">
                                    <span class="hidden sm:flex w-10 h-10 shrink-0 rounded-xl bg-orange-50 text-[#C73D1A] items-center justify-center"><i data-lucide="calendar-check" class="w-5 h-5"></i></span>
                                    <div>
                                        <dd class="text-base sm:text-xl font-bold text-[#0E0F3B] leading-tight">{{ $user->created_at?->format('M Y') ?? '—' }}</dd>
                                        <dt class="text-[11px] font-semibold uppercase tracking-normal sm:tracking-wide text-slate-400">Member Since</dt>
                                    </div>
                                </div>
                            </dl>
                        </div>
                    </section>

                    <div class="mt-6 space-y-6">

                        {{-- ===== Personal information (view / edit), full width ===== --}}
                        <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                            <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                                <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i data-lucide="user" class="w-4 h-4"></i></span>
                                <div>
                                    <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Personal Information</h3>
                                    <p class="text-xs text-slate-500">Your name, contact details and address.</p>
                                </div>
                            </header>
                            @php
                                $fields = [
                                    ['First Name', 'user_first_name', 'text', $user->user_first_name, $user->user_first_name, true],
                                    ['Middle Name', 'user_middle_name', 'text', $user->user_middle_name, $user->user_middle_name, false],
                                    ['Last Name', 'user_last_name', 'text', $user->user_last_name, $user->user_last_name, true],
                                    ['Suffix', 'user_suffix', 'text', $user->user_suffix, $user->user_suffix ?: 'N/A', false],
                                    ['Date of Birth', 'office_birth_date', 'date', $birthDate?->format('Y-m-d'), $birthDate?->format('F j, Y'), false],
                                    ['Email Address', 'user_email', 'email', $user->user_email, $user->user_email, true],
                                    ['Phone Number', 'user_number', 'text', $user->user_number, $user->user_number, false],
                                    ['Address', 'office_address', 'text', $office?->office_address, $office?->office_address, false],
                                ];
                            @endphp
                            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-5 p-5 sm:p-6">
                                @foreach ($fields as [$label, $name, $type, $value, $display, $required])
                                <div class="min-w-0">
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">
                                        <label for="pf-{{ $name }}">{{ $label }}</label>@if ($required)<span class="req-mark edit-mode hidden" aria-hidden="true">*</span>@endif
                                    </dt>
                                    <dd class="mt-1 text-sm font-semibold text-[#0E0F3B] break-words">
                                        <span class="view-mode">{!! $display ? e($display) : $notProvided !!}</span>
                                        <input type="{{ $type }}" id="pf-{{ $name }}" name="{{ $name }}" value="{{ $value }}" {{ $required ? 'required' : '' }}
                                            class="edit-mode hidden profile-field">
                                    </dd>
                                </div>
                                @endforeach
                            </dl>
                        </section>

                        {{-- ===== Below it: System & Security and Feature Access, side by side ===== --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                                <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                                    <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i data-lucide="lock-keyhole" class="w-4 h-4"></i></span>
                                    <div>
                                        <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">System &amp; Security</h3>
                                        <p class="text-xs text-slate-500">Account role and activity.</p>
                                    </div>
                                </header>
                                <dl class="p-5 sm:p-6 space-y-3.5">
                                    @foreach ([
                                        ['shield-check', 'User Role', e($roleLabel)],
                                        ['badge-check', 'Account Status', $user->user_active ? '<span class="text-green-700">Active</span>' : '<span class="text-slate-500">Inactive</span>'],
                                        ['calendar-plus', 'Date Created', e($user->created_at?->format('F j, Y') ?? '—')],
                                        ['clock', 'Last Active', $user->isOnline() ? '<span class="inline-flex items-center gap-1.5 text-green-700"><span class="w-2 h-2 rounded-full bg-green-500"></span> Online now</span>' : e($user->last_active_at?->diffForHumans() ?? 'Not recorded yet')],
                                    ] as [$icon, $label, $value])
                                    <div class="flex items-start gap-3">
                                        <span class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 text-slate-500 flex items-center justify-center"><i data-lucide="{{ $icon }}" class="w-4 h-4"></i></span>
                                        <div class="min-w-0">
                                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">{{ $label }}</dt>
                                            <dd class="text-sm font-semibold text-[#0E0F3B]">{!! $value !!}</dd>
                                        </div>
                                    </div>
                                    @endforeach
                                </dl>
                            </section>

                            <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                                <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                                    <span class="w-9 h-9 shrink-0 rounded-lg bg-orange-50 text-[#C73D1A] flex items-center justify-center"><i data-lucide="key-round" class="w-4 h-4"></i></span>
                                    <div>
                                        <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Feature Access</h3>
                                        <p class="text-xs text-slate-500">{{ $isSuperAdmin ? 'Super admins can use every admin feature.' : 'Admin features this account can use.' }}</p>
                                    </div>
                                </header>
                                <ul class="p-5 sm:p-6 space-y-2">
                                    @foreach (\App\Models\Office::PERMISSIONS as $key => $label)
                                    @php $allowed = $isSuperAdmin || $office?->hasPermission($key); @endphp
                                    <li class="flex items-center gap-2.5 text-sm {{ $allowed ? 'text-[#0E0F3B] font-medium' : 'text-slate-400' }}">
                                        <i data-lucide="{{ $permissionIcons[$key] ?? 'circle' }}" class="w-4 h-4 shrink-0 {{ $allowed ? 'text-[#1D264F]' : 'text-slate-300' }}"></i>
                                        <span class="flex-1 min-w-0">{{ $label }}</span>
                                        <i data-lucide="{{ $allowed ? 'circle-check' : 'circle-minus' }}" class="w-4 h-4 shrink-0 {{ $allowed ? 'text-green-600' : 'text-slate-300' }}"></i>
                                    </li>
                                    @endforeach
                                </ul>
                            </section>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    @include('partials.image-lightbox')

    <script>
        lucide.createIcons();

        const cameraBtn = document.getElementById('cameraBtn');
        const photoMenu = document.getElementById('photoMenu');

        function toggleEdit(isEdit) {
            document.getElementById('viewActions').classList.toggle('hidden', isEdit);
            document.getElementById('editActions').classList.toggle('hidden', !isEdit);
            document.querySelectorAll('.view-mode').forEach(el => el.classList.toggle('hidden', isEdit));
            document.querySelectorAll('.edit-mode').forEach(el => el.classList.toggle('hidden', !isEdit));
            cameraBtn.classList.toggle('hidden', !isEdit);
            if (!isEdit) {
                photoMenu.classList.add('hidden');
                document.getElementById('profileForm').reset(); // Cancel drops unsaved edits
            }
        }

        cameraBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            photoMenu.classList.toggle('hidden');
        });

        window.addEventListener('click', (e) => {
            if (!photoMenu.contains(e.target)) photoMenu.classList.add('hidden');
        });

        // A failed save comes back with errors: reopen edit mode so the fields are right there.
        // Same when arriving from Edit Profile (/profile/edit -> ?edit=1).
        @if ($errors->any() || request()->boolean('edit'))
        toggleEdit(true);
        @endif
    </script>
</body>

</html>
