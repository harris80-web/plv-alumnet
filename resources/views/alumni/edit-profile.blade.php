<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Edit Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSectionBackground.png') }}");
        background-size: cover;
        background-position: center;
    }
</style>

<body>
    @include('partials.header-alumni')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <main class="max-w-5xl mx-auto mt-10 mb-12 px-4">
        <form action="{{ route('alumni.updateProfile', $user->user_id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden p-8 md:p-12">
            @csrf
            @method('PUT')
            <h2 class="w-fit mx-auto text-center text-3xl font-bold mb-10 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                EDIT PROFILE
            </h2>
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @if ($errors->any())
                    <div class="bg-red-50 border border-red-300 border-l-4 border-l-red-600 rounded-md px-4 py-3 mb-4">
                        <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </ul>
            </div>
            @endif
            @include('partials.success')
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                <div class="md:col-span-3 flex justify-center md:justify-start">
                    <div class="relative w-40 h-40">

                        <div class="w-full h-full bg-[#0E0F3B] rounded-full flex items-center justify-center border-4 border-white shadow-lg overflow-hidden">
                            @if ($user->user_profile_picture)
                            <img src="{{ asset('storage/' . $user->user_profile_picture) }}" alt="Profile Picture" class="w-full h-full object-cover">
                            @else
                            <i class="fa-solid fa-user text-7xl text-white mt-4"></i>
                            @endif

                        </div>

                        <div class="absolute bottom-1 right-1">
                            <button type="button" onclick="togglePhotoOptions(event)" class="bg-gray-600 text-white p-2 rounded-full border-2 border-white hover:bg-gray-800 transition z-10 shadow-md">
                                <i class="fa-solid fa-camera text-xs"></i>
                            </button>

                            <div id="photoOptions" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50">
                                <button class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] hover:font-bold hover:bg-gray-100 flex items-center gap-3">
                                    <i class="fa-solid fa-image text-[#0E0F3B]"></i> View Profile Image
                                </button>
                                <label for="user_profile_picture" class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] hover:font-bold hover:bg-gray-100 flex items-center gap-3 cursor-pointer mb-0">
                                    <i class="fa-solid fa-upload text-[#0E0F3B]"></i> Upload an Image
                                    <input type="file" name="user_profile_picture" id="user_profile_picture" class="hidden">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-9 space-y-4">
                    {{-- Row 1: identity fields, 4 across --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-y-4 gap-x-4">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-orange-600 uppercase">Last Name</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">{{ $user->user_last_name }}</h3>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-orange-600 uppercase">First Name</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">{{ $user->user_first_name }}</h3>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-orange-600 uppercase">Middle Name</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">{{ $user->user_middle_name }}</h3>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-orange-600 uppercase">Suffix</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">{{ $user->user_suffix }}</h3>
                        </div>
                    </div>

                    {{-- Row 2: Gender and Batch --}}
                    <div class="grid grid-cols-2 gap-y-4 gap-x-4">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-orange-600 uppercase">Gender</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">
                                {{ \App\Models\Alumnus::genderLabels()[$user->alumnus->alumnus_gender] ?? 'Not specified' }}
                            </h3>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-orange-600 uppercase">Batch</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">{{ $user->alumnus->alumnus_batch ?? 'Not specified' }}</h3>
                        </div>
                    </div>

                    {{-- Row 3: Program alone, full width --}}
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-orange-600 uppercase">Program</p>
                        <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate"
                            title="{{ $user->alumnus->program->program_name ?? 'Not specified' }}">
                            {{ $user->alumnus->program->program_name ?? 'Not specified' }}</h3>
                    </div>
                </div>
            </div>

            <div class="mt-12 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="alumnus_employment_status" class="text-xs font-bold text-orange-600 uppercase block mb-1">Employment Status</label>
                        <select name="alumnus_employment_status" id="alumnus_employment_status" onchange="toggleEmploymentFields(this.value)" class="w-full py-1.5 px-2 border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                            <option value="1" {{ $user->alumnus->alumnus_employment_status == 1 ? 'selected' : '' }}>Employed</option>
                            <option value="0" {{ $user->alumnus->alumnus_employment_status == 0 ? 'selected' : '' }}>Unemployed</option>
                        </select>
                    </div>
                    <div>
                        <label for="user_email" class="text-xs font-bold text-orange-600 uppercase block mb-1">Email</label>
                        <input type="email" name="user_email" placeholder="example@email.com" value="{{ $user->user_email }}" class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                    </div>
                </div>

                {{-- Only relevant once "Employed" is picked above — laid out as a
                     neat 2-column grid of its own instead of one long stacked
                     column, so it doesn't dominate the page when expanded. --}}
                <div id="employment-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ $user->alumnus->alumnus_employment_status ? '' : 'hidden' }}">
                    <div>
                        <label for="industry_id" class="text-xs font-bold text-orange-600 uppercase block mb-1">Industry / Sector</label>
                        <select name="industry_id" id="industry_id" required class="w-full py-1.5 px-2 border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                            <option value="" disabled {{ $user->alumnus->industry_id ? '' : 'selected' }}>Select Industry / Sector</option>
                            @foreach($industries as $industry)
                            <option value="{{ $industry->industry_id }}" {{ $user->alumnus->industry_id == $industry->industry_id ? 'selected' : '' }}>
                                {{ $industry->industry_name }}
                            </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set automatically when you're hired through a job post here — change it yourself if your job didn't come from the system.
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="alumnus_workplace" class="text-xs font-bold text-orange-600 uppercase">Where Do You Work?</label>
                            <button type="button" id="workplaceUndisclosedBtn" onclick="toggleWorkplaceUndisclosed()"
                                class="text-[10px] font-bold uppercase px-2 py-1 rounded border border-[#0E0F3B] text-[#0E0F3B] hover:bg-[#0E0F3B] hover:text-white transition shrink-0">
                                <span id="workplaceUndisclosedLabel">{{ $user->alumnus->alumnus_workplace_undisclosed ? 'Disclose Workplace' : "Don't Disclose" }}</span>
                            </button>
                        </div>
                        <input type="hidden" name="alumnus_workplace_undisclosed" id="alumnus_workplace_undisclosed" value="{{ $user->alumnus->alumnus_workplace_undisclosed ? 1 : 0 }}">
                        <input type="text" name="alumnus_workplace" id="alumnus_workplace" placeholder="Company / Organization name"
                            value="{{ $user->alumnus->alumnus_workplace }}"
                            {{ $user->alumnus->alumnus_workplace_undisclosed ? 'disabled' : '' }}
                            class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set automatically when you're hired through a job post here — only fill this in yourself if your job didn't come from the system. Optional, and you can mark it undisclosed and just leave your industry above set.
                        </p>
                    </div>

                    <div>
                        <label for="alumnus_job_position" class="text-xs font-bold text-orange-600 uppercase block mb-1">Job Position</label>
                        @if($user->alumnus->alumnus_employed_via_platform)
                        <input type="text" value="{{ $user->alumnus->alumnus_job_position }}" disabled
                            class="w-full border border-[#0E0F3B] rounded-md p-2 bg-gray-100 text-gray-400 cursor-not-allowed">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set from the job posting you were hired through here — not editable while that's your current job.
                        </p>
                        @else
                        <input type="text" name="alumnus_job_position" id="alumnus_job_position" placeholder="e.g. Software Engineer"
                            value="{{ $user->alumnus->alumnus_job_position }}"
                            class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set automatically when you're hired through a job post here — fill this in yourself if your job didn't come from the system.
                        </p>
                        @endif
                    </div>

                    <div>
                        <label for="alumnus_employment_date" class="text-xs font-bold text-orange-600 uppercase block mb-1">Employment Date</label>
                        @if($user->alumnus->alumnus_employed_via_platform)
                        <input type="date" value="{{ optional($user->alumnus->alumnus_employment_date)->format('Y-m-d') }}" disabled
                            class="w-full border border-[#0E0F3B] rounded-md p-2 bg-gray-100 text-gray-400 cursor-not-allowed">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set when you were hired through this job post — not editable while that's your current job.
                        </p>
                        @else
                        <input type="date" name="alumnus_employment_date" id="alumnus_employment_date" max="{{ now()->format('Y-m-d') }}"
                            value="{{ optional($user->alumnus->alumnus_employment_date)->format('Y-m-d') }}"
                            class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                        <p class="text-[10px] text-gray-400 mt-1">
                            When you started your current job — not the same as your date of first job below. Set automatically when hired through a job post here.
                        </p>
                        @endif
                    </div>

                    <div>
                        <label for="alumnus_first_job_date" class="text-xs font-bold text-orange-600 uppercase block mb-1">Date of First Job</label>
                        @if($user->alumnus->alumnus_first_job_date)
                        <input type="date" value="{{ $user->alumnus->alumnus_first_job_date->format('Y-m-d') }}" disabled
                            class="w-full border border-[#0E0F3B] rounded-md p-2 bg-gray-100 text-gray-400 cursor-not-allowed">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Already recorded — this can only be set once.
                        </p>
                        @else
                        <input type="date" name="alumnus_first_job_date" max="{{ now()->format('Y-m-d') }}"
                            class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set automatically when you're hired through a job post here — only fill this in yourself if your first job didn't come from the system. You can only set this once.
                        </p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="user_number" class="text-xs font-bold text-orange-600 uppercase block mb-1">Contact No.</label>
                        <input type="text" name="user_number" placeholder="09XXXXXXXXX" value="{{ $user->user_number }}" class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                    </div>
                    <div>
                        {{-- Skills are managed via the chip-search UI in the Resume
                             section below (a many-to-many relation, not a plain
                             field this form posts) — shown read-only here just to
                             mirror the mockup's layout, same treatment as
                             Program/Batch above. --}}
                        <p class="text-xs font-bold text-orange-600 uppercase block mb-1">Skills</p>
                        <p class="w-full border border-gray-200 bg-gray-50 rounded-md p-2 text-sm text-[#0E0F3B] min-h-[38px]">
                            {{ $user->alumnus->skills->pluck('skill_name')->join(', ') ?: 'Add skills from the Resume section below' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between md:w-1/2">
                    <span class="text-xs font-bold text-orange-600 uppercase">Resume</span>
                    <button type="button" id="openResumeEditorBtn"
                        class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white text-xs font-bold py-2 px-8 rounded shadow-md transition duration-200 uppercase w-44">
                        {{ $user->alumnus->isResumeComplete() ? 'Edit Resume' : 'Create Resume' }}
                    </button>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-12">
                <button class="px-10 py-2 border-2 border-[#0E0F3B] text-[#0E0F3B] font-bold rounded-lg transition-all duration-200 uppercase tracking-widest text-sm hover:bg-[#0E0F3B] hover:text-white active:scale-95">
                    Cancel
                </button>
                <button type="submit" class="px-10 py-2 bg-[#0E0F3B] text-white font-bold rounded-lg hover:bg-[#1D46A4] transition uppercase tracking-widest text-sm shadow-lg">
                    Save
                </button>
            </div>

        </form>
    </main>

    @if($user->alumnus->isResumeComplete())
        @include('alumni.resume-editor-modal', ['user' => $user, 'resumeData' => $resumeData, 'industries' => $industries])
    @else
        @include('alumni.resume-builder-modal', ['user' => $user, 'resumeData' => $resumeData, 'industries' => $industries])
    @endif

    @include('partials.footer-alumni')

</body>

<script>
    // Industry / first-job-date only make sense once the alumnus says
    // they're employed — keep them hidden otherwise, live as the select
    // changes during editing (not just on initial page load).
    function toggleEmploymentFields(status) {
        document.getElementById('employment-fields').classList.toggle('hidden', status !== '1');
    }

    function toggleWorkplaceUndisclosed() {
        const hiddenField = document.getElementById('alumnus_workplace_undisclosed');
        const workplaceInput = document.getElementById('alumnus_workplace');
        const label = document.getElementById('workplaceUndisclosedLabel');
        const undisclosed = hiddenField.value !== '1';

        hiddenField.value = undisclosed ? '1' : '0';
        workplaceInput.disabled = undisclosed;
        if (undisclosed) {
            workplaceInput.value = '';
        }
        label.textContent = undisclosed ? 'Disclose Workplace' : "Don't Disclose";
    }

    //user profile view image/upload image
    function togglePhotoOptions(event) {
        event.stopPropagation();
        const menu = document.getElementById('photoOptions');
        menu.classList.toggle('hidden');

    }

    window.addEventListener('click', function(e) {
        const menu = document.getElementById('photoOptions');
        if (!menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
        }
    });
</script>

</html>