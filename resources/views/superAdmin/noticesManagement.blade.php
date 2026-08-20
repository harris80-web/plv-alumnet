<?php $current_page = 'notices'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices & Events Management | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <!-- @vite('resources/css/app.css')
    @vite('resources/js/app.js') -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        * {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .notices-table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
            font-size: 11px;
        }

        .notices-table thead th {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .notices-table tbody td {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
            word-break: break-word;
        }

        #noticeModal>div,
        #addNoticeModal>div,
        #editNoticeModal>div {
            overflow-y: auto;
            max-height: 90vh;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        #noticeModal>div::-webkit-scrollbar,
        #addNoticeModal>div::-webkit-scrollbar,
        #editNoticeModal>div::-webkit-scrollbar {
            display: none;
        }

        .notice-description-content img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">
        @include('partials.super-admin-side-bar')
        <main class="flex-1 flex flex-col overflow-hidden min-w-0">
            @include('partials.super-admin-header')
            <div class="flex-1 overflow-y-auto p-6">

                @include('partials.success')
                @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-xs">
                    @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <!-- STAT CARDS -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-slate-800">{{ $counts['total'] }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Total Notices</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-blue-600">{{ $counts['event'] }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Events</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-purple-600">{{ $counts['seminar'] }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Seminars</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-amber-600">{{ $counts['announcement'] }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Announcements</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 w-full">

                    <!-- TOOLBAR: search + add -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-slate-100">
                        <div class="relative w-full md:w-72">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                            <input type="text" id="noticeSearchInput" onkeyup="filterNoticeRows()"
                                placeholder="Search by title"
                                class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                        </div>

                        <button type="button" onclick="openAddNoticeModal()"
                            class="flex items-center gap-2 bg-[#1D264F] hover:bg-[#0E0F3B] text-white px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide shadow-sm transition-all uppercase">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Notice
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="notices-table">
                            <thead class="bg-[#0E0F3B] text-white">
                                <tr>
                                    <th class="border-r border-slate-700">ID No.</th>
                                    <th class="border-r border-slate-700">Category</th>
                                    <th class="border-r border-slate-700">Title</th>
                                    <th class="border-r border-slate-700">Date &amp; Time</th>
                                    <th class="border-r border-slate-700">Location</th>
                                    <th class="border-r border-slate-700">Attendance</th>
                                    <th class="border-r border-slate-700">Recipient</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="noticesTbody">
                                @forelse ($notices as $notice)
                                @php
                                    $modalData = [
                                        'id' => $notice->id,
                                        'category' => $notice->category,
                                        'title' => $notice->title,
                                        'thumbnailUrl' => $notice->thumbnailUrl(),
                                        'dateTimeDisplay' => $notice->event_datetime->format('M d, Y - h:i A'),
                                        'location' => $notice->location,
                                        'speakerName' => $notice->speaker_name,
                                        'speakerTopic' => $notice->speaker_topic,
                                        'descriptionHtml' => $notice->description,
                                        'recipient' => $notice->recipient,
                                        'interestedNames' => $notice->interestedAlumniNames(),
                                    ];
                                @endphp
                                <tr class="border-b border-slate-100" data-search="{{ mb_strtolower($notice->title) }}">
                                    <td class="border-r border-slate-100 font-semibold text-[#0E0F3B]">{{ $notice->id }}</td>
                                    <td class="border-r border-slate-100">
                                        <span class="px-2 py-1 rounded-full text-[9px] font-bold uppercase {{ $notice->categoryBadgeClass() }}">
                                            {{ $notice->categoryLabel() }}
                                        </span>
                                    </td>
                                    <td class="border-r border-slate-100 text-left font-medium text-[#0E0F3B]">{{ $notice->title }}</td>
                                    <td class="border-r border-slate-100 whitespace-nowrap">{{ $notice->event_datetime->format('M d, Y - h:i A') }}</td>
                                    <td class="border-r border-slate-100">{{ $notice->location ?? '—' }}</td>
                                    <td class="border-r border-slate-100">
                                        <span class="inline-flex items-center gap-1 text-[#0E0F3B] font-semibold">
                                            <i data-lucide="users" class="w-3 h-3 text-slate-400"></i>
                                            {{ $notice->interested_alumni_count }}
                                        </span>
                                    </td>
                                    <td class="border-r border-slate-100">{{ $notice->recipientLabel() }}</td>
                                    <td>
                                        <div class="relative inline-block">
                                            <button type="button" onclick="toggleNoticeDropdown(this)"
                                                class="p-2 hover:bg-slate-100 rounded-full transition-colors">
                                                <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                            </button>
                                            <div class="action-dropdown absolute right-4 mt-2 w-36 origin-top-right rounded-md bg-white shadow-xl ring-1 ring-black ring-opacity-5 z-50 hidden text-left">
                                                <div class="py-1">
                                                    <button type="button" onclick='openNoticeViewModal(@json($modalData))'
                                                        class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-blue-50 transition-colors">
                                                        <i data-lucide="eye" class="w-4 h-4 mr-3 text-blue-500"></i> View
                                                    </button>
                                                    <button type="button" onclick="openEditNoticeModal({{ $notice->id }})"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-amber-50 transition-colors">
                                                        <i data-lucide="pencil" class="w-4 h-4 mr-3 text-amber-500"></i> Edit
                                                    </button>
                                                    <hr class="my-1 border-slate-100">
                                                    <button type="button" onclick="confirmDeleteNotice('{{ addslashes($notice->title) }}', '{{ route('notices.destroy', $notice->id) }}')"
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
                                    <td colspan="8" class="py-16 text-center text-gray-400">
                                        <i data-lucide="bell-off" class="w-10 h-10 mx-auto mb-3"></i>
                                        No notices, events, or seminars yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <p id="noticeNoSearchResults" class="hidden text-center text-gray-400 py-10 text-xs">No matching notices.</p>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- ══════════════════════ ADD NOTICE MODAL ══════════════════════ -->
    <div id="addNoticeModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all mx-4">
            <div class="relative bg-[#0E0F3B] flex items-center justify-between p-6">
                <h2 class="text-xl font-bold text-white">Add Notice</h2>
                <button type="button" onclick="closeAddNoticeModal()" class="text-white/80 hover:text-white transition-colors">
                    <i data-lucide="x-circle" class="w-6 h-6"></i>
                </button>
            </div>

            <form id="addNoticeForm" action="{{ route('notices.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-6 space-y-4">

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Thumbnail</label>
                        <div class="relative w-full h-36 rounded-lg overflow-hidden border-2 border-[#0E0F3B] bg-slate-100 flex items-center justify-center">
                            <img id="an-thumb-preview" src="" class="hidden w-full h-full object-cover">
                            <span id="an-thumb-placeholder" class="text-slate-400 text-xs flex flex-col items-center gap-1">
                                <i data-lucide="image" class="w-6 h-6"></i> No thumbnail selected
                            </span>
                            <button type="button" onclick="document.getElementById('an-thumb-input').click()"
                                class="absolute bottom-2 right-2 bg-white/90 hover:bg-white text-[#0E0F3B] text-[10px] font-bold px-3 py-1.5 rounded-full shadow uppercase">
                                Upload
                            </button>
                        </div>
                        <input type="file" name="thumbnail" id="an-thumb-input" accept="image/*" class="hidden"
                            onchange="previewImageInto(this, 'an-thumb-preview', 'an-thumb-placeholder')">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Category</label>
                        <select name="category" id="an-category" required onchange="toggleNoticeCategoryFields('an')"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] bg-white">
                            @foreach (\App\Models\Notice::categoryLabels() as $catKey => $catLabel)
                            <option value="{{ $catKey }}" {{ $catKey === 'event' ? 'selected' : '' }}>{{ $catLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Title</label>
                        <input type="text" name="title" required placeholder="e.g. Alumni Homecoming 2026"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Date</label>
                            <input type="date" name="event_date" required
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Time</label>
                            <input type="time" name="event_time" required
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                        </div>
                    </div>

                    <div id="an-speaker-field" class="hidden grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Speaker Name</label>
                            <input type="text" name="speaker_name" placeholder="e.g. Dr. Juan Dela Cruz"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Speaker Topic</label>
                            <input type="text" name="speaker_topic" placeholder="e.g. Career Growth After Graduation"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                        </div>
                    </div>

                    <div id="an-location-field">
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Location</label>
                        <input type="text" name="location" placeholder="e.g. PLV Gymnasium"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Description</label>
                        @include('partials.rich-text-editor', ['uid' => 'addNotice', 'fieldName' => 'description'])
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Recipient</label>
                        <select name="recipient" required
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] bg-white">
                            @foreach (\App\Models\Notice::recipientLabels() as $recKey => $recLabel)
                            <option value="{{ $recKey }}" {{ $recKey === 'everyone' ? 'selected' : '' }}>{{ $recLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeAddNoticeModal()" class="px-6 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-white transition-colors uppercase">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-[#C73D1A] rounded-lg hover:bg-orange-700 transition-colors uppercase">
                        Add
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════ VIEW NOTICE MODAL (read-only) ══════════════════════ -->
    <div id="noticeModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all mx-4">
            <div class="relative bg-[#0E0F3B] flex items-center justify-between p-6">
                <h2 class="text-xl font-bold text-white">Notice Details</h2>
                <button type="button" onclick="closeNoticeModal()" class="text-white/80 hover:text-white transition-colors">
                    <i data-lucide="x-circle" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 text-sm text-[#0E0F3B]">
                <img id="nm-view-thumbnail" src="" class="w-full h-40 object-cover rounded-lg border border-slate-200">

                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Category</p>
                    <span id="nm-view-category" class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase"></span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Title</p>
                    <p id="nm-view-title" class="font-semibold"></p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Date &amp; Time</p>
                    <p id="nm-view-datetime" class="font-semibold"></p>
                </div>
                <div id="nm-view-speaker-row" class="hidden">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Speaker</p>
                    <p id="nm-view-speaker" class="font-semibold"></p>
                </div>
                <div id="nm-view-location-row">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Location</p>
                    <p id="nm-view-location" class="font-semibold"></p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Description</p>
                    <div id="nm-view-description" class="notice-description-content leading-relaxed text-sm"></div>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Recipient</p>
                    <span id="nm-view-recipient" class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase bg-slate-100 text-slate-600"></span>
                </div>

                <div id="nm-view-interested-row" class="hidden pt-2 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Interested Alumni (<span id="nm-view-interested-count">0</span>)</p>
                    <ul id="nm-view-interested-list" class="max-h-32 overflow-y-auto space-y-1 text-xs text-slate-600 list-disc list-inside"></ul>
                </div>
            </div>

            <div class="p-4 bg-slate-50 flex justify-end gap-3 border-t border-slate-100">
                <button type="button" id="nm-view-edit-btn" class="px-6 py-2 text-sm font-medium text-white bg-amber-500 rounded-lg hover:bg-amber-600 transition-colors uppercase">
                    Edit
                </button>
                <button type="button" onclick="closeNoticeModal()" class="px-6 py-2 text-sm font-medium text-white bg-[#0E0F3B] rounded-lg hover:bg-[#1a1c5a] transition-colors uppercase">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════ EDIT NOTICE MODAL — one pre-rendered form per notice ══════════════════════ -->
    <div id="editNoticeModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden mx-4">
            @foreach ($notices as $notice)
            <form id="editNoticeForm-{{ $notice->id }}" class="hidden" action="{{ route('notices.update', $notice->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="relative bg-[#0E0F3B] flex items-center justify-between p-6">
                    <h2 class="text-xl font-bold text-white">Edit Notice</h2>
                    <button type="button" onclick="closeEditNoticeModal()" class="text-white/80 hover:text-white transition-colors">
                        <i data-lucide="x-circle" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4">

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Thumbnail</label>
                        <div class="relative w-full h-36 rounded-lg overflow-hidden border-2 border-[#0E0F3B] bg-slate-100 flex items-center justify-center">
                            <img id="edit-{{ $notice->id }}-thumb-preview" src="{{ $notice->thumbnail ? asset('storage/' . $notice->thumbnail) : '' }}"
                                class="w-full h-full object-cover {{ $notice->thumbnail ? '' : 'hidden' }}">
                            <span id="edit-{{ $notice->id }}-thumb-placeholder" class="text-slate-400 text-xs flex flex-col items-center gap-1 {{ $notice->thumbnail ? 'hidden' : '' }}">
                                <i data-lucide="image" class="w-6 h-6"></i> No thumbnail selected
                            </span>
                            <button type="button" onclick="document.getElementById('edit-{{ $notice->id }}-thumb-input').click()"
                                class="absolute bottom-2 right-2 bg-white/90 hover:bg-white text-[#0E0F3B] text-[10px] font-bold px-3 py-1.5 rounded-full shadow uppercase">
                                Upload
                            </button>
                        </div>
                        <input type="file" name="thumbnail" id="edit-{{ $notice->id }}-thumb-input" accept="image/*" class="hidden"
                            onchange="previewImageInto(this, 'edit-{{ $notice->id }}-thumb-preview', 'edit-{{ $notice->id }}-thumb-placeholder')">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Category</label>
                        <select name="category" id="edit-{{ $notice->id }}-category" onchange="toggleNoticeCategoryFields('edit-{{ $notice->id }}')"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] bg-white">
                            @foreach (\App\Models\Notice::categoryLabels() as $catKey => $catLabel)
                            <option value="{{ $catKey }}" {{ $notice->category === $catKey ? 'selected' : '' }}>{{ $catLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Title</label>
                        <input type="text" name="title" value="{{ $notice->title }}"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Date</label>
                            <input type="date" name="event_date" value="{{ $notice->event_datetime->format('Y-m-d') }}"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Time</label>
                            <input type="time" name="event_time" value="{{ $notice->event_datetime->format('H:i') }}"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                        </div>
                    </div>

                    <div id="edit-{{ $notice->id }}-speaker-field" class="{{ $notice->category === 'seminar' ? '' : 'hidden' }} grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Speaker Name</label>
                            <input type="text" name="speaker_name" value="{{ $notice->speaker_name }}"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Speaker Topic</label>
                            <input type="text" name="speaker_topic" value="{{ $notice->speaker_topic }}"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                        </div>
                    </div>

                    <div id="edit-{{ $notice->id }}-location-field" class="{{ $notice->category === 'announcement' ? 'hidden' : '' }}">
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Location</label>
                        <input type="text" name="location" value="{{ $notice->location }}"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Description</label>
                        @include('partials.rich-text-editor', ['uid' => 'edit-' . $notice->id, 'fieldName' => 'description', 'initialValue' => $notice->description])
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Recipient</label>
                        <select name="recipient"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] bg-white">
                            @foreach (\App\Models\Notice::recipientLabels() as $recKey => $recLabel)
                            <option value="{{ $recKey }}" {{ $notice->recipient === $recKey ? 'selected' : '' }}>{{ $recLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditNoticeModal()" class="px-6 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-white transition-colors uppercase">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-[#C73D1A] rounded-lg hover:bg-orange-700 transition-colors uppercase">
                        Save
                    </button>
                </div>
            </form>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });

        const categoryLabels = <?= json_encode(\App\Models\Notice::categoryLabels()) ?>;
        const categoryBadgeClasses = <?= json_encode(\App\Models\Notice::categoryBadgeClasses()) ?>;
        const recipientLabels = <?= json_encode(\App\Models\Notice::recipientLabels()) ?>;

        // ── SEARCH ──
        function filterNoticeRows() {
            const query = document.getElementById('noticeSearchInput').value.trim().toLowerCase();
            const rows = document.querySelectorAll('#noticesTbody tr[data-search]');
            let visibleCount = 0;
            rows.forEach(row => {
                const match = row.dataset.search.includes(query);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            document.getElementById('noticeNoSearchResults').classList.toggle('hidden', visibleCount !== 0 || rows.length === 0);
        }

        // ── 3-DOT ACTION DROPDOWN ──
        function toggleNoticeDropdown(btn) {
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

        // ── CATEGORY-CONDITIONAL FIELDS (shared by Add form + every per-row Edit form) ──
        // Event: location shown, speaker hidden. Seminar: both shown.
        // Announcement: location hidden, speaker hidden. Hidden fields are
        // display:none, which the browser already excludes from required-field
        // validation on its own — the server (NoticeController::validationRules())
        // is the real source of truth either way.
        function toggleNoticeCategoryFields(prefix) {
            const category = document.getElementById(prefix + '-category').value;
            document.getElementById(prefix + '-location-field').classList.toggle('hidden', category === 'announcement');
            document.getElementById(prefix + '-speaker-field').classList.toggle('hidden', category !== 'seminar');
        }

        // ── IMAGE PREVIEW (shared by Add thumbnail + every per-row Edit thumbnail) ──
        function previewImageInto(input, imgElId, placeholderElId) {
            const img = document.getElementById(imgElId);
            const placeholder = placeholderElId ? document.getElementById(placeholderElId) : null;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // ── ADD MODAL ──
        function openAddNoticeModal() {
            document.getElementById('addNoticeForm').reset();
            document.getElementById('an-category').value = 'event';
            document.getElementById('an-thumb-preview').classList.add('hidden');
            document.getElementById('an-thumb-placeholder').classList.remove('hidden');
            toggleNoticeCategoryFields('an');
            document.getElementById('addNoticeModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }

        function closeAddNoticeModal() {
            document.getElementById('addNoticeModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // ── VIEW MODAL (read-only) ──
        let currentViewNoticeId = null;

        function openNoticeViewModal(data) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));
            currentViewNoticeId = data.id;

            document.getElementById('nm-view-thumbnail').src = data.thumbnailUrl;

            const catBadge = document.getElementById('nm-view-category');
            catBadge.textContent = categoryLabels[data.category] ?? data.category;
            catBadge.className = 'inline-block px-3 py-1 rounded-full text-xs font-bold uppercase ' + (categoryBadgeClasses[data.category] ?? 'bg-slate-100 text-slate-500');

            document.getElementById('nm-view-title').textContent = data.title;
            document.getElementById('nm-view-datetime').textContent = data.dateTimeDisplay;
            document.getElementById('nm-view-description').innerHTML = data.descriptionHtml || '<p class="text-slate-400">No description provided.</p>';

            const recipientBadge = document.getElementById('nm-view-recipient');
            recipientBadge.textContent = recipientLabels[data.recipient] ?? data.recipient;

            const locationRow = document.getElementById('nm-view-location-row');
            if (data.category === 'announcement') {
                locationRow.classList.add('hidden');
            } else {
                locationRow.classList.remove('hidden');
                document.getElementById('nm-view-location').textContent = data.location || '—';
            }

            const speakerRow = document.getElementById('nm-view-speaker-row');
            if (data.category === 'seminar') {
                speakerRow.classList.remove('hidden');
                document.getElementById('nm-view-speaker').textContent = (data.speakerName || '—') + ' — ' + (data.speakerTopic || '—');
            } else {
                speakerRow.classList.add('hidden');
            }

            const interestedRow = document.getElementById('nm-view-interested-row');
            const interestedList = document.getElementById('nm-view-interested-list');
            if (data.category === 'announcement') {
                interestedRow.classList.add('hidden');
            } else {
                interestedRow.classList.remove('hidden');
                document.getElementById('nm-view-interested-count').textContent = data.interestedNames.length;
                interestedList.innerHTML = data.interestedNames.length
                    ? data.interestedNames.map(name => '<li>' + name + '</li>').join('')
                    : '<li class="list-none text-slate-400">No one has marked interest yet.</li>';
            }

            document.getElementById('nm-view-edit-btn').onclick = function () {
                openEditNoticeModal(data.id);
            };

            document.getElementById('noticeModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }

        function closeNoticeModal() {
            document.getElementById('noticeModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // ── EDIT MODAL — shows the one pre-rendered form matching this notice ──
        function openEditNoticeModal(id) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));
            closeNoticeModal();

            document.querySelectorAll('[id^="editNoticeForm-"]').forEach(f => f.classList.add('hidden'));
            const form = document.getElementById('editNoticeForm-' + id);
            if (form) form.classList.remove('hidden');

            document.getElementById('editNoticeModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }

        function closeEditNoticeModal() {
            document.getElementById('editNoticeModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // ── DELETE ──
        function confirmDeleteNotice(title, url) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));
            if (!confirm('Delete "' + title + '"? This cannot be undone.')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                '<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        }

        window.addEventListener('click', function (event) {
            if (event.target === document.getElementById('noticeModal')) closeNoticeModal();
            if (event.target === document.getElementById('addNoticeModal')) closeAddNoticeModal();
            if (event.target === document.getElementById('editNoticeModal')) closeEditNoticeModal();
        });
    </script>
</body>

</html>
