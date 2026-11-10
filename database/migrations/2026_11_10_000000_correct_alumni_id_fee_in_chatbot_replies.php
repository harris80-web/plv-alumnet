<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The seeded staff reply to "Is the alumni ID free?" said the first Alumni ID
 * is free. It isn't: the Alumni ID costs ₱1,200, and so does a replacement.
 * This corrects that reply in databases that were already seeded (the seed
 * catalog itself is fixed too). Only the exact old text is touched, so real
 * staff replies are never changed.
 */
return new class extends Migration
{
    private const OLD_REPLY = 'Hi! The first Alumni ID is free for all PLV graduates. Replacement IDs have a minimal fee payable at the Cashier.';

    private const NEW_REPLY = 'Hi! The Alumni ID is not free. It has a fee of ₱1,200, and replacement IDs cost the same ₱1,200. You can pay at the Cashier.';

    public function up(): void
    {
        DB::table('chat_ticket_messages')->where('message', self::OLD_REPLY)->update(['message' => self::NEW_REPLY]);
    }

    public function down(): void
    {
        DB::table('chat_ticket_messages')->where('message', self::NEW_REPLY)->update(['message' => self::OLD_REPLY]);
    }
};
