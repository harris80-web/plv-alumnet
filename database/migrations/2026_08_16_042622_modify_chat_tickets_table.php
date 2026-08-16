<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ChatTicket previously modeled a single Q&A pair (user_query/office_response)
     * with a required office_id — unusable for a real chatbot session, which
     * starts AI-only (no agent yet) and holds a back-and-forth thread (see the
     * new chat_ticket_messages table). Reshapes it into a session record:
     * status progresses ai_active -> waiting_agent -> with_agent -> resolved,
     * office_id is nullable until an agent actually claims it.
     */
    public function up(): void
    {
        Schema::table('chat_tickets', function (Blueprint $table) {
            $table->dropColumn(['user_query', 'office_response', 'ticket_status']);
            // Dropped and re-added nullable below — no ->change() since
            // doctrine/dbal isn't installed, and this table has never been
            // used (ChatTicketController was an empty stub), so there's no
            // data to lose.
            $table->dropConstrainedForeignId('office_id');
        });

        Schema::table('chat_tickets', function (Blueprint $table) {
            $table->foreignId('office_id')->nullable()->after('user_id')->constrained('offices', 'office_id')->nullOnDelete();
            $table->string('status', 20)->default('ai_active')->after('office_id');
            $table->unsignedTinyInteger('failed_attempts')->default(0)->after('status');
            $table->timestamp('escalated_at')->nullable()->after('failed_attempts');
            $table->timestamp('claimed_at')->nullable()->after('escalated_at');
            $table->timestamp('resolved_at')->nullable()->after('claimed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_tickets', function (Blueprint $table) {
            $table->dropColumn(['status', 'failed_attempts', 'escalated_at', 'claimed_at', 'resolved_at']);
            $table->dropConstrainedForeignId('office_id');
        });

        Schema::table('chat_tickets', function (Blueprint $table) {
            $table->foreignId('office_id')->after('user_id')->constrained('offices', 'office_id');
            $table->text('user_query')->default('');
            $table->text('office_response')->default('');
            $table->enum('ticket_status', ['open', 'pending', 'closed'])->default('open');
        });
    }
};
