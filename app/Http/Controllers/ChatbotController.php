<?php

namespace App\Http\Controllers;

use App\Models\ChatbotSetting;
use App\Models\ChatTicket;
use App\Models\ChatTicketMessage;
use App\Models\Faq;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\GeminiChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Backend for the floating chatbot widget (alumni + employer). One
 * ChatTicket per user's ongoing conversation — reused across widget
 * open/close until it's resolved, so closing the modal doesn't lose the
 * thread or abandon a pending live-agent escalation.
 */
class ChatbotController extends Controller
{
    private function authorizeWidgetUser(): void
    {
        abort_unless(in_array(Auth::user()->user_role, ['alumni', 'employer']), 403);
    }

    private function authorizeOwner(ChatTicket $ticket): void
    {
        abort_unless($ticket->user_id === Auth::id(), 403);
    }

    /** Opens the widget — resumes the caller's open ticket, or starts a fresh one with a greeting. */
    public function start()
    {
        $this->authorizeWidgetUser();

        $ticket = ChatTicket::where('user_id', Auth::id())->open()->latest('ticket_id')->first();

        if (!$ticket) {
            $ticket = ChatTicket::create(['user_id' => Auth::id(), 'status' => 'ai_active']);
            ChatTicketMessage::create([
                'ticket_id' => $ticket->ticket_id,
                'sender_type' => 'ai',
                'message' => "Hi! I'm the PLV-AlumNet assistant. Ask me anything about your account, events, or the job board — I'll connect you with our team if I can't help.",
            ]);
        }

        return response()->json([
            'ticketId' => $ticket->ticket_id,
            'status' => $ticket->status,
            'messages' => $ticket->messages()->get()->map->toChatArray(),
        ]);
    }

    public function send(Request $request, ChatTicket $ticket, GeminiChatbotService $gemini)
    {
        $this->authorizeWidgetUser();
        $this->authorizeOwner($ticket);
        abort_if($ticket->status === 'resolved', 409, 'This conversation has already been resolved.');

        $validated = $request->validate(['message' => ['required', 'string', 'max:1000']]);

        ChatTicketMessage::create([
            'ticket_id' => $ticket->ticket_id,
            'sender_type' => 'user',
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        if ($ticket->status === 'ai_active') {
            $this->attemptAiReply($ticket, $validated['message'], $gemini);
        }
        // waiting_agent / with_agent: no AI/escalation step — the message just
        // sits in the thread for the agent to see on their next queue poll.

        return response()->json([
            'status' => $ticket->fresh()->status,
            'messages' => $ticket->messages()->get()->map->toChatArray(),
        ]);
    }

    private function attemptAiReply(ChatTicket $ticket, string $question, GeminiChatbotService $gemini): void
    {
        $settings = ChatbotSetting::current();

        if (!$settings->ai_chatbot_enabled) {
            $this->escalateOrDecline($ticket, $settings);
            return;
        }

        // Checked before the AI call, not after — someone explicitly asking
        // for a person shouldn't wait on a Gemini round-trip (or burn through
        // the failed-attempts counter) just to be told what they already asked for.
        if ($this->requestsHumanAgent($question)) {
            $this->escalateOrDecline($ticket, $settings, acknowledged: true);
            return;
        }

        $role = Auth::user()->user_role;
        $faqs = ($role === 'employer' ? Faq::visibleToEmployer() : Faq::visibleToAlumni())
            ->get(['faq_question', 'faq_answer'])
            ->map->only(['faq_question', 'faq_answer'])
            ->all();

        $history = $ticket->messages()->latest('id')->limit(6)->get()->reverse()
            ->map(fn (ChatTicketMessage $m) => ['role' => $m->sender_type, 'text' => $m->message])
            ->values()->all();

        try {
            $result = $gemini->ask($question, $faqs, $history);
        } catch (\Throwable $e) {
            // Previously swallowed silently — a real failure here (bad key,
            // quota, timeout, malformed response) left zero trace to debug
            // from later. Still degrades to "couldn't answer" either way.
            Log::warning('GeminiChatbotService::ask() failed, escalating instead', [
                'ticket_id' => $ticket->ticket_id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            $result = ['answered' => false, 'answer' => null];
        }

        if ($result['answered']) {
            ChatTicketMessage::create([
                'ticket_id' => $ticket->ticket_id,
                'sender_type' => 'ai',
                'message' => $result['answer'],
            ]);
            $ticket->update(['failed_attempts' => 0]);
            return;
        }

        $ticket->increment('failed_attempts');
        $ticket->refresh();

        if ($ticket->failed_attempts >= $settings->escalate_after_failed_attempts) {
            $this->escalateOrDecline($ticket, $settings);
        } else {
            ChatTicketMessage::create([
                'ticket_id' => $ticket->ticket_id,
                'sender_type' => 'ai',
                'message' => "I'm not quite sure about that — could you rephrase, or ask something else I might help with?",
            ]);
        }
    }

    private function escalateOrDecline(ChatTicket $ticket, ChatbotSetting $settings, bool $acknowledged = false): void
    {
        if ($settings->live_agent_escalation_enabled) {
            ChatTicketMessage::create([
                'ticket_id' => $ticket->ticket_id,
                'sender_type' => 'ai',
                'message' => $acknowledged
                    ? 'Sure — connecting you with a member of our team now. Someone will be with you shortly.'
                    : "I'm not able to answer that — connecting you with a member of our team. Someone will be with you shortly.",
            ]);
            $ticket->escalate();

            if ($settings->live_agent_notification) {
                $this->notifyAgentsOfEscalation($ticket);
            }
        } else {
            ChatTicketMessage::create([
                'ticket_id' => $ticket->ticket_id,
                'sender_type' => 'ai',
                'message' => $acknowledged
                    ? "I'd love to connect you with our team, but live agent support isn't available right now. Please try again later."
                    : "I'm sorry, I don't have an answer for that right now. Please try the FAQ page or check back later.",
            ]);
        }
    }

    /**
     * Gated on ChatbotSetting::live_agent_notification — the setting already
     * existed (and was already validated in ChatTicketController::updateSettings())
     * but nothing ever read it until now. Notifies the same pool ChatTicketController
     * treats as available agents (see $agentsOnline there): admin + super_admin.
     */
    private function notifyAgentsOfEscalation(ChatTicket $ticket): void
    {
        $recipientIds = User::whereIn('user_role', ['admin', 'super_admin'])->pluck('user_id');
        if ($recipientIds->isEmpty()) {
            return;
        }

        $requester = $ticket->user;
        $requesterName = $requester ? "{$requester->user_first_name} {$requester->user_last_name}" : 'A user';
        $now = now();
        $rows = $recipientIds->map(fn ($userId) => [
            'user_id' => $userId,
            'type' => 'live_agent_escalation',
            'title' => 'Live agent requested',
            'body' => "{$requesterName} needs help in the chatbot and is waiting for a live agent.",
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        UserNotification::insert($rows);
    }

    /**
     * Fast, deterministic phrase match — not an AI call — so "can I talk to
     * a human" escalates instantly instead of waiting on Gemini's judgment
     * (and instead of silently counting as just one more failed attempt
     * toward the usual escalation threshold).
     */
    private function requestsHumanAgent(string $message): bool
    {
        $patterns = [
            '\bagents?\b',
            '\bhumans?\b',
            '\brepresentatives?\b',
            'real person',
            'actual person',
            'live person',
            'customer service',
            'talk to (a |an )?(someone|staff)',
            'speak (to|with) (a |an )?(someone|staff)',
            'connect me (to|with)',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $message)) {
                return true;
            }
        }

        return false;
    }

    /** Polled by the widget while waiting_agent/with_agent, to pick up agent replies without a page reload. */
    public function poll(Request $request, ChatTicket $ticket)
    {
        $this->authorizeWidgetUser();
        $this->authorizeOwner($ticket);

        $afterId = (int) $request->query('after', 0);

        return response()->json([
            'status' => $ticket->status,
            'messages' => $ticket->messages()->where('id', '>', $afterId)->get()->map->toChatArray(),
        ]);
    }
}
