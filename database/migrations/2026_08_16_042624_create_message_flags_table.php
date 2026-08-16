<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages', 'message_id')->onDelete('cascade');
            // JSON array of category strings, e.g. ["money_transfer","external_link"] — a
            // single message can trip more than one rule.
            $table->json('flag_reasons');
            $table->string('status', 20)->default('pending'); // pending, warned, muted, dismissed
            $table->foreignId('reviewed_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_flags');
    }
};
