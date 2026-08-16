<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('chat_tickets', 'ticket_id')->onDelete('cascade');
            $table->enum('sender_type', ['user', 'ai', 'agent']);
            // Null for 'ai' — the assistant isn't a row in users.
            $table->foreignId('sender_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_ticket_messages');
    }
};
