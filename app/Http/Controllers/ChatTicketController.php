<?php

namespace App\Http\Controllers;

use App\Models\ChatbotSetting;
use App\Models\ChatTicket;
use App\Models\ChatTicketMessage;
use App\Models\Conversation;
use App\Models\Faq;
use App\Models\Message;
use App\Models\MessageFlag;
use App\Models\Office;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatTicketController extends Controller
{
    private function authorizeStaff(): void
    {
        abort_unless(in_array(Auth::user()->user_role, ['admin', 'super_admin']), 403);
    }

    /** One page, six tabs (see resources/views/superAdmin/chatbotMessaging.blade.php) — all data loads together. */
    public function index()
    {
        $this->authorizeStaff();

        $settings = ChatbotSetting::current();
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $activeAiSessions = ChatTicket::with(['user', 'latestMessage'])->where('status', 'ai_active')->latest('ticket_id')->get();
        $waitingTickets = ChatTicket::with(['user', 'latestMessage'])->waitingAgent()->oldest('escalated_at')->get();
        $withAgentTickets = ChatTicket::with(['user', 'office.user', 'latestMessage'])->where('status', 'with_agent')->latest('claimed_at')->get();
        $resolvedToday = ChatTicket::where('status', 'resolved')->whereDate('resolved_at', $today)->count();
        $resolvedTodayByAgent = ChatTicket::where('status', 'resolved')->whereDate('resolved_at', $today)->whereNotNull('office_id')->count();

        $totalTicketsEverAttempted = ChatTicket::where('failed_attempts', '>', 0)->orWhereNotNull('escalated_at')->orWhere('status', '!=', 'ai_active')->count();
        $everEscalated = ChatTicket::whereNotNull('escalated_at')->count();
        $allTicketsCount = ChatTicket::count();
        $aiResolutionRate = $allTicketsCount > 0 ? round((($allTicketsCount - $everEscalated) / $allTicketsCount) * 100) : 0;

        $avgWaitMinutes = (int) round(
            $waitingTickets->avg(fn (ChatTicket $t) => $t->escalated_at?->diffInMinutes(now()) ?? 0) ?? 0
        );

        $agentsOnline = User::whereIn('user_role', ['admin', 'super_admin'])->where('user_active', true)->count();

        $pendingFlags = MessageFlag::pending()->with(['message.sender', 'message.receiver'])->latest()->get();
        $allFlags = MessageFlag::with(['message.sender', 'message.receiver', 'reviewer'])->latest()->get();

        $overview = [
            'activeAiSessions' => $activeAiSessions->count(),
            'aiResolutionRate' => $aiResolutionRate,
            'pendingEscalations' => $waitingTickets->count(),
            'alumniMessagesToday' => Message::whereDate('created_at', $today)->count(),
            'detectedMessages' => $pendingFlags->count(),
        ];

        $aiChatbot = [
            'activeAiSessions' => $activeAiSessions->count(),
            'resolvedToday' => $resolvedToday,
            'escalatedToday' => ChatTicket::whereDate('escalated_at', $today)->count(),
            'resolutionRate' => $aiResolutionRate,
        ];

        $liveQueue = [
            'totalInQueue' => $waitingTickets->count(),
            'avgWaitMinutes' => $avgWaitMinutes,
            'resolvedTodayByAgents' => $resolvedTodayByAgent,
            'agentsOnline' => $agentsOnline,
        ];

        $alumniMessaging = [
            'threadsToday' => Conversation::whereDate('conversation_last_message_at', $today)->count(),
            'autoFlagged' => $pendingFlags->count(),
            'warningsThisWeek' => MessageFlag::where('status', 'warned')->where('reviewed_at', '>=', $weekStart)->count(),
            'mutedThisMonth' => User::where('user_muted', true)->where('updated_at', '>=', $monthStart)->count(),
        ];

        $totalSessionsThisWeek = ChatTicket::where('created_at', '>=', $weekStart)->count();
        $resolvedThisWeek = ChatTicket::where('status', 'resolved')->where('resolved_at', '>=', $weekStart)->get();
        $avgResolutionMinutes = (int) round(
            $resolvedThisWeek->avg(fn (ChatTicket $t) => $t->created_at->diffInMinutes($t->resolved_at)) ?? 0
        );

        $reports = [
            'totalSessionsThisWeek' => $totalSessionsThisWeek,
            'aiResolvedThisWeek' => ChatTicket::where('created_at', '>=', $weekStart)->whereNull('escalated_at')->count(),
            'escalatedThisWeek' => ChatTicket::where('created_at', '>=', $weekStart)->whereNotNull('escalated_at')->count(),
            'avgResolutionMinutes' => $avgResolutionMinutes,
            'sessionsByRole' => [
                'alumni' => ChatTicket::whereHas('user', fn ($q) => $q->where('user_role', 'alumni'))->where('created_at', '>=', $weekStart)->count(),
                'employer' => ChatTicket::whereHas('user', fn ($q) => $q->where('user_role', 'employer'))->where('created_at', '>=', $weekStart)->count(),
            ],
            'messageVolumePerDay' => collect(range(6, 0))->map(function ($daysAgo) {
                $date = Carbon::today()->subDays($daysAgo);
                return ['label' => $date->format('D'), 'count' => Message::whereDate('created_at', $date)->count()];
            })->values(),
            'flagBreakdown' => collect(MessageFlag::REASONS)->map(fn ($reason) => [
                'key' => $reason,
                'label' => MessageFlag::reasonLabels()[$reason],
                'count' => $allFlags->filter(fn ($f) => in_array($reason, $f->flag_reasons ?? []))->count(),
            ])->values(),
        ];

        $faqs = Faq::orderByDesc('created_at')->get();

        return view('superAdmin.chatbotMessaging', compact(
            'settings', 'activeAiSessions', 'waitingTickets', 'withAgentTickets',
            'overview', 'aiChatbot', 'liveQueue', 'alumniMessaging', 'reports',
            'pendingFlags', 'allFlags', 'faqs'
        ));
    }

    /**
     * Agent picks up a waiting ticket — called via AJAX (see claimTicket() in
     * the view), not a page reload, so the whole queue stays live for every
     * admin watching it. Locks the row before checking status so two admins
     * clicking "Assign to me" within the same instant can't both succeed —
     * the status check alone (no lock) would leave a race window where both
     * requests read "waiting_agent" before either had committed its update.
     */
    public function claim(ChatTicket $ticket)
    {
        $this->authorizeStaff();

        $claimed = DB::transaction(function () use ($ticket) {
            $locked = ChatTicket::where('ticket_id', $ticket->ticket_id)->lockForUpdate()->first();

            if (!$locked || $locked->status !== 'waiting_agent') {
                return null;
            }

            $office = Office::firstOrCreate(['user_id' => Auth::id()], ['office_address' => '']);
            $locked->claimBy($office);

            ChatTicketMessage::create([
                'ticket_id' => $locked->ticket_id,
                'sender_type' => 'agent',
                'sender_id' => Auth::id(),
                'message' => trim(Auth::user()->user_first_name . ' from the PLV-AlumNet team has joined the chat.'),
            ]);

            return $locked;
        });

        if (!$claimed) {
            return response()->json(['error' => 'This ticket was just claimed by someone else.'], 409);
        }

        return response()->json(['success' => true, 'ticketId' => $claimed->ticket_id]);
    }

    public function reply(Request $request, ChatTicket $ticket)
    {
        $this->authorizeStaff();
        abort_unless($ticket->status === 'with_agent', 409, 'This ticket is not currently assigned to an agent.');

        $validated = $request->validate(['message' => ['required', 'string', 'max:2000']]);

        ChatTicketMessage::create([
            'ticket_id' => $ticket->ticket_id,
            'sender_type' => 'agent',
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Reply sent.');
    }

    public function resolve(ChatTicket $ticket)
    {
        $this->authorizeStaff();
        $ticket->resolve();

        return response()->json(['success' => true]);
    }

    /** JSON — thread contents for the queue's "open thread" panel, and its own light poll. */
    public function threadJson(ChatTicket $ticket)
    {
        $this->authorizeStaff();
        $ticket->loadMissing('office.user');

        return response()->json([
            'ticketId' => $ticket->ticket_id,
            'status' => $ticket->status,
            'userName' => trim($ticket->user->user_first_name . ' ' . $ticket->user->user_last_name),
            'claimedByName' => $ticket->office?->user ? trim($ticket->office->user->user_first_name . ' ' . $ticket->office->user->user_last_name) : null,
            'claimedByMe' => $ticket->office?->user_id === Auth::id(),
            'messages' => $ticket->messages()->get()->map->toChatArray(),
        ]);
    }

    /**
     * Polled by every admin viewing the Live Agent Queue tab (see
     * pollQueue() in the view) so the list — including who has claimed
     * what — stays in sync across every open admin session, not just the
     * one that clicked. This is what actually prevents the "two admins
     * both accept the same ticket" confusion: a claim by one admin shows
     * up on everyone else's screen within a few seconds, without anyone
     * needing to refresh.
     */
    public function queueJson()
    {
        $this->authorizeStaff();

        $waitingTickets = ChatTicket::with(['user', 'latestMessage'])->waitingAgent()->oldest('escalated_at')->get();
        $withAgentTickets = ChatTicket::with(['user', 'office.user', 'latestMessage'])->where('status', 'with_agent')->latest('claimed_at')->get();

        $currentUserId = Auth::id();
        $mapTicket = function (ChatTicket $t) use ($currentUserId) {
            return [
                'ticketId' => $t->ticket_id,
                'name' => trim(($t->user->user_first_name ?? '') . ' ' . ($t->user->user_last_name ?? '')),
                'initial' => mb_substr($t->user->user_first_name ?? '?', 0, 1),
                'preview' => $t->latestMessage->message ?? '—',
                'status' => $t->status,
                'statusLabel' => $t->statusLabel(),
                'badgeClass' => $t->badgeClass(),
                'timeLabel' => optional($t->latestMessage?->created_at)->diffForHumans(),
                'claimedByName' => $t->office?->user ? trim($t->office->user->user_first_name . ' ' . $t->office->user->user_last_name) : null,
                'claimedAt' => optional($t->claimed_at)->diffForHumans(),
                'claimedByMe' => $t->office?->user_id === $currentUserId,
            ];
        };

        return response()->json([
            'tickets' => $waitingTickets->concat($withAgentTickets)->map($mapTicket)->values(),
        ]);
    }

    /** Action buttons on a flagged message: warn (logged), mute (real effect), or dismiss (false positive). */
    public function flagAction(Request $request, MessageFlag $messageFlag)
    {
        $this->authorizeStaff();

        $validated = $request->validate(['action' => ['required', 'in:warned,muted,dismissed']]);

        $messageFlag->update([
            'status' => $validated['action'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if (in_array($validated['action'], ['warned', 'muted'], true)) {
            $messageFlag->load('message.sender');
            $sender = $messageFlag->message->sender;
            $reasons = implode(', ', $messageFlag->reasonLabelsList());

            if ($validated['action'] === 'muted') {
                $sender?->update(['user_muted' => true]);
            }

            if ($sender) {
                UserNotification::create([
                    'user_id' => $sender->user_id,
                    'type' => $validated['action'] === 'muted' ? 'message_mute' : 'message_warning',
                    'title' => $validated['action'] === 'muted'
                        ? 'Your messaging access has been restricted'
                        : 'You received a warning about a message you sent',
                    'body' => $validated['action'] === 'muted'
                        ? "A message you sent was flagged for: {$reasons}. Your account has been muted from messaging — you can no longer send messages to other alumni. Contact the alumni office if you believe this is a mistake."
                        : "A message you sent was flagged for: {$reasons}. Please review our messaging guidelines — repeated flags may result in your account being muted.",
                ]);
            }
        }

        return back()->with('success', 'Flagged message updated.');
    }

    public function updateSettings(Request $request)
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'ai_chatbot_enabled' => ['sometimes', 'boolean'],
            'live_agent_escalation_enabled' => ['sometimes', 'boolean'],
            'job_board_queries_enabled' => ['sometimes', 'boolean'],
            'events_queries_enabled' => ['sometimes', 'boolean'],
            'general_faq_queries_enabled' => ['sometimes', 'boolean'],
            'career_advice_queries_enabled' => ['sometimes', 'boolean'],
            'escalate_after_failed_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'auto_assign_available_agent' => ['sometimes', 'boolean'],
            'allow_queue_estimation' => ['sometimes', 'boolean'],
            'live_agent_notification' => ['sometimes', 'boolean'],
            'chat_auditing_enabled' => ['sometimes', 'boolean'],
            'money_transfer_detection' => ['sometimes', 'boolean'],
            'personal_info_detection' => ['sometimes', 'boolean'],
            'external_link_detection' => ['sometimes', 'boolean'],
            'auto_notify_admin_on_flag' => ['sometimes', 'boolean'],
        ]);

        $booleanKeys = [
            'ai_chatbot_enabled', 'live_agent_escalation_enabled', 'job_board_queries_enabled',
            'events_queries_enabled', 'general_faq_queries_enabled', 'career_advice_queries_enabled',
            'auto_assign_available_agent', 'allow_queue_estimation', 'live_agent_notification',
            'chat_auditing_enabled', 'money_transfer_detection', 'personal_info_detection',
            'external_link_detection', 'auto_notify_admin_on_flag',
        ];
        foreach ($booleanKeys as $key) {
            $validated[$key] = $request->boolean($key);
        }

        ChatbotSetting::current()->update($validated);

        return back()->with('success', 'Chatbot settings updated.');
    }
}
