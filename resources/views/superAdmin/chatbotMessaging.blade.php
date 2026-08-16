<?php $current_page = 'messaging'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot & Messaging | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        ::-webkit-scrollbar { display: none; }
        * { -ms-overflow-style: none; scrollbar-width: none; }

        .page-tab-btn { border-bottom: 3px solid transparent; color: #64748b; }
        .page-tab-btn.active { border-color: #C73D1A; color: #0E0F3B; }

        .bar-track { background: #e2e8f0; border-radius: 999px; overflow: hidden; height: 8px; }
        .bar-fill { height: 100%; border-radius: 999px; }

        .toggle-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; inset: 0; background-color: #cbd5e1; transition: .2s; border-radius: 999px; }
        .toggle-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .2s; border-radius: 50%; }
        .toggle-switch input:checked + .toggle-slider { background-color: #1D264F; }
        .toggle-switch input:checked + .toggle-slider:before { transform: translateX(18px); }

        #queueThreadModal>div, #flagDetailPanel { scrollbar-width: none; -ms-overflow-style: none; }
        #queueThreadModal>div::-webkit-scrollbar { display: none; }

        .msg-bubble-user { background: #f1f5f9; color: #0E0F3B; }
        .msg-bubble-ai { background: #eef2ff; color: #3730a3; }
        .msg-bubble-agent { background: #1D264F; color: white; }
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

                <h1 class="text-2xl font-bold text-[#0E0F3B] mb-4">Chatbot &amp; Messaging</h1>

                <!-- PAGE TABS -->
                <div class="flex gap-6 border-b border-slate-200 mb-6 overflow-x-auto">
                    <button type="button" onclick="switchPageTab('overview')" id="pageTabBtn-overview" class="page-tab-btn active px-1 pb-3 text-sm font-bold uppercase tracking-wide transition-colors whitespace-nowrap">Overview</button>
                    <button type="button" onclick="switchPageTab('aichatbot')" id="pageTabBtn-aichatbot" class="page-tab-btn px-1 pb-3 text-sm font-bold uppercase tracking-wide transition-colors whitespace-nowrap">AI Chatbot</button>
                    <button type="button" onclick="switchPageTab('queue')" id="pageTabBtn-queue" class="page-tab-btn px-1 pb-3 text-sm font-bold uppercase tracking-wide transition-colors whitespace-nowrap">Live Agent Queue</button>
                    <button type="button" onclick="switchPageTab('alumnimsg')" id="pageTabBtn-alumnimsg" class="page-tab-btn px-1 pb-3 text-sm font-bold uppercase tracking-wide transition-colors whitespace-nowrap">Alumni Messaging</button>
                    <button type="button" onclick="switchPageTab('reports')" id="pageTabBtn-reports" class="page-tab-btn px-1 pb-3 text-sm font-bold uppercase tracking-wide transition-colors whitespace-nowrap">Reports</button>
                    <button type="button" onclick="switchPageTab('settings')" id="pageTabBtn-settings" class="page-tab-btn px-1 pb-3 text-sm font-bold uppercase tracking-wide transition-colors whitespace-nowrap">Settings</button>
                </div>

                <!-- ══════════════════════ OVERVIEW ══════════════════════ -->
                <div id="pageTab-overview">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $overview['activeAiSessions'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Active AI Sessions</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-600">{{ $overview['aiResolutionRate'] }}%</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">AI resolution rate</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-600">{{ $overview['pendingEscalations'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Pending escalations</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-blue-600">{{ $overview['alumniMessagesToday'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Alumni Messages today</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-red-600">{{ $overview['detectedMessages'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Detected Messages</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4">
                            <h3 class="text-xs font-bold text-slate-500 uppercase mb-3">Active AI sessions</h3>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @forelse ($activeAiSessions->take(6) as $t)
                                <div class="flex items-center gap-2 text-xs border-b border-slate-50 pb-2">
                                    <div class="w-7 h-7 rounded-full bg-[#0E0F3B] flex items-center justify-center text-white text-[10px] font-bold shrink-0">{{ mb_substr($t->user->user_first_name ?? '?', 0, 1) }}</div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-[#0E0F3B] truncate">{{ trim(($t->user->user_first_name ?? '') . ' ' . ($t->user->user_last_name ?? '')) }}</p>
                                        <p class="text-slate-400 truncate">{{ \Illuminate\Support\Str::limit($t->latestMessage->message ?? '—', 40) }}</p>
                                    </div>
                                </div>
                                @empty
                                <p class="text-slate-400 text-xs">No active AI sessions.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4">
                            <h3 class="text-xs font-bold text-slate-500 uppercase mb-3">Live agent queue</h3>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @forelse ($waitingTickets->take(6) as $t)
                                <div class="flex items-center gap-2 text-xs border-b border-slate-50 pb-2">
                                    <div class="w-7 h-7 rounded-full bg-amber-500 flex items-center justify-center text-white text-[10px] font-bold shrink-0">{{ mb_substr($t->user->user_first_name ?? '?', 0, 1) }}</div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-[#0E0F3B] truncate">{{ trim(($t->user->user_first_name ?? '') . ' ' . ($t->user->user_last_name ?? '')) }}</p>
                                        <p class="text-slate-400 truncate">{{ \Illuminate\Support\Str::limit($t->latestMessage->message ?? '—', 40) }}</p>
                                    </div>
                                </div>
                                @empty
                                <p class="text-slate-400 text-xs">Nothing waiting right now.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4">
                            <h3 class="text-xs font-bold text-slate-500 uppercase mb-3">Detected Messages</h3>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @forelse ($pendingFlags->take(6) as $f)
                                <div class="flex items-center gap-2 text-xs border-b border-slate-50 pb-2">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 shrink-0"></i>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-[#0E0F3B] truncate">{{ trim(($f->message->sender->user_first_name ?? '') . ' ' . ($f->message->sender->user_last_name ?? '')) }}</p>
                                        <p class="text-slate-400 truncate">{{ implode(', ', $f->reasonLabelsList()) }}</p>
                                    </div>
                                </div>
                                @empty
                                <p class="text-slate-400 text-xs">Nothing flagged right now.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
                            <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Escalation flow</h3>
                            <ol class="space-y-3 text-xs">
                                <li class="flex gap-3"><span class="w-5 h-5 rounded-full bg-[#0E0F3B] text-white flex items-center justify-center font-bold shrink-0">1</span> User sends a message to the AI chatbot</li>
                                <li class="flex gap-3"><span class="w-5 h-5 rounded-full bg-[#0E0F3B] text-white flex items-center justify-center font-bold shrink-0">2</span> AI attempts to answer from the FAQ knowledge base</li>
                                <li class="flex gap-3"><span class="w-5 h-5 rounded-full bg-[#0E0F3B] text-white flex items-center justify-center font-bold shrink-0">3</span> After {{ $settings->escalate_after_failed_attempts }} unanswered attempts, the ticket escalates</li>
                                <li class="flex gap-3"><span class="w-5 h-5 rounded-full bg-[#0E0F3B] text-white flex items-center justify-center font-bold shrink-0">4</span> Ticket appears in the Live Agent Queue for staff to claim</li>
                                <li class="flex gap-3"><span class="w-5 h-5 rounded-full bg-[#0E0F3B] text-white flex items-center justify-center font-bold shrink-0">5</span> Agent resolves and closes the thread</li>
                            </ol>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
                            <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Session outcome breakdown</h3>
                            @php
                                $outcomeTotal = max($allTicketsCountOverview = \App\Models\ChatTicket::count(), 1);
                                $outcomes = [
                                    ['label' => 'Answered by AI', 'count' => \App\Models\ChatTicket::whereNull('escalated_at')->count(), 'color' => '#16a34a'],
                                    ['label' => 'Escalated to agent', 'count' => \App\Models\ChatTicket::whereNotNull('escalated_at')->count(), 'color' => '#C73D1A'],
                                ];
                            @endphp
                            <div class="space-y-4">
                                @foreach ($outcomes as $o)
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="font-semibold text-[#0E0F3B]">{{ $o['label'] }}</span>
                                        <span class="text-slate-500">{{ round(($o['count'] / $outcomeTotal) * 100) }}%</span>
                                    </div>
                                    <div class="bar-track"><div class="bar-fill" style="width: {{ round(($o['count'] / $outcomeTotal) * 100) }}%; background: {{ $o['color'] }};"></div></div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════ AI CHATBOT ══════════════════════ -->
                <div id="pageTab-aichatbot" class="hidden">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $aiChatbot['activeAiSessions'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Active AI Sessions</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-600">{{ $aiChatbot['resolvedToday'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Resolved today</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-600">{{ $aiChatbot['escalatedToday'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Escalated today</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-blue-600">{{ $aiChatbot['resolutionRate'] }}%</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Resolution rate</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4 lg:col-span-1">
                            <h3 class="text-xs font-bold text-slate-500 uppercase mb-3">All active AI sessions</h3>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @forelse ($activeAiSessions as $t)
                                <div class="flex items-center gap-2 text-xs border-b border-slate-50 pb-2">
                                    <div class="w-7 h-7 rounded-full bg-[#0E0F3B] flex items-center justify-center text-white text-[10px] font-bold shrink-0">{{ mb_substr($t->user->user_first_name ?? '?', 0, 1) }}</div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-[#0E0F3B] truncate">{{ trim(($t->user->user_first_name ?? '') . ' ' . ($t->user->user_last_name ?? '')) }}</p>
                                        <p class="text-slate-400 truncate">{{ \Illuminate\Support\Str::limit($t->latestMessage->message ?? '—', 40) }}</p>
                                    </div>
                                </div>
                                @empty
                                <p class="text-slate-400 text-xs">No active AI sessions.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm lg:col-span-2">
                            <div class="flex items-center justify-between p-4 border-b border-slate-100">
                                <div>
                                    <h3 class="text-sm font-bold text-[#0E0F3B]">AI Knowledge base</h3>
                                    <p class="text-[10px] text-slate-400">The chatbot answers using your FAQ entries — manage the full list from the FAQs page.</p>
                                </div>
                                <a href="{{ route('faqs.management') }}" class="flex items-center gap-2 bg-[#1D264F] hover:bg-[#0E0F3B] text-white px-4 py-2 rounded-lg text-[10px] font-bold uppercase whitespace-nowrap">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Manage FAQs
                                </a>
                            </div>
                            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                                <table class="w-full text-xs">
                                    <thead class="bg-[#0E0F3B] text-white sticky top-0">
                                        <tr>
                                            <th class="text-left px-4 py-2 font-bold uppercase text-[9px]">Question</th>
                                            <th class="text-left px-4 py-2 font-bold uppercase text-[9px]">Topic</th>
                                            <th class="text-right px-4 py-2 font-bold uppercase text-[9px]">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($faqs as $faq)
                                        <tr class="border-b border-slate-100">
                                            <td class="px-4 py-2.5 text-left text-[#0E0F3B]">{{ \Illuminate\Support\Str::limit($faq->faq_question, 60) }}</td>
                                            <td class="px-4 py-2.5 text-left">
                                                <span class="px-2 py-1 rounded-full text-[9px] font-bold uppercase {{ $faq->badgeClass() }}">{{ $faq->recipientLabel() }}</span>
                                            </td>
                                            <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                                <a href="{{ route('faqs.management') }}" class="text-blue-600 hover:underline mr-3">Edit</a>
                                                <form action="{{ route('faqs.destroy', $faq->faq_id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this FAQ?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-slate-400 py-8">No FAQs yet — add some from the FAQs page.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════ LIVE AGENT QUEUE ══════════════════════ -->
                <div id="pageTab-queue" class="hidden">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-600">{{ $liveQueue['totalInQueue'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Total in queue</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $liveQueue['avgWaitMinutes'] }}m</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Avg. wait time</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-600">{{ $liveQueue['resolvedTodayByAgents'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Resolved today</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-blue-600">{{ $liveQueue['agentsOnline'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Agents available</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 w-full">
                        <div class="p-4 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-[#0E0F3B]">Full queue</h3>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @forelse ($waitingTickets->concat($withAgentTickets) as $t)
                            <div class="flex items-center gap-3 px-4 py-3">
                                <div class="w-9 h-9 rounded-full bg-[#0E0F3B] flex items-center justify-center text-white text-xs font-bold shrink-0">{{ mb_substr($t->user->user_first_name ?? '?', 0, 1) }}</div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-[#0E0F3B] text-sm truncate">{{ trim(($t->user->user_first_name ?? '') . ' ' . ($t->user->user_last_name ?? '')) }}</p>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $t->badgeClass() }}">{{ $t->statusLabel() }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500 truncate">{{ \Illuminate\Support\Str::limit($t->latestMessage->message ?? '—', 70) }}</p>
                                </div>
                                <span class="text-[10px] text-slate-400 shrink-0">{{ $t->latestMessage?->created_at?->diffForHumans() }}</span>
                                @if ($t->status === 'waiting_agent')
                                <form action="{{ route('chatbot.claim', $t->ticket_id) }}" method="POST" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-[10px] font-bold px-3 py-2 rounded-md uppercase whitespace-nowrap">Assign to me</button>
                                </form>
                                @endif
                                <button type="button" onclick="openQueueThread({{ $t->ticket_id }})" class="shrink-0 bg-[#1D264F] hover:bg-[#0E0F3B] text-white text-[10px] font-bold px-3 py-2 rounded-md uppercase whitespace-nowrap">Open thread</button>
                            </div>
                            @empty
                            <p class="text-center text-slate-400 text-sm py-12">The queue is empty — no one is waiting for an agent right now.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════ ALUMNI MESSAGING (auditing) ══════════════════════ -->
                <div id="pageTab-alumnimsg" class="hidden">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $alumniMessaging['threadsToday'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Total threads today</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-red-600">{{ $alumniMessaging['autoFlagged'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Auto-flagged</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-600">{{ $alumniMessaging['warningsThisWeek'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Warnings issued this week</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $alumniMessaging['mutedThisMonth'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Muted this month</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm lg:col-span-1">
                            <div class="p-4 border-b border-slate-100">
                                <h3 class="text-sm font-bold text-[#0E0F3B]">Message threads</h3>
                            </div>
                            <div class="divide-y divide-slate-100 max-h-[32rem] overflow-y-auto" id="flagList">
                                @forelse ($allFlags as $flag)
                                @php
                                    $sender = $flag->message->sender;
                                    $receiver = $flag->message->receiver;
                                    $flagData = [
                                        'id' => $flag->id,
                                        'senderName' => trim(($sender->user_first_name ?? '?') . ' ' . ($sender->user_last_name ?? '')),
                                        'receiverName' => trim(($receiver->user_first_name ?? '?') . ' ' . ($receiver->user_last_name ?? '')),
                                        'content' => $flag->message->message_content,
                                        'reasons' => $flag->reasonLabelsList(),
                                        'status' => $flag->status,
                                        'createdAt' => $flag->created_at->format('M d, Y - h:i A'),
                                    ];
                                @endphp
                                <button type="button" onclick='openFlagDetail(@json($flagData))' class="w-full text-left px-4 py-3 hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-xs font-semibold text-[#0E0F3B] truncate">{{ trim(($sender->user_first_name ?? '?') . ' ' . ($sender->user_last_name ?? '')) }} → {{ trim(($receiver->user_first_name ?? '?') . ' ' . ($receiver->user_last_name ?? '')) }}</p>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase shrink-0 ml-2 {{ $flag->status === 'pending' ? 'bg-red-100 text-red-700' : ($flag->status === 'dismissed' ? 'bg-slate-100 text-slate-500' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($flag->status) }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 truncate">{{ \Illuminate\Support\Str::limit($flag->message->message_content, 55) }}</p>
                                </button>
                                @empty
                                <p class="text-slate-400 text-xs p-4">No flagged messages yet.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm lg:col-span-2 p-5" id="flagDetailPanel">
                            <div id="flagDetailEmpty" class="text-center text-slate-400 text-sm py-20">
                                <i data-lucide="shield-alert" class="w-10 h-10 mx-auto mb-3"></i>
                                Select a flagged thread on the left to review it.
                            </div>
                            <div id="flagDetailContent" class="hidden">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-bold text-[#0E0F3B]" id="fd-title"></h3>
                                    <span id="fd-status" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase"></span>
                                </div>
                                <p class="text-[10px] text-slate-400 mb-1">Flagged reasons</p>
                                <div id="fd-reasons" class="flex gap-2 flex-wrap mb-4"></div>
                                <p class="text-[10px] text-slate-400 mb-1">Flagged message</p>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-900 mb-1" id="fd-content"></div>
                                <p class="text-[10px] text-slate-400 mb-4" id="fd-date"></p>

                                <form id="flagActionForm" method="POST" class="flex gap-2 flex-wrap">
                                    @csrf
                                    <button type="button" onclick="submitFlagAction('warned')" class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2 rounded-md uppercase">Warn sender</button>
                                    <button type="button" onclick="submitFlagAction('muted')" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-2 rounded-md uppercase">Mute sender</button>
                                    <button type="button" onclick="submitFlagAction('dismissed')" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-4 py-2 rounded-md uppercase">Not a violation</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════ REPORTS ══════════════════════ -->
                <div id="pageTab-reports" class="hidden">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $reports['totalSessionsThisWeek'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Total chatbot sessions this week</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-600">{{ $reports['aiResolvedThisWeek'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">AI resolved</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-600">{{ $reports['escalatedThisWeek'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Escalated to agent</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-blue-600">{{ $reports['avgResolutionMinutes'] }}m</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Avg. resolution time</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
                            <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Sessions by user type (this week)</h3>
                            @php $roleTotal = max(array_sum($reports['sessionsByRole']), 1); @endphp
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-xs mb-1"><span class="font-semibold text-[#0E0F3B]">Alumni</span><span class="text-slate-500">{{ $reports['sessionsByRole']['alumni'] }}</span></div>
                                    <div class="bar-track"><div class="bar-fill" style="width: {{ round(($reports['sessionsByRole']['alumni'] / $roleTotal) * 100) }}%; background:#0E0F3B;"></div></div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1"><span class="font-semibold text-[#0E0F3B]">Employer</span><span class="text-slate-500">{{ $reports['sessionsByRole']['employer'] }}</span></div>
                                    <div class="bar-track"><div class="bar-fill" style="width: {{ round(($reports['sessionsByRole']['employer'] / $roleTotal) * 100) }}%; background:#C73D1A;"></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
                            <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Flagged message breakdown</h3>
                            @php $flagTotal = max($reports['flagBreakdown']->sum('count'), 1); @endphp
                            <div class="space-y-4">
                                @foreach ($reports['flagBreakdown'] as $fb)
                                <div>
                                    <div class="flex justify-between text-xs mb-1"><span class="font-semibold text-[#0E0F3B]">{{ $fb['label'] }}</span><span class="text-slate-500">{{ $fb['count'] }}</span></div>
                                    <div class="bar-track"><div class="bar-fill" style="width: {{ round(($fb['count'] / $flagTotal) * 100) }}%; background:#C73D1A;"></div></div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
                        <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Message volume per day (last 7 days)</h3>
                        @php $volMax = max($reports['messageVolumePerDay']->max('count'), 1); @endphp
                        <div class="space-y-3">
                            @foreach ($reports['messageVolumePerDay'] as $day)
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] text-slate-400 w-8 shrink-0">{{ $day['label'] }}</span>
                                <div class="bar-track flex-1"><div class="bar-fill" style="width: {{ round(($day['count'] / $volMax) * 100) }}%; background:#1D264F;"></div></div>
                                <span class="text-[10px] text-slate-500 w-6 text-right shrink-0">{{ $day['count'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════ SETTINGS ══════════════════════ -->
                <div id="pageTab-settings" class="hidden">
                    <form action="{{ route('chatbot.settings.update') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
                                <h3 class="text-sm font-bold text-[#0E0F3B] mb-4">Chatbot settings</h3>
                                <div class="space-y-4">
                                    @foreach ([
                                        'ai_chatbot_enabled' => 'AI chatbot enabled',
                                        'live_agent_escalation_enabled' => 'Live agent escalation',
                                        'job_board_queries_enabled' => 'Job board queries',
                                        'events_queries_enabled' => 'Events & announcement queries',
                                        'general_faq_queries_enabled' => 'General FAQ queries',
                                        'career_advice_queries_enabled' => 'Career / personal advice queries',
                                    ] as $key => $label)
                                    <label class="flex items-center justify-between gap-3 cursor-pointer">
                                        <span class="text-xs text-[#0E0F3B]">{{ $label }}</span>
                                        <span class="toggle-switch">
                                            <input type="checkbox" name="{{ $key }}" value="1" {{ $settings->$key ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
                                <h3 class="text-sm font-bold text-[#0E0F3B] mb-4">Messaging settings</h3>
                                <div class="space-y-4">
                                    @foreach ([
                                        'chat_auditing_enabled' => 'Chat auditing enabled',
                                        'money_transfer_detection' => 'Money transfer detection',
                                        'personal_info_detection' => 'Personal info detection',
                                        'external_link_detection' => 'External link detection',
                                        'auto_notify_admin_on_flag' => 'Auto-notify admin on flag',
                                    ] as $key => $label)
                                    <label class="flex items-center justify-between gap-3 cursor-pointer">
                                        <span class="text-xs text-[#0E0F3B]">{{ $label }}</span>
                                        <span class="toggle-switch">
                                            <input type="checkbox" name="{{ $key }}" value="1" {{ $settings->$key ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5 lg:col-span-2">
                                <h3 class="text-sm font-bold text-[#0E0F3B] mb-4">Escalation settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label class="flex items-center justify-between gap-3">
                                        <span class="text-xs text-[#0E0F3B]">Escalate after how many failed AI attempts</span>
                                        <input type="number" name="escalate_after_failed_attempts" min="1" max="10" value="{{ $settings->escalate_after_failed_attempts }}"
                                            class="w-20 border border-slate-300 rounded-lg px-3 py-1.5 text-sm text-center">
                                    </label>
                                    <label class="flex items-center justify-between gap-3 cursor-pointer">
                                        <span class="text-xs text-[#0E0F3B]">Auto-assign to available agent</span>
                                        <span class="toggle-switch">
                                            <input type="checkbox" name="auto_assign_available_agent" value="1" {{ $settings->auto_assign_available_agent ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </span>
                                    </label>
                                    <label class="flex items-center justify-between gap-3 cursor-pointer">
                                        <span class="text-xs text-[#0E0F3B]">Allow queue wait-time estimation</span>
                                        <span class="toggle-switch">
                                            <input type="checkbox" name="allow_queue_estimation" value="1" {{ $settings->allow_queue_estimation ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </span>
                                    </label>
                                    <label class="flex items-center justify-between gap-3 cursor-pointer">
                                        <span class="text-xs text-[#0E0F3B]">Live agent notification</span>
                                        <span class="toggle-switch">
                                            <input type="checkbox" name="live_agent_notification" value="1" {{ $settings->live_agent_notification ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="mt-5 bg-[#C73D1A] hover:bg-orange-700 text-white text-xs font-bold px-6 py-3 rounded-lg uppercase">Save Settings</button>
                    </form>
                </div>

            </div>
        </main>
    </div>

    <!-- ══════════════════════ QUEUE THREAD MODAL ══════════════════════ -->
    <div id="queueThreadModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden mx-4 flex flex-col" style="height: 80vh;">
            <div class="relative bg-[#0E0F3B] flex items-center justify-between p-5 shrink-0">
                <h2 class="text-lg font-bold text-white" id="qt-title">Conversation</h2>
                <button type="button" id="qt-close-btn" onclick="closeQueueThread()" class="text-white/80 hover:text-white"><i data-lucide="x-circle" class="w-6 h-6"></i></button>
            </div>
            <div id="qt-messages" class="flex-1 overflow-y-auto p-5 space-y-3 bg-slate-50"></div>
            <div class="p-4 border-t border-slate-100 shrink-0">
                <div class="flex gap-2">
                    <input type="text" id="qt-input" placeholder="Type a reply..." class="flex-1 border border-slate-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20">
                    <button type="button" id="qt-send-btn" onclick="sendAgentReply()" class="w-10 h-10 rounded-full bg-[#1D264F] hover:bg-[#0E0F3B] text-white flex items-center justify-center shrink-0"><i data-lucide="send" class="w-4 h-4"></i></button>
                </div>
                <button type="button" id="qt-resolve-btn" onclick="resolveQueueTicket()" class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-2 rounded-lg uppercase">Mark resolved</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });
        const CSRF_TOKEN = '{{ csrf_token() }}';

        // ── PAGE TABS ──
        function switchPageTab(tab) {
            ['overview', 'aichatbot', 'queue', 'alumnimsg', 'reports', 'settings'].forEach(t => {
                document.getElementById('pageTab-' + t).classList.toggle('hidden', t !== tab);
                document.getElementById('pageTabBtn-' + t).classList.toggle('active', t === tab);
            });
            if (window.lucide) lucide.createIcons();
        }

        // ── ALUMNI MESSAGING: flag detail panel ──
        let currentFlagId = null;
        const reasonColors = { money_transfer: 'bg-red-100 text-red-700', personal_info: 'bg-purple-100 text-purple-700', external_link: 'bg-blue-100 text-blue-700' };

        function openFlagDetail(data) {
            currentFlagId = data.id;
            document.getElementById('flagDetailEmpty').classList.add('hidden');
            document.getElementById('flagDetailContent').classList.remove('hidden');

            document.getElementById('fd-title').textContent = data.senderName + ' → ' + data.receiverName;
            const statusEl = document.getElementById('fd-status');
            statusEl.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
            statusEl.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase ' + (data.status === 'pending' ? 'bg-red-100 text-red-700' : (data.status === 'dismissed' ? 'bg-slate-100 text-slate-500' : 'bg-amber-100 text-amber-700'));

            document.getElementById('fd-reasons').innerHTML = data.reasons.map(r => {
                const key = Object.keys(reasonColors).find(k => r.toLowerCase().includes(k.split('_')[0])) || '';
                return `<span class="px-2 py-1 rounded-full text-[9px] font-bold uppercase bg-red-100 text-red-700">${r}</span>`;
            }).join('');

            document.getElementById('fd-content').textContent = data.content || '(attachment only)';
            document.getElementById('fd-date').textContent = 'Flagged ' + data.createdAt;

            if (window.lucide) lucide.createIcons();
        }

        function submitFlagAction(action) {
            if (!currentFlagId) return;
            if (action === 'muted' && !confirm('Mute this sender? They will not be able to send further messages.')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/chatbotMessaging/flags') }}/${currentFlagId}/action`;
            form.innerHTML = `<input type="hidden" name="_token" value="${CSRF_TOKEN}"><input type="hidden" name="action" value="${action}">`;
            document.body.appendChild(form);
            form.submit();
        }

        // ── LIVE AGENT QUEUE: thread modal ──
        let queueThreadTicketId = null;
        let queueThreadPollTimer = null;
        let queueThreadLastId = 0;

        function renderQueueMessages(messages, append) {
            const container = document.getElementById('qt-messages');
            if (!append) container.innerHTML = '';
            messages.forEach(m => {
                const bubbleClass = m.senderType === 'agent' ? 'msg-bubble-agent ml-auto' : (m.senderType === 'ai' ? 'msg-bubble-ai' : 'msg-bubble-user');
                const div = document.createElement('div');
                div.className = 'max-w-[75%] rounded-2xl px-4 py-2 text-xs ' + bubbleClass;
                div.textContent = m.message;
                container.appendChild(div);
                queueThreadLastId = Math.max(queueThreadLastId, m.id);
            });
            container.scrollTop = container.scrollHeight;
        }

        function applyThreadStatus(status) {
            const input = document.getElementById('qt-input');
            const sendBtn = document.getElementById('qt-send-btn');
            const resolveBtn = document.getElementById('qt-resolve-btn');
            const canReply = status === 'with_agent';

            input.disabled = !canReply;
            sendBtn.disabled = !canReply;
            resolveBtn.classList.toggle('hidden', status === 'resolved');
            input.placeholder = status === 'waiting_agent'
                ? 'Claim this ticket from the queue to reply'
                : (status === 'resolved' ? 'This conversation is resolved.' : 'Type a reply...');
        }

        async function openQueueThread(ticketId) {
            queueThreadTicketId = ticketId;
            queueThreadLastId = 0;
            document.getElementById('queueThreadModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            const res = await fetch(`{{ url('/chatbotMessaging') }}/${ticketId}/thread`);
            const data = await res.json();
            if (queueThreadTicketId !== ticketId) return; // modal was closed/switched while this was in flight
            document.getElementById('qt-title').textContent = data.userName;
            applyThreadStatus(data.status);
            renderQueueMessages(data.messages, false);

            queueThreadPollTimer = setInterval(async () => {
                const r = await fetch(`{{ url('/chatbotMessaging') }}/${ticketId}/thread`);
                const d = await r.json();
                if (queueThreadTicketId !== ticketId) return; // stale tick from a since-closed/switched thread
                applyThreadStatus(d.status);
                const newOnes = d.messages.filter(m => m.id > queueThreadLastId);
                if (newOnes.length) renderQueueMessages(newOnes, true);
            }, 4000);
        }

        function closeQueueThread() {
            document.getElementById('queueThreadModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            if (queueThreadPollTimer) { clearInterval(queueThreadPollTimer); queueThreadPollTimer = null; }
        }

        async function sendAgentReply() {
            const input = document.getElementById('qt-input');
            const message = input.value.trim();
            if (!message || !queueThreadTicketId) return;

            const formData = new FormData();
            formData.append('_token', CSRF_TOKEN);
            formData.append('message', message);

            const replyRes = await fetch(`{{ url('/chatbotMessaging') }}/${queueThreadTicketId}/reply`, { method: 'POST', body: formData });
            if (!replyRes.ok) {
                alert('Could not send — this ticket may no longer be assigned to an agent. Refreshing...');
                const res = await fetch(`{{ url('/chatbotMessaging') }}/${queueThreadTicketId}/thread`);
                const data = await res.json();
                applyThreadStatus(data.status);
                return;
            }
            input.value = '';

            const res = await fetch(`{{ url('/chatbotMessaging') }}/${queueThreadTicketId}/thread`);
            const data = await res.json();
            const newOnes = data.messages.filter(m => m.id > queueThreadLastId);
            if (newOnes.length) renderQueueMessages(newOnes, true);
        }

        function resolveQueueTicket() {
            if (!queueThreadTicketId || !confirm('Mark this conversation as resolved?')) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/chatbotMessaging') }}/${queueThreadTicketId}/resolve`;
            form.innerHTML = `<input type="hidden" name="_token" value="${CSRF_TOKEN}">`;
            document.body.appendChild(form);
            form.submit();
        }

        document.getElementById('qt-input')?.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); sendAgentReply(); }
        });

        window.addEventListener('click', function (event) {
            if (event.target === document.getElementById('queueThreadModal')) closeQueueThread();
        });
    </script>
</body>

</html>
