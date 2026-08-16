<?php

namespace Database\Seeders;

use App\Models\ChatbotSetting;
use App\Models\ChatTicket;
use App\Models\ChatTicketMessage;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageFlag;
use App\Models\Office;
use App\Models\User;
use App\Services\MessageAuditor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ChatbotSeeder extends Seeder
{
    /**
     * Practice data for the Chatbot & Messaging admin dashboard: a few chat
     * tickets spanning every stage of the escalation flow, and a couple of
     * genuinely flaggable alumni messages — run through the real
     * MessageAuditor (not hand-typed flag rows) so the Alumni Messaging tab
     * reflects what the live detector actually catches.
     */
    public function run(): void
    {
        ChatbotSetting::current();

        $ryza = User::where('user_email', 'alumni@example.com')->first();
        $miguel = User::where('user_email', 'alumni2@example.com')->first();
        $angela = User::where('user_email', 'alumni3@example.com')->first();
        $employer = User::where('user_email', 'employer@example.com')->first();
        $admin = User::where('user_email', 'admin@example.com')->first();

        if (!$ryza || !$admin) {
            return;
        }

        // ── ai_active: still chatting with the assistant ──
        $this->seedTicket($ryza, 'ai_active', [
            ['ai', "Hi! I'm the PLV-AlumNet assistant. Ask me anything about your account, events, or the job board."],
            ['user', 'When is the alumni homecoming?'],
            ['ai', "The Alumni Homecoming 2026 is coming up — check the Events page on your dashboard for the exact date and RSVP."],
        ], minutesAgo: 12);

        if ($miguel) {
            $this->seedTicket($miguel, 'ai_active', [
                ['ai', "Hi! I'm the PLV-AlumNet assistant. Ask me anything about your account, events, or the job board."],
                ['user', 'How do I update my resume?'],
            ], minutesAgo: 4);
        }

        // ── waiting_agent: escalated, nobody has claimed it yet ──
        if ($angela) {
            $this->seedTicket($angela, 'waiting_agent', [
                ['ai', "Hi! I'm the PLV-AlumNet assistant. Ask me anything about your account, events, or the job board."],
                ['user', 'My alumni ID application has been stuck on "under review" for three weeks, can someone check?'],
                ['ai', "I'm not able to look up individual application statuses — connecting you with a member of our team. Someone will be with you shortly."],
            ], minutesAgo: 25, escalatedMinutesAgo: 22);
        }

        if ($employer) {
            $this->seedTicket($employer, 'waiting_agent', [
                ['ai', "Hi! I'm the PLV-AlumNet assistant. Ask me anything about your account or job postings."],
                ['user', 'One of our job postings has been pending approval for over a week.'],
                ['ai', "I'm not able to answer that — connecting you with a member of our team. Someone will be with you shortly."],
            ], minutesAgo: 40, escalatedMinutesAgo: 35);
        }

        // ── with_agent: claimed and actively being handled ──
        $office = Office::firstOrCreate(['user_id' => $admin->user_id], ['office_address' => '123 Main St, City, Country']);
        $bianca = User::where('user_email', 'alumni5@example.com')->first();
        if ($bianca) {
            $ticket = $this->seedTicket($bianca, 'with_agent', [
                ['ai', "Hi! I'm the PLV-AlumNet assistant. Ask me anything about your account, events, or the job board."],
                ['user', "I can't upload my resume file, it keeps failing."],
                ['ai', "I'm not quite sure about that — could you rephrase, or ask something else I might help with?"],
                ['user', 'Still not working, can I talk to someone?'],
                ['ai', "I'm not able to answer that — connecting you with a member of our team. Someone will be with you shortly."],
            ], minutesAgo: 18, escalatedMinutesAgo: 15);
            $ticket->update(['office_id' => $office->office_id, 'claimed_at' => Carbon::now()->subMinutes(10)]);
            ChatTicketMessage::create([
                'ticket_id' => $ticket->ticket_id,
                'sender_type' => 'agent',
                'sender_id' => $admin->user_id,
                'message' => trim($admin->user_first_name . " from the PLV-AlumNet team has joined the chat. Could you tell me what file format you're uploading?"),
            ])->forceFill(['created_at' => Carbon::now()->subMinutes(9)])->save();
        }

        // ── resolved ──
        $carlo = User::where('user_email', 'alumni6@example.com')->first();
        if ($carlo) {
            $ticket = $this->seedTicket($carlo, 'resolved', [
                ['ai', "Hi! I'm the PLV-AlumNet assistant. Ask me anything about your account, events, or the job board."],
                ['user', 'How do I claim my yearbook?'],
                ['ai', 'Navigate to the ID/Yearbook Tracker section on your dashboard to check your claiming status and pickup window.'],
            ], minutesAgo: 180);
            $ticket->update(['resolved_at' => Carbon::now()->subMinutes(175)]);
        }

        // ── Alumni messaging auditing: real content run through the real auditor ──
        if ($miguel && $angela) {
            $conversation = Conversation::findOrCreateBetween($miguel->user_id, $angela->user_id);
            $auditor = app(MessageAuditor::class);

            $exchanges = [
                ['from' => $angela, 'to' => $miguel, 'text' => "Hey! Are you still coming to the batch meetup this weekend?", 'minutesAgo' => 50],
                ['from' => $miguel, 'to' => $angela, 'text' => "Yes! By the way, quick favor — can you send me your GCash number for the gift?", 'minutesAgo' => 45],
                ['from' => $angela, 'to' => $miguel, 'text' => "Sure, one sec.", 'minutesAgo' => 44],
                ['from' => $miguel, 'to' => $angela, 'text' => "Actually please just transfer P500 to GCash 09171234567 for the venue deposit.", 'minutesAgo' => 43],
            ];

            foreach ($exchanges as $exchange) {
                $createdAt = Carbon::now()->subMinutes($exchange['minutesAgo']);
                $message = Message::create([
                    'conversation_id' => $conversation->conversation_id,
                    'sender_id' => $exchange['from']->user_id,
                    'receiver_id' => $exchange['to']->user_id,
                    'message_content' => $exchange['text'],
                    'message_read' => true,
                ]);
                $message->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

                $reasons = $auditor->scan($exchange['text']);
                if (!empty($reasons)) {
                    MessageFlag::create([
                        'message_id' => $message->message_id,
                        'flag_reasons' => $reasons,
                        'status' => 'pending',
                    ])->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
                }
            }
            $conversation->forceFill(['conversation_last_message_at' => Carbon::now()->subMinutes(43)])->save();
        }
    }

    /** @param array<int, array{0: string, 1: string}> $turns [sender_type, message] pairs, oldest first. */
    private function seedTicket(User $user, string $status, array $turns, int $minutesAgo, ?int $escalatedMinutesAgo = null): ChatTicket
    {
        $ticket = ChatTicket::create(['user_id' => $user->user_id, 'status' => $status]);
        $ticket->forceFill(['created_at' => Carbon::now()->subMinutes($minutesAgo)])->save();

        if ($escalatedMinutesAgo !== null) {
            $ticket->update(['escalated_at' => Carbon::now()->subMinutes($escalatedMinutesAgo)]);
        }

        $step = max(1, intdiv($minutesAgo, max(count($turns), 1)));
        $elapsed = $minutesAgo;

        foreach ($turns as [$senderType, $text]) {
            $createdAt = Carbon::now()->subMinutes($elapsed);
            ChatTicketMessage::create([
                'ticket_id' => $ticket->ticket_id,
                'sender_type' => $senderType,
                'sender_id' => $senderType === 'user' ? $user->user_id : null,
                'message' => $text,
            ])->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
            $elapsed = max(0, $elapsed - $step);
        }

        return $ticket;
    }
}
