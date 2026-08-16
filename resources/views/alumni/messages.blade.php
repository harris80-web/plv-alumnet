<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Messages</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
    }

    html,
    body {
        -ms-overflow-style: none;
        scrollbar-width: none;
        overflow: hidden;
    }

    .chat-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .chat-scroll::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 3px;
    }
</style>

<body class="bg-slate-50">
    @php $current_page = Route::currentRouteName(); @endphp
    @include('partials.header-alumni')

    @include('partials.success')

    <main class="max-w-7xl mx-auto p-4">
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden flex" style="height: calc(100vh - 130px);">

            <!-- ══════════════════════ LEFT: CHAT LIST ══════════════════════ -->
            <div class="w-80 shrink-0 border-r border-gray-100 flex flex-col">
                <div class="p-5 border-b border-gray-100 shrink-0">
                    <h2 class="text-2xl font-bold text-[#C73D1A] mb-3">CHATS</h2>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" id="chatSearchInput" placeholder="Search" autocomplete="off" oninput="filterChatList()"
                                class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                        </div>
                        <a href="{{ route('messages.index') }}" title="New message"
                            class="w-10 h-10 shrink-0 flex items-center justify-center bg-[#1D264F] hover:bg-[#0E0F3B] text-white rounded-lg transition-colors">
                            <i class="fas fa-pen-to-square text-sm"></i>
                        </a>
                    </div>
                </div>
                <div id="chatListContainer" class="flex-1 overflow-y-auto chat-scroll">
                    @include('partials.chat-list-items', ['conversations' => $conversations, 'activeConversationId' => $activeConversation?->conversation_id])
                </div>
            </div>

            <!-- ══════════════════════ MIDDLE: THREAD ══════════════════════ -->
            <div class="flex-1 flex flex-col min-w-0">
                @if (!$activeConversation)
                <div class="p-5 border-b border-gray-100 relative shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-[#0E0F3B]">To:</span>
                        <input type="text" id="toSearchInput" placeholder="Search alumni by name..." autocomplete="off"
                            class="flex-1 border-b border-gray-200 focus:outline-none focus:border-[#C73D1A] py-1 text-sm">
                    </div>
                    <div id="toSearchResults" class="hidden absolute left-5 right-5 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl z-20 max-h-72 overflow-y-auto"></div>
                </div>
                <div class="flex-1 flex items-center justify-center text-gray-300">
                    <div class="text-center px-6">
                        <i class="fas fa-comments text-6xl mb-3"></i>
                        <p class="text-sm">Search for an alumnus above to start a conversation.</p>
                    </div>
                </div>
                @else
                <div class="p-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#0E0F3B] flex items-center justify-center overflow-hidden shrink-0">
                            @if($activeContact->user_profile_picture)
                            <img src="{{ asset('storage/' . $activeContact->user_profile_picture) }}" class="w-full h-full object-cover">
                            @else
                            <i class="fas fa-user text-white text-sm"></i>
                            @endif
                        </div>
                        <h3 class="font-bold text-[#0E0F3B]">{{ $activeContact->user_first_name }} {{ $activeContact->user_last_name }}</h3>
                    </div>
                    <button type="button" onclick="toggleInfoPanel()" title="Conversation info" class="text-gray-400 hover:text-[#C73D1A] transition-colors">
                        <i class="fas fa-circle-info text-lg"></i>
                    </button>
                </div>

                <div id="messageThread" class="flex-1 overflow-y-auto chat-scroll p-5 space-y-4">
                    @foreach ($messages as $message)
                        @include('partials.chat-message-bubble', ['message' => $message, 'contact' => $activeContact, 'currentUserId' => auth()->id()])
                    @endforeach
                </div>

                <form id="composerForm" class="p-4 border-t border-gray-100 shrink-0">
                    <div id="attachmentPreview" class="hidden mb-2 flex items-center gap-2 bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 text-xs">
                        <i class="fas fa-paperclip text-gray-400"></i>
                        <span id="attachmentPreviewName" class="flex-1 truncate"></span>
                        <button type="button" onclick="clearAttachment()" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="document.getElementById('attachmentInput').click()" title="Attach a file or image"
                            class="w-10 h-10 shrink-0 rounded-full flex items-center justify-center text-gray-400 hover:text-[#C73D1A] hover:bg-gray-50 transition-colors">
                            <i class="fas fa-plus"></i>
                        </button>
                        <input type="file" id="attachmentInput" class="hidden" onchange="handleAttachmentPreview(this)"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                        <input type="text" id="messageInput" placeholder="Type Message..." autocomplete="off"
                            class="flex-1 border border-gray-200 rounded-full px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                        <button type="submit" title="Send"
                            class="w-10 h-10 shrink-0 rounded-full bg-[#1D264F] hover:bg-[#0E0F3B] text-white flex items-center justify-center transition-colors disabled:opacity-50">
                            <i class="fas fa-paper-plane text-sm"></i>
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </main>

    <!-- ══════════════════════ RIGHT: INFO PANEL (slide-in) ══════════════════════ -->
    @if ($activeConversation)
    <div id="infoPanelOverlay" onclick="toggleInfoPanel()" class="fixed inset-0 bg-black/30 z-[75] hidden"></div>
    <div id="infoPanel" class="fixed top-0 right-0 h-full w-80 bg-white z-[80] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-[#0E0F3B]">Contact Info</h3>
            <button type="button" onclick="toggleInfoPanel()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6 text-center border-b border-gray-100">
            <div class="w-24 h-24 rounded-full bg-[#0E0F3B] flex items-center justify-center overflow-hidden mx-auto mb-3">
                @if($activeContact->user_profile_picture)
                <img src="{{ asset('storage/' . $activeContact->user_profile_picture) }}" class="w-full h-full object-cover">
                @else
                <i class="fas fa-user text-white text-3xl"></i>
                @endif
            </div>
            <h4 class="font-bold text-lg text-[#0E0F3B]">{{ $activeContact->user_first_name }} {{ $activeContact->user_last_name }}</h4>
            <p class="text-xs text-gray-500">{{ $activeContact->alumnus->program->program_name ?? 'Program not specified' }}</p>
        </div>

        <div class="p-6 space-y-4 text-sm border-b border-gray-100">
            <div class="flex justify-between gap-3">
                <span class="font-bold text-[#C73D1A] shrink-0">Batch</span>
                <span class="text-gray-700 text-right">{{ $activeContact->alumnus->alumnus_batch ?? '—' }}</span>
            </div>
            <div class="flex justify-between gap-3">
                <span class="font-bold text-[#C73D1A] shrink-0">Employment</span>
                <span class="text-gray-700 text-right">
                    {{ ($activeContact->alumnus->alumnus_employment_status ?? false) ? 'Employed' : 'Unemployed' }}
                    @if(($activeContact->alumnus->alumnus_employment_status ?? false) && ($activeContact->alumnus->industry->industry_name ?? null))
                        — {{ $activeContact->alumnus->industry->industry_name }}
                    @endif
                </span>
            </div>
            <div class="flex justify-between gap-3">
                <span class="font-bold text-[#C73D1A] shrink-0">Email</span>
                <a href="mailto:{{ $activeContact->user_email }}" class="text-blue-600 hover:underline text-right truncate max-w-[170px]">{{ $activeContact->user_email }}</a>
            </div>
        </div>

        <div class="p-6">
            <h4 class="text-xs font-bold text-gray-400 uppercase mb-3">Shared Files ({{ $sharedAttachments->count() }})</h4>
            <div class="space-y-2">
                @forelse ($sharedAttachments as $att)
                <a href="{{ $att->attachmentUrl() }}" target="_blank" class="flex items-center gap-3 bg-slate-50 hover:bg-slate-100 rounded-lg px-3 py-2 transition-colors">
                    @if($att->isImageAttachment())
                    <img src="{{ $att->attachmentUrl() }}" class="w-9 h-9 rounded object-cover shrink-0">
                    @else
                    <div class="w-9 h-9 rounded bg-white border border-gray-200 flex items-center justify-center shrink-0">
                        <i class="fas fa-file text-gray-400 text-xs"></i>
                    </div>
                    @endif
                    <span class="text-xs text-gray-700 truncate">{{ $att->attachment_original_name }}</span>
                </a>
                @empty
                <p class="text-xs text-gray-400">No files shared yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const CONVERSATION_ID = {{ $activeConversation ? $activeConversation->conversation_id : 'null' }};
        const CURRENT_USER_ID = {{ auth()->id() }};
        const CONTACT_NAME = {!! $activeContact ? json_encode($activeContact->user_first_name) : 'null' !!};
        const CONTACT_PHOTO = {!! $activeContact && $activeContact->user_profile_picture ? json_encode(asset('storage/' . $activeContact->user_profile_picture)) : 'null' !!};
        // Full, app-base-aware URLs (this app can be served from a subpath, e.g. /plv-alumnet/public,
        // so hardcoded '/messages/...' strings would resolve against the domain root and 404).
        const MESSAGES_BASE_URL = {!! json_encode(url('/messages')) !!};
        const SEND_URL = {!! $activeConversation ? json_encode(route('messages.send', $activeConversation)) : 'null' !!};
        const POLL_URL = {!! $activeConversation ? json_encode(route('messages.poll', $activeConversation)) : 'null' !!};
        const SIDEBAR_POLL_URL = {!! json_encode(route('messages.sidebarPoll')) !!};
        const SEARCH_CONTACTS_URL = {!! json_encode(route('messages.searchContacts')) !!};
        const START_CONVERSATION_URL = {!! json_encode(route('messages.start')) !!};
        let lastMessageId = {{ $messages->isNotEmpty() ? $messages->last()['id'] : 0 }};
        let pollTimer = null;
        let sidebarPollTimer = null;

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function scrollThreadToBottom() {
            const thread = document.getElementById('messageThread');
            if (thread) thread.scrollTop = thread.scrollHeight;
        }
        scrollThreadToBottom();

        // ── Render one message bubble (mirrors alumni/_chat-message-bubble.blade.php) ──
        function renderBubble(msg, isMine) {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex ' + (isMine ? 'justify-end' : 'justify-start');
            wrapper.dataset.messageId = msg.id;

            const avatarHtml = isMine ? '' : `<div class="w-8 h-8 rounded-full bg-[#0E0F3B] flex items-center justify-center overflow-hidden shrink-0 mr-2 self-end">${CONTACT_PHOTO ? `<img src="${CONTACT_PHOTO}" class="w-full h-full object-cover">` : '<i class="fas fa-user text-white text-[10px]"></i>'}</div>`;
            const nameHtml = isMine ? '' : `<p class="text-xs font-bold text-[#0E0F3B] mb-1">${escapeHtml(CONTACT_NAME)}</p>`;

            let attachmentHtml = '';
            if (msg.hasAttachment) {
                attachmentHtml = msg.isImage
                    ? `<img src="${msg.attachmentUrl}" class="rounded-lg max-w-full mb-1" style="max-height:200px">`
                    : `<a href="${msg.attachmentUrl}" target="_blank" class="flex items-center gap-2 underline"><i class="fas fa-file"></i> ${escapeHtml(msg.attachmentName || 'File')}</a>`;
            }
            const contentHtml = msg.content ? `<p>${escapeHtml(msg.content)}</p>` : '';

            wrapper.innerHTML = `
                ${avatarHtml}
                <div class="max-w-[60%]">
                    ${nameHtml}
                    <div class="${isMine ? 'bg-[#1D264F] text-white' : 'bg-gray-100 text-gray-800'} rounded-2xl px-4 py-2.5 text-sm break-words">
                        ${attachmentHtml}
                        ${contentHtml}
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1 ${isMine ? 'text-right' : ''}">${msg.timeLabel}</p>
                </div>
            `;
            return wrapper;
        }

        // ── Poll the open conversation for new messages ──
        async function pollMessages() {
            if (!CONVERSATION_ID) return;
            try {
                const res = await fetch(`${POLL_URL}?after=${lastMessageId}`);
                if (!res.ok) return;
                const newMessages = await res.json();
                if (newMessages.length > 0) {
                    const thread = document.getElementById('messageThread');
                    const wasNearBottom = thread.scrollHeight - thread.scrollTop - thread.clientHeight < 100;
                    newMessages.forEach(msg => {
                        thread.appendChild(renderBubble(msg, msg.senderId === CURRENT_USER_ID));
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });
                    if (wasNearBottom) scrollThreadToBottom();
                }
            } catch (e) { /* transient network hiccup — next tick retries */ }
        }

        // ── Poll the sidebar list (runs regardless of which conversation, if any, is open) ──
        async function pollSidebar() {
            try {
                const res = await fetch(SIDEBAR_POLL_URL);
                if (!res.ok) return;
                const conversations = await res.json();
                renderChatList(conversations);
            } catch (e) { /* transient network hiccup — next tick retries */ }
        }

        function renderChatList(conversations) {
            const container = document.getElementById('chatListContainer');
            if (!container) return;

            if (conversations.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-400 text-sm py-10 px-5">No conversations yet. Click the compose icon to message an alumnus.</p>';
                return;
            }

            container.innerHTML = conversations.map(c => `
                <a href="${MESSAGES_BASE_URL}/${c.id}" data-name="${escapeHtml(c.name.toLowerCase())}"
                    class="chat-list-item flex items-center gap-3 px-5 py-3 border-b border-gray-50 hover:bg-slate-50 transition-colors ${CONVERSATION_ID == c.id ? 'bg-orange-50' : ''}">
                    <div class="w-11 h-11 rounded-full bg-[#0E0F3B] flex items-center justify-center overflow-hidden shrink-0">
                        ${c.photo ? `<img src="${c.photo}" class="w-full h-full object-cover">` : '<i class="fas fa-user text-white text-sm"></i>'}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline">
                            <p class="font-bold text-[#0E0F3B] text-sm truncate">${escapeHtml(c.name)}</p>
                            <span class="text-[10px] text-gray-400 shrink-0 ml-2">${c.time}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-xs ${c.unread > 0 && CONVERSATION_ID != c.id ? 'font-bold text-[#0E0F3B]' : 'text-gray-500'} truncate">${escapeHtml(c.preview) || 'No messages yet'}</p>
                            ${c.unread > 0 && CONVERSATION_ID != c.id ? `<span class="bg-[#C73D1A] text-white text-[9px] font-bold rounded-full w-5 h-5 flex items-center justify-center shrink-0 ml-2">${c.unread}</span>` : ''}
                        </div>
                    </div>
                </a>
            `).join('');
            filterChatList();
        }

        function filterChatList() {
            const q = (document.getElementById('chatSearchInput')?.value || '').toLowerCase();
            document.querySelectorAll('.chat-list-item').forEach(item => {
                item.style.display = item.dataset.name.includes(q) ? '' : 'none';
            });
        }

        // ── "To:" typeahead ──
        let toSearchDebounce = null;
        const toSearchInput = document.getElementById('toSearchInput');
        if (toSearchInput) {
            toSearchInput.addEventListener('input', function () {
                clearTimeout(toSearchDebounce);
                const q = this.value.trim();
                const resultsBox = document.getElementById('toSearchResults');
                if (q.length < 1) {
                    resultsBox.classList.add('hidden');
                    return;
                }
                toSearchDebounce = setTimeout(async () => {
                    try {
                        const res = await fetch(`${SEARCH_CONTACTS_URL}?q=${encodeURIComponent(q)}`);
                        const contacts = await res.json();
                        resultsBox.innerHTML = contacts.length === 0
                            ? '<p class="text-xs text-gray-400 px-4 py-3">No alumni found.</p>'
                            : contacts.map(c => `
                                <button type="button" onclick="startConversation(${c.id})" class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 text-left transition-colors">
                                    <div class="w-8 h-8 rounded-full bg-[#0E0F3B] flex items-center justify-center overflow-hidden shrink-0">
                                        ${c.photo ? `<img src="${c.photo}" class="w-full h-full object-cover">` : '<i class="fas fa-user text-white text-[10px]"></i>'}
                                    </div>
                                    <span class="text-sm font-semibold text-[#0E0F3B]">${escapeHtml(c.name)}</span>
                                </button>
                            `).join('');
                        resultsBox.classList.remove('hidden');
                    } catch (e) { /* leave the box as-is on a transient failure */ }
                }, 250);
            });

            document.addEventListener('click', function (e) {
                if (!e.target.closest('#toSearchInput') && !e.target.closest('#toSearchResults')) {
                    document.getElementById('toSearchResults')?.classList.add('hidden');
                }
            });
        }

        function startConversation(recipientId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = START_CONVERSATION_URL;
            form.innerHTML = `<input type="hidden" name="_token" value="${CSRF_TOKEN}"><input type="hidden" name="recipient_id" value="${recipientId}">`;
            document.body.appendChild(form);
            form.submit();
        }

        // ── Composer ──
        let pendingAttachment = null;

        function handleAttachmentPreview(input) {
            if (input.files && input.files[0]) {
                pendingAttachment = input.files[0];
                document.getElementById('attachmentPreviewName').textContent = pendingAttachment.name;
                document.getElementById('attachmentPreview').classList.remove('hidden');
            }
        }

        function clearAttachment() {
            pendingAttachment = null;
            document.getElementById('attachmentInput').value = '';
            document.getElementById('attachmentPreview').classList.add('hidden');
        }

        const composerForm = document.getElementById('composerForm');
        if (composerForm) {
            composerForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const input = document.getElementById('messageInput');
                const content = input.value.trim();
                if (!content && !pendingAttachment) return;

                const formData = new FormData();
                formData.append('_token', CSRF_TOKEN);
                formData.append('message_content', content);
                if (pendingAttachment) formData.append('attachment', pendingAttachment);

                const submitBtn = composerForm.querySelector('button[type="submit"]');
                submitBtn.disabled = true;

                try {
                    const res = await fetch(SEND_URL, { method: 'POST', body: formData });
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        alert(err.error || 'Failed to send message.');
                        return;
                    }
                    const message = await res.json();
                    document.getElementById('messageThread').appendChild(renderBubble(message, true));
                    lastMessageId = Math.max(lastMessageId, message.id);
                    scrollThreadToBottom();
                    input.value = '';
                    clearAttachment();
                } catch (e) {
                    alert('Failed to send message. Please check your connection and try again.');
                } finally {
                    submitBtn.disabled = false;
                }
            });
        }

        // ── Info panel ──
        function toggleInfoPanel() {
            document.getElementById('infoPanel')?.classList.toggle('translate-x-full');
            document.getElementById('infoPanelOverlay')?.classList.toggle('hidden');
        }

        // ── Start/stop polling, and pause entirely while the tab is hidden —
        // meaningfully cuts server load from idle background tabs at scale. ──
        function startPolling() {
            if (CONVERSATION_ID && !pollTimer) {
                pollTimer = setInterval(pollMessages, 3000);
            }
            if (!sidebarPollTimer) {
                sidebarPollTimer = setInterval(pollSidebar, 8000);
            }
        }

        function stopPolling() {
            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
            if (sidebarPollTimer) { clearInterval(sidebarPollTimer); sidebarPollTimer = null; }
        }

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopPolling();
            } else {
                if (CONVERSATION_ID) pollMessages();
                pollSidebar();
                startPolling();
            }
        });

        startPolling();
    </script>
</body>

</html>
