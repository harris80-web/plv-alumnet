<!-- ══════════════════════ FLOATING CHATBOT WIDGET ══════════════════════ -->
<button type="button" id="chatWidgetButton" onclick="toggleChatWidget()"
    class="fixed bottom-6 right-6 z-[90] w-14 h-14 rounded-full bg-[#1D264F] hover:bg-[#0E0F3B] text-white shadow-xl flex items-center justify-center transition-transform hover:scale-105">
    <i data-lucide="message-circle" class="w-6 h-6" id="chatWidgetIcon"></i>
</button>

<div id="chatWidgetPanel" class="fixed bottom-24 right-6 z-[90] w-[23rem] max-w-[calc(100vw-2rem)] h-[32rem] max-h-[calc(100vh-8rem)] bg-white rounded-2xl shadow-2xl hidden flex-col overflow-hidden border border-slate-200">
    <div class="bg-[#0E0F3B] p-4 flex items-center justify-between shrink-0">
        <div>
            <p class="text-white font-bold text-sm">PLV-AlumNet Assistant</p>
            <p id="chatWidgetStatus" class="text-[10px] text-white/60">Starting...</p>
        </div>
        <button type="button" onclick="toggleChatWidget()" class="text-white/70 hover:text-white">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <div id="chatWidgetThread" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50"></div>

    <div id="chatWidgetTyping" class="hidden px-4 pb-1 text-[10px] text-slate-400 italic">Assistant is typing...</div>

    <form id="chatWidgetForm" class="p-3 border-t border-slate-100 shrink-0 flex gap-2">
        <input type="text" id="chatWidgetInput" placeholder="Type your question..." autocomplete="off"
            class="flex-1 border border-slate-200 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30">
        <button type="submit" class="w-9 h-9 shrink-0 rounded-full bg-[#1D264F] hover:bg-[#0E0F3B] text-white flex items-center justify-center disabled:opacity-50">
            <i data-lucide="send" class="w-4 h-4"></i>
        </button>
    </form>
</div>

<script>
    (function () {
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const START_URL = {!! json_encode(route('widget.start')) !!};
        const BASE_URL = {!! json_encode(url('/chatbot')) !!};

        let ticketId = null;
        let ticketStatus = null;
        let lastMessageId = 0;
        let pollTimer = null;
        let started = false;

        const statusLabels = {
            ai_active: 'Chatting with AI assistant',
            waiting_agent: 'Waiting for a live agent...',
            with_agent: 'Connected with a live agent',
            resolved: 'Conversation resolved',
        };

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function scrollThreadToBottom() {
            const thread = document.getElementById('chatWidgetThread');
            if (thread) thread.scrollTop = thread.scrollHeight;
        }

        function renderBubble(msg) {
            const wrapper = document.createElement('div');
            const isMine = msg.senderType === 'user';
            wrapper.className = 'flex ' + (isMine ? 'justify-end' : 'justify-start');
            wrapper.dataset.msgId = msg.id;

            const bubbleClass = isMine
                ? 'bg-[#1D264F] text-white'
                : (msg.senderType === 'agent' ? 'bg-green-600 text-white' : 'bg-white border border-slate-200 text-slate-700');

            const labelHtml = msg.senderType === 'agent'
                ? `<p class="text-[9px] font-bold text-green-600 mb-0.5">${escapeHtml(msg.senderName || 'Agent')} (Live Agent)</p>`
                : (msg.senderType === 'ai' ? '<p class="text-[9px] font-bold text-slate-400 mb-0.5">AI Assistant</p>' : '');

            wrapper.innerHTML = `
                <div class="max-w-[80%]">
                    ${labelHtml}
                    <div class="${bubbleClass} rounded-2xl px-3.5 py-2 text-xs leading-relaxed break-words">${escapeHtml(msg.message)}</div>
                </div>
            `;
            return wrapper;
        }

        function appendMessages(messages) {
            const thread = document.getElementById('chatWidgetThread');
            messages.forEach(m => {
                if (m.id <= lastMessageId && thread.querySelector(`[data-msg-id="${m.id}"]`)) return;
                thread.appendChild(renderBubble(m));
                lastMessageId = Math.max(lastMessageId, m.id);
            });
            scrollThreadToBottom();
        }

        function setStatus(status) {
            ticketStatus = status;
            document.getElementById('chatWidgetStatus').textContent = statusLabels[status] || status;
            const input = document.getElementById('chatWidgetInput');
            const submitBtn = document.querySelector('#chatWidgetForm button[type="submit"]');
            const resolved = status === 'resolved';
            input.disabled = resolved;
            submitBtn.disabled = resolved;
            input.placeholder = resolved ? 'This conversation has ended.' : 'Type your question...';

            if ((status === 'waiting_agent' || status === 'with_agent') && !pollTimer) {
                pollTimer = setInterval(pollWidget, 4000);
            } else if (status === 'resolved' && pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        }

        async function startSession() {
            if (started) return;
            started = true;
            try {
                const res = await fetch(START_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                });
                const data = await res.json();
                ticketId = data.ticketId;
                appendMessages(data.messages);
                setStatus(data.status);
            } catch (e) {
                document.getElementById('chatWidgetStatus').textContent = 'Unable to connect. Please try again.';
            }
        }

        async function pollWidget() {
            if (!ticketId) return;
            try {
                const res = await fetch(`${BASE_URL}/${ticketId}/poll?after=${lastMessageId}`);
                if (!res.ok) return;
                const data = await res.json();
                appendMessages(data.messages);
                if (data.status !== ticketStatus) setStatus(data.status);
            } catch (e) { /* transient network hiccup — next tick retries */ }
        }

        async function sendMessage(text) {
            if (!ticketId) return;
            const formData = new FormData();
            formData.append('_token', CSRF_TOKEN);
            formData.append('message', text);

            const submitBtn = document.querySelector('#chatWidgetForm button[type="submit"]');
            submitBtn.disabled = true;
            // Shown only for the AI-reply wait — an agent's reply arrives later via polling, not this request.
            document.getElementById('chatWidgetTyping').classList.toggle('hidden', ticketStatus !== 'ai_active');

            try {
                const res = await fetch(`${BASE_URL}/${ticketId}/send`, { method: 'POST', body: formData });
                const data = await res.json();
                // Full thread comes back (not just new messages) — appendMessages
                // already skips anything with an id it has rendered before, so
                // this naturally renders just the new user + AI turns.
                appendMessages(data.messages);
                if (data.status !== ticketStatus) setStatus(data.status);
            } catch (e) {
                alert('Failed to send your message. Please check your connection and try again.');
            } finally {
                document.getElementById('chatWidgetTyping').classList.add('hidden');
                submitBtn.disabled = false;
            }
        }

        function toggleChatWidget() {
            const panel = document.getElementById('chatWidgetPanel');
            const willShow = panel.classList.contains('hidden');
            panel.classList.toggle('hidden');
            panel.classList.toggle('flex', willShow);

            if (willShow) {
                startSession(); // no-ops on repeat opens — the ticket/thread persists across them
                if (!pollTimer && (ticketStatus === 'waiting_agent' || ticketStatus === 'with_agent')) {
                    pollTimer = setInterval(pollWidget, 4000);
                }
                scrollThreadToBottom();
            } else if (pollTimer) {
                // Closed — stop polling rather than fetching for a panel no one can see.
                clearInterval(pollTimer);
                pollTimer = null;
            }

            if (window.lucide) lucide.createIcons();
        }
        window.toggleChatWidget = toggleChatWidget;

        document.getElementById('chatWidgetForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const input = document.getElementById('chatWidgetInput');
            const text = input.value.trim();
            if (!text) return;
            input.value = '';
            sendMessage(text);
        });
    })();
</script>
