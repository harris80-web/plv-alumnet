<?php $current_page = 'faqs'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage FAQs | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
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

        .faqs-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            font-size: 11px;
        }

        .faqs-table thead th {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .faqs-table tbody td {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
            word-break: break-word;
        }

        .clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        #faqModal>div,
        #addFaqModal>div,
        #editFaqModal>div {
            overflow-y: auto;
            max-height: 90vh;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        #faqModal>div::-webkit-scrollbar,
        #addFaqModal>div::-webkit-scrollbar,
        #editFaqModal>div::-webkit-scrollbar {
            display: none;
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
                        <p class="text-xs font-medium text-slate-500 mt-1">Total FAQs</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-green-600">{{ $counts['everyone'] }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Everyone</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-blue-600">{{ $counts['alumni'] }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Alumni Only</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-purple-600">{{ $counts['employer'] }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Employer Only</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 w-full">

                    <!-- TOOLBAR: search + bulk actions + add -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-slate-100">
                        <div class="relative w-full md:w-72">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                            <input type="text" id="faqSearchInput" onkeyup="filterFaqRows()"
                                placeholder="Search by question"
                                class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                        </div>

                        <div class="flex items-center gap-2 flex-wrap justify-end">
                            <span class="text-xs font-semibold text-gray-500"><span id="faqSelectedCount">0</span> selected</span>

                            <form id="bulkFaqRecipientForm" action="{{ route('faqs.bulkUpdateRecipient') }}" method="POST" class="hidden">
                                @csrf
                                <input type="hidden" name="faq_recipient" id="bulkFaqRecipientInput">
                            </form>
                            <form id="bulkFaqDeleteForm" action="{{ route('faqs.bulkDestroy') }}" method="POST" class="hidden">
                                @csrf
                            </form>

                            @foreach (\App\Models\Faq::recipientLabels() as $recKey => $recLabel)
                            <button type="button" disabled data-bulk-btn data-recipient="{{ $recKey }}"
                                onclick="submitBulkFaqRecipient('{{ $recKey }}', '{{ $recLabel }}')"
                                class="{{ \App\Models\Faq::recipientButtonClasses()[$recKey] }} disabled:!bg-gray-300 disabled:cursor-not-allowed text-white text-[10px] font-bold px-3 py-2 rounded-md transition-colors uppercase whitespace-nowrap">
                                Set {{ $recLabel }}
                            </button>
                            @endforeach
                            <button type="button" disabled data-bulk-btn onclick="submitBulkFaqDelete()"
                                class="bg-red-600 hover:bg-red-700 disabled:!bg-gray-300 disabled:cursor-not-allowed text-white text-[10px] font-bold px-3 py-2 rounded-md transition-colors uppercase whitespace-nowrap">
                                Delete Selected
                            </button>

                            <button type="button" onclick="openAddFaqModal()"
                                class="flex items-center gap-2 bg-[#1D264F] hover:bg-[#0E0F3B] text-white px-5 py-2.5 rounded-lg text-xs font-bold tracking-wide shadow-sm transition-all uppercase">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Question
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="faqs-table">
                            <thead class="bg-[#0E0F3B] text-white">
                                <tr>
                                    <th class="border-r border-slate-700 w-10">
                                        <input type="checkbox" id="faqSelectAllCheckbox" onchange="toggleSelectAllFaqs(this)" class="w-4 h-4 cursor-pointer" title="Select all">
                                    </th>
                                    <th class="border-r border-slate-700">ID No.</th>
                                    <th class="border-r border-slate-700" style="width: 26%;">Question</th>
                                    <th class="border-r border-slate-700" style="width: 34%;">Answer</th>
                                    <th class="border-r border-slate-700">Recipient</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="faqsTbody">
                                @forelse ($faqs as $faq)
                                @php
                                    $modalData = [
                                        'id' => $faq->faq_id,
                                        'question' => $faq->faq_question,
                                        'answer' => $faq->faq_answer,
                                        'recipient' => $faq->faq_recipient,
                                    ];
                                @endphp
                                <tr class="border-b border-slate-100" data-search="{{ mb_strtolower($faq->faq_question) }}">
                                    <td class="border-r border-slate-100">
                                        <input type="checkbox" class="faq-checkbox w-4 h-4 accent-[#1D264F] cursor-pointer" value="{{ $faq->faq_id }}" onchange="updateFaqBulkUI()">
                                    </td>
                                    <td class="border-r border-slate-100 font-semibold text-[#0E0F3B]">{{ $faq->faq_id }}</td>
                                    <td class="border-r border-slate-100 text-left font-medium text-[#0E0F3B]">
                                        <p class="clamp-2">{{ $faq->faq_question }}</p>
                                    </td>
                                    <td class="border-r border-slate-100 text-left text-slate-600">
                                        <p class="clamp-2">{{ $faq->faq_answer }}</p>
                                    </td>
                                    <td class="border-r border-slate-100">
                                        <span class="px-2 py-1 rounded-full text-[9px] font-bold uppercase {{ $faq->badgeClass() }}">
                                            {{ $faq->recipientLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="relative inline-block">
                                            <button type="button" onclick="toggleFaqDropdown(this)"
                                                class="p-2 hover:bg-slate-100 rounded-full transition-colors">
                                                <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                            </button>
                                            <div class="action-dropdown absolute right-4 mt-2 w-36 origin-top-right rounded-md bg-white shadow-xl ring-1 ring-black ring-opacity-5 z-50 hidden text-left">
                                                <div class="py-1">
                                                    <button type="button" onclick='openFaqViewModal(@json($modalData))'
                                                        class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-blue-50 transition-colors">
                                                        <i data-lucide="eye" class="w-4 h-4 mr-3 text-blue-500"></i> View
                                                    </button>
                                                    <button type="button" onclick="openEditFaqModal({{ $faq->faq_id }})"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-amber-50 transition-colors">
                                                        <i data-lucide="pencil" class="w-4 h-4 mr-3 text-amber-500"></i> Edit
                                                    </button>
                                                    <hr class="my-1 border-slate-100">
                                                    <button type="button" onclick="confirmDeleteFaq('{{ addslashes(\Illuminate\Support\Str::limit($faq->faq_question, 60)) }}', '{{ route('faqs.destroy', $faq->faq_id) }}')"
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
                                    <td colspan="6" class="py-16 text-center text-gray-400">
                                        <i data-lucide="help-circle" class="w-10 h-10 mx-auto mb-3"></i>
                                        No FAQs yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <p id="faqNoSearchResults" class="hidden text-center text-gray-400 py-10 text-xs">No matching FAQs.</p>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- ══════════════════════ ADD FAQ MODAL ══════════════════════ -->
    <div id="addFaqModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all mx-4">
            <div class="relative bg-[#0E0F3B] flex items-center justify-between p-6">
                <h2 class="text-xl font-bold text-white">Add Question</h2>
                <button type="button" onclick="closeAddFaqModal()" class="text-white/80 hover:text-white transition-colors">
                    <i data-lucide="x-circle" class="w-6 h-6"></i>
                </button>
            </div>

            <form id="addFaqForm" action="{{ route('faqs.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Recipient</label>
                        <select name="faq_recipient" id="af-recipient" required
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] bg-white">
                            @foreach (\App\Models\Faq::recipientLabels() as $recKey => $recLabel)
                            <option value="{{ $recKey }}" {{ $recKey === 'everyone' ? 'selected' : '' }}>{{ $recLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Question</label>
                        <textarea name="faq_question" required rows="2" placeholder="e.g. How do I sign up as an alumni?"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] resize-none"></textarea>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Answer</label>
                        <textarea name="faq_answer" required rows="5" placeholder="Write the answer shown to users..."
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] resize-none"></textarea>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeAddFaqModal()" class="px-6 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-white transition-colors uppercase">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-[#C73D1A] rounded-lg hover:bg-orange-700 transition-colors uppercase">
                        Add
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════ VIEW FAQ MODAL (read-only) ══════════════════════ -->
    <div id="faqModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all mx-4">
            <div class="relative bg-[#0E0F3B] flex items-center justify-between p-6">
                <h2 class="text-xl font-bold text-white">FAQ Details</h2>
                <button type="button" onclick="closeFaqModal()" class="text-white/80 hover:text-white transition-colors">
                    <i data-lucide="x-circle" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 text-sm text-[#0E0F3B]">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Question</p>
                    <p id="fm-view-question" class="font-semibold"></p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Answer</p>
                    <p id="fm-view-answer" class="leading-relaxed whitespace-pre-line"></p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Recipient</p>
                    <span id="fm-view-recipient" class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase"></span>
                </div>
            </div>

            <div class="p-4 bg-slate-50 flex justify-end gap-3 border-t border-slate-100">
                <button type="button" id="fm-view-edit-btn" class="px-6 py-2 text-sm font-medium text-white bg-amber-500 rounded-lg hover:bg-amber-600 transition-colors uppercase">
                    Edit
                </button>
                <button type="button" onclick="closeFaqModal()" class="px-6 py-2 text-sm font-medium text-white bg-[#0E0F3B] rounded-lg hover:bg-[#1a1c5a] transition-colors uppercase">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════ EDIT FAQ MODAL — one pre-rendered form per FAQ ══════════════════════ -->
    <div id="editFaqModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden mx-4">
            @foreach ($faqs as $faq)
            <form id="editFaqForm-{{ $faq->faq_id }}" class="hidden" action="{{ route('faqs.update', $faq->faq_id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="relative bg-[#0E0F3B] flex items-center justify-between p-6">
                    <h2 class="text-xl font-bold text-white">Edit Question</h2>
                    <button type="button" onclick="closeEditFaqModal()" class="text-white/80 hover:text-white transition-colors">
                        <i data-lucide="x-circle" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4">

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Recipient</label>
                        <select name="faq_recipient"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] bg-white">
                            @foreach (\App\Models\Faq::recipientLabels() as $recKey => $recLabel)
                            <option value="{{ $recKey }}" {{ $faq->faq_recipient === $recKey ? 'selected' : '' }}>{{ $recLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Question</label>
                        <textarea name="faq_question" rows="2"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] resize-none">{{ $faq->faq_question }}</textarea>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Answer</label>
                        <textarea name="faq_answer" rows="5"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] resize-none">{{ $faq->faq_answer }}</textarea>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditFaqModal()" class="px-6 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-white transition-colors uppercase">
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

        const faqRecipientLabels = <?= json_encode(\App\Models\Faq::recipientLabels()) ?>;
        const faqRecipientBadgeClasses = <?= json_encode(\App\Models\Faq::recipientBadgeClasses()) ?>;

        // ── SEARCH ──
        function filterFaqRows() {
            const query = document.getElementById('faqSearchInput').value.trim().toLowerCase();
            const rows = document.querySelectorAll('#faqsTbody tr[data-search]');
            let visibleCount = 0;
            rows.forEach(row => {
                const match = row.dataset.search.includes(query);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            document.getElementById('faqNoSearchResults').classList.toggle('hidden', visibleCount !== 0 || rows.length === 0);
        }

        // ── 3-DOT ACTION DROPDOWN ──
        function toggleFaqDropdown(btn) {
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

        // ── BULK SELECT ──
        function toggleSelectAllFaqs(checkbox) {
            document.querySelectorAll('.faq-checkbox').forEach(cb => {
                if (cb.closest('tr').style.display !== 'none') cb.checked = checkbox.checked;
            });
            updateFaqBulkUI();
        }

        function updateFaqBulkUI() {
            const checked = document.querySelectorAll('.faq-checkbox:checked');
            document.getElementById('faqSelectedCount').textContent = checked.length;
            document.querySelectorAll('[data-bulk-btn]').forEach(btn => btn.disabled = checked.length === 0);

            const allBoxes = document.querySelectorAll('.faq-checkbox');
            document.getElementById('faqSelectAllCheckbox').checked = allBoxes.length > 0 && checked.length === allBoxes.length;
        }

        function selectedFaqIds() {
            return [...document.querySelectorAll('.faq-checkbox:checked')].map(cb => cb.value);
        }

        function submitBulkFaqRecipient(recipientKey, recipientLabel) {
            const ids = selectedFaqIds();
            if (ids.length === 0) return;
            if (!confirm(`Set recipient to "${recipientLabel}" for ${ids.length} selected FAQ(s)?`)) return;

            const form = document.getElementById('bulkFaqRecipientForm');
            document.getElementById('bulkFaqRecipientInput').value = recipientKey;
            form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            form.submit();
        }

        function submitBulkFaqDelete() {
            const ids = selectedFaqIds();
            if (ids.length === 0) return;
            if (!confirm(`Delete ${ids.length} selected FAQ(s)? This cannot be undone.`)) return;

            const form = document.getElementById('bulkFaqDeleteForm');
            form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            form.submit();
        }

        // ── ADD MODAL ──
        function openAddFaqModal() {
            document.getElementById('addFaqForm').reset();
            document.getElementById('addFaqModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }

        function closeAddFaqModal() {
            document.getElementById('addFaqModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // ── VIEW MODAL (read-only) ──
        function openFaqViewModal(data) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));

            document.getElementById('fm-view-question').textContent = data.question;
            document.getElementById('fm-view-answer').textContent = data.answer;

            const recipientBadge = document.getElementById('fm-view-recipient');
            recipientBadge.textContent = faqRecipientLabels[data.recipient] ?? data.recipient;
            recipientBadge.className = 'inline-block px-3 py-1 rounded-full text-xs font-bold uppercase ' + (faqRecipientBadgeClasses[data.recipient] ?? 'bg-slate-100 text-slate-500');

            document.getElementById('fm-view-edit-btn').onclick = function () {
                openEditFaqModal(data.id);
            };

            document.getElementById('faqModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }

        function closeFaqModal() {
            document.getElementById('faqModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // ── EDIT MODAL — shows the one pre-rendered form matching this FAQ ──
        function openEditFaqModal(id) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));
            closeFaqModal();

            document.querySelectorAll('[id^="editFaqForm-"]').forEach(f => f.classList.add('hidden'));
            const form = document.getElementById('editFaqForm-' + id);
            if (form) form.classList.remove('hidden');

            document.getElementById('editFaqModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }

        function closeEditFaqModal() {
            document.getElementById('editFaqModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // ── DELETE (single) ──
        function confirmDeleteFaq(question, url) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));
            if (!confirm('Delete "' + question + '"? This cannot be undone.')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                '<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        }

        window.addEventListener('click', function (event) {
            if (event.target === document.getElementById('faqModal')) closeFaqModal();
            if (event.target === document.getElementById('addFaqModal')) closeAddFaqModal();
            if (event.target === document.getElementById('editFaqModal')) closeEditFaqModal();
        });
    </script>
</body>

</html>
