{{--
    Alumni Directory's results table + pagination bar — shared by the full
    page (alumni/directory.blade.php) and AlumnusController::directoryFragment()
    (the live-search AJAX response), so the two can never render differently.
    Needs just $alumni (paginated) in scope.
--}}
<!-- TABLE -->
<div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-[#0E0F3B] text-white uppercase tracking-wider text-center text-xs">
                <tr>
                    <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Full Name <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                    <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Program <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                    <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Batch <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                    <th class="px-4 py-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($alumni as $alumnus)
                @php
                    $skillNames = $alumnus->skills->pluck('skill_name')->implode(', ');
                    $modalData = [
                        'id' => $alumnus->user_id,
                        'name' => trim($alumnus->user->user_first_name . ' ' . $alumnus->user->user_middle_name . ' ' . $alumnus->user->user_last_name . ' ' . $alumnus->user->user_suffix),
                        'photo' => $alumnus->user->user_profile_picture ? asset('storage/' . $alumnus->user->user_profile_picture) : null,
                        'program' => $alumnus->program->program_name ?? 'Not specified',
                        'batch' => optional($alumnus->alumnus_batch)->format('Y'),
                        'section' => $alumnus->section->section_name ?? 'N/A',
                        'employment' => $alumnus->alumnus_employment_status ? 'Employed' : 'Unemployed',
                        'industry' => $alumnus->industry->industry_name ?? null,
                        'aligned' => $alumnus->hasCourseAlignedJob(),
                        // Each respects that alumnus's own Profile Settings toggle
                        // (resources/views/alumni/edit-profile.blade.php) — null/omitted
                        // here reads the same as "not provided" to the modal's JS.
                        'skills' => $alumnus->alumnus_show_skills ? ($skillNames ?: 'None listed') : null,
                        'linkedin' => $alumnus->alumnus_show_linkedin ? $alumnus->linkedin_url : null,
                        'email' => $alumnus->alumnus_show_email ? $alumnus->user->user_email : null,
                        'contact' => $alumnus->user->user_number,
                    ];
                @endphp
                <tr class="hover:bg-slate-50/80 transition-colors text-center">
                    <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $alumnus->formalName() }}</td>
                    <td class="px-4 py-3 font-medium text-black border-r border-slate-100 leading-tight">{{ $alumnus->program->program_name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ optional($alumnus->alumnus_batch)->format('Y') }}</td>
                    <td class="px-4 py-3 text-center relative">
                        @if ($alumnus->user_id !== auth()->id())
                        <div class="relative inline-block text-left">
                            <button type="button" onclick="toggleDirectoryDropdown(this)"
                                class="p-2 hover:bg-slate-100 rounded-full transition-colors">
                                <i class="fas fa-ellipsis-vertical text-slate-500"></i>
                            </button>
                            <div class="action-dropdown absolute right-4 mt-2 w-44 origin-top-right rounded-md bg-white shadow-xl ring-1 ring-black ring-opacity-5 z-50 hidden">
                                <div class="py-1">
                                    <button type="button" onclick='openProfileModal(@json($modalData))'
                                        class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-blue-50 transition-colors">
                                        <i class="fas fa-user w-4 mr-3 text-blue-500"></i> View Profile
                                    </button>
                                    <button type="button" onclick="messageAlumnus({{ $alumnus->user_id }})"
                                        class="flex items-center w-full px-4 py-2.5 text-sm text-[#0E0F3B] hover:bg-green-50 transition-colors">
                                        <i class="fas fa-comment w-4 mr-3 text-green-500"></i> Message
                                    </button>
                                </div>
                            </div>
                        </div>
                        @else
                        <span class="text-xs text-gray-400 italic">You</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-16 text-center text-gray-400">
                        <i class="fas fa-users-slash text-4xl mb-3 block"></i>
                        No alumni found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('partials.table-pagination-bar', [
    'id' => 'alumniDirectory',
    'mode' => 'ajax',
    'paginator' => $alumni,
    'fetchUrl' => route('alumni.directoryFragment'),
    'wrapId' => 'alumniDirectoryResults',
])
