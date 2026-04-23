<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id('conversation_id');
            $table->foreignId('conversation_user_a')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('conversation_user_b')->constrained('users', 'user_id')->onDelete('cascade');
            $table->timestamp('conversation_last_message_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('conversation_created_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
