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
        Schema::create('chat_tickets', function (Blueprint $table) {
            $table->id('ticket_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onUpdate('cascade');
            $table->foreignId('office_id')->constrained('offices', 'office_id')->onUpdate('cascade');
            $table->text('user_query');
            $table->text('office_response');
            $table->enum('ticket_status', ['open', 'pending', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_tickets');
    }
};
