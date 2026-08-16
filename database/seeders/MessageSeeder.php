<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Generic-but-plausible opening lines — combined with REPLIES below to
     * build back-and-forth exchanges for the bulk pass without needing a
     * handwritten script for every one of the ~70 seeded alumni.
     */
    private const OPENERS = [
        "Hey! Long time no talk, how's everything going?",
        'Hi! Are you planning to attend the alumni homecoming this year?',
        'Congrats on the new job, saw your update on the platform!',
        "Hey, do you still have the notes from our batch's seminar?",
        'Hi! Saw your profile on the directory, would love to reconnect.',
        'Hey, any tips for someone applying to jobs in your field?',
        'Hi! Are you free to catch up sometime this month?',
        "Hey, I'm putting together a small batch reunion, you in?",
        'Hi! Just wanted to say congrats on finishing your certification.',
        "Hey, do you know if the yearbook's ready for pickup yet?",
        "Hi, saw you're also job hunting — want to compare notes?",
        'Hey! Are you going to the job fair next month?',
        'Hey, it has been a while! How is work treating you?',
        'Hi! Do you remember which room the registrar moved to?',
    ];

    private const REPLIES = [
        'Doing great, thanks for asking! You?',
        "Yes, I'm planning to go! See you there.",
        'Thank you so much, means a lot!',
        'I think I still have them somewhere, let me check and send them over.',
        'Same here, would be great to catch up!',
        'Sure, happy to share what I know.',
        'That sounds good, let me know the details.',
        'Count me in!',
        'Thank you! It was a lot of work but worth it.',
        "Not yet, I'll check the yearbook page and let you know.",
        'Definitely, always good to compare notes.',
        "I'll be there, wouldn't miss it.",
        'Happy to help however I can.',
        "It's going well, keeping busy! How about you?",
    ];

    /** Reused from existing seeders so no new files are needed on disk. */
    private const ATTACHMENTS = [
        'jobImages/softdev.png',
        'jobImages/accounting.jpg',
        'jobImages/networkenginerr.png',
    ];

    /**
     * A few hand-written conversations for alumni@example.com (Ryza Ison,
     * the account used throughout manual/browser testing), plus a bulk pass
     * that spreads conversations across the FULL alumni pool — otherwise
     * the messaging sidebar only ever shows 3 chats no matter how many
     * alumni exist, while the "To:" search (which queries the alumni table
     * directly) shows all of them. Conversation coverage should scale with
     * the alumni count the same way the directory/ID/yearbook pages do.
     */
    public function run(): void
    {
        $ryza = User::where('user_email', 'alumni@example.com')->first();
        if (!$ryza) {
            return;
        }

        /** @var array<string, bool> pair keys already used, so the bulk pass never double-conversations two people */
        $seenPairs = [];

        $seenPairs[$this->pairKey($ryza->user_id, $this->userId('alumni2@example.com'))] = true;
        $seenPairs[$this->pairKey($ryza->user_id, $this->userId('alumni3@example.com'))] = true;
        $seenPairs[$this->pairKey($ryza->user_id, $this->userId('alumni5@example.com'))] = true;

        $this->seedConversation(
            $ryza,
            'alumni2@example.com', // Miguel Torres
            [
                ['from' => 'other', 'text' => "Hey Ryza! Saw you're also into full stack dev, how's the job hunt going?", 'minutesAgo' => 180, 'read' => true],
                ['from' => 'me', 'text' => 'Going well! Just applied to a few postings on the job board. You?', 'minutesAgo' => 175, 'read' => true],
                ['from' => 'other', 'text' => 'Same here. Let me know if you hear back from Tech Solutions Inc., I applied there too.', 'minutesAgo' => 170, 'read' => true],
                ['from' => 'me', 'text' => 'Will do! Good luck 🤞', 'minutesAgo' => 165, 'read' => true],
            ]
        );

        $this->seedConversation(
            $ryza,
            'alumni3@example.com', // Angela Cruz
            [
                ['from' => 'other', 'text' => 'Hi! Are you going to the Alumni Homecoming next month?', 'minutesAgo' => 60, 'read' => true],
                ['from' => 'me', 'text' => 'Yes! Already marked myself interested on the events page.', 'minutesAgo' => 55, 'read' => true],
                ['from' => 'other', 'text' => 'Nice, see you there then! Also — do you still have notes from the networking seminar?', 'minutesAgo' => 5, 'read' => false],
            ]
        );

        $this->seedConversation(
            $ryza,
            'alumni5@example.com', // Bianca Mendoza
            [
                ['from' => 'other', 'text' => "Here's the flyer for the guidance counseling workshop I mentioned.", 'minutesAgo' => 30, 'read' => false, 'attachment' => 'jobImages/softdev.png'],
            ]
        );

        $this->seedBulkConversations($seenPairs);
    }

    /**
     * Every alumnus (named + practice, ~70 total) gets a chance to be a
     * "starter" who opens 1-3 conversations with random other alumni they
     * haven't already messaged — so the conversation count grows with the
     * alumni pool instead of staying fixed at whatever was hand-written.
     */
    private function seedBulkConversations(array $seenPairs): void
    {
        $alumniIds = User::where('user_role', 'alumni')->pluck('user_id')->shuffle()->values();
        if ($alumniIds->count() < 2) {
            return;
        }

        foreach ($alumniIds as $starterId) {
            if (!fake()->boolean(65)) {
                continue;
            }

            $conversationsToStart = fake()->numberBetween(1, 3);

            for ($n = 0; $n < $conversationsToStart; $n++) {
                $partnerId = $alumniIds->random();
                $attempts = 0;
                while (
                    $attempts < 5 &&
                    ($partnerId === $starterId || isset($seenPairs[$this->pairKey($starterId, $partnerId)]))
                ) {
                    $partnerId = $alumniIds->random();
                    $attempts++;
                }
                if ($partnerId === $starterId || isset($seenPairs[$this->pairKey($starterId, $partnerId)])) {
                    continue; // couldn't find a free pair this round, skip rather than loop forever
                }

                $seenPairs[$this->pairKey($starterId, $partnerId)] = true;
                $this->seedRandomConversation($starterId, $partnerId);
            }
        }
    }

    private function seedRandomConversation(int $userAId, int $userBId): void
    {
        $conversation = Conversation::create([
            'conversation_user_a' => $userAId,
            'conversation_user_b' => $userBId,
        ]);

        $messageCount = fake()->numberBetween(2, 6);
        $startedAgo = fake()->numberBetween(10, 64800); // up to ~45 days ago, in minutes
        $withAttachment = fake()->boolean(10);
        $endsUnread = fake()->boolean(30);

        // Alternate who speaks first so it isn't always the same participant.
        $firstSender = fake()->boolean() ? $userAId : $userBId;
        $secondSender = $firstSender === $userAId ? $userBId : $userAId;

        $lastCreatedAt = null;

        for ($i = 0; $i < $messageCount; $i++) {
            $senderId = $i % 2 === 0 ? $firstSender : $secondSender;
            $receiverId = $senderId === $userAId ? $userBId : $userAId;
            $isLast = $i === $messageCount - 1;

            $text = $i === 0
                ? self::OPENERS[array_rand(self::OPENERS)]
                : self::REPLIES[array_rand(self::REPLIES)];

            $minutesAgo = (int) round($startedAgo * (1 - $i / max($messageCount - 1, 1)));
            $createdAt = Carbon::now()->subMinutes(max($minutesAgo, 0));

            $hasAttachment = $withAttachment && $isLast;
            $attachmentPath = $hasAttachment ? self::ATTACHMENTS[array_rand(self::ATTACHMENTS)] : null;

            $message = Message::create([
                'conversation_id' => $conversation->conversation_id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message_content' => $hasAttachment ? '' : $text,
                'message_read' => $isLast ? !$endsUnread : true,
                'attachment_path' => $attachmentPath,
                'attachment_original_name' => $attachmentPath ? basename($attachmentPath) : null,
                'attachment_mime_type' => $attachmentPath ? 'image/png' : null,
            ]);

            $message->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
            $lastCreatedAt = $createdAt;
        }

        $conversation->forceFill(['conversation_last_message_at' => $lastCreatedAt ?? now()])->save();
    }

    private function seedConversation(User $ryza, string $otherEmail, array $exchanges): void
    {
        $other = User::where('user_email', $otherEmail)->first();
        if (!$other) {
            return;
        }

        $conversation = Conversation::create([
            'conversation_user_a' => $ryza->user_id,
            'conversation_user_b' => $other->user_id,
        ]);

        foreach ($exchanges as $exchange) {
            $isFromRyza = $exchange['from'] === 'me';
            $sender = $isFromRyza ? $ryza : $other;
            $receiver = $isFromRyza ? $other : $ryza;
            $createdAt = Carbon::now()->subMinutes($exchange['minutesAgo']);

            $message = Message::create([
                'conversation_id' => $conversation->conversation_id,
                'sender_id' => $sender->user_id,
                'receiver_id' => $receiver->user_id,
                'message_content' => $exchange['text'],
                'message_read' => $exchange['read'],
                'attachment_path' => $exchange['attachment'] ?? null,
                'attachment_original_name' => isset($exchange['attachment']) ? basename($exchange['attachment']) : null,
                'attachment_mime_type' => isset($exchange['attachment']) ? 'image/png' : null,
            ]);

            // created_at/updated_at aren't mass-assignable — backdate them
            // directly so the sidebar's "most recent activity" ordering and
            // timestamps look realistic instead of all bunched at "now".
            $message->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }

        $conversation->forceFill(['conversation_last_message_at' => $createdAt ?? now()])->save();
    }

    private function userId(string $email): ?int
    {
        return User::where('user_email', $email)->value('user_id');
    }

    private function pairKey(?int $a, ?int $b): string
    {
        $pair = [$a, $b];
        sort($pair);
        return implode('-', $pair);
    }
}
