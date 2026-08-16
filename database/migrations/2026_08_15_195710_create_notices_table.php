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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['event', 'seminar', 'announcement']);
            $table->string('title', 255);
            $table->dateTime('event_datetime');
            // Nullable — announcements don't collect a location (see
            // NoticeController::validationRules(), required for the other
            // two categories only).
            $table->string('location', 255)->nullable();
            $table->text('description')->nullable();
            $table->enum('recipient', ['alumni', 'general', 'everyone'])->default('everyone');
            // Seminar-only fields, nullable for the other two categories.
            $table->string('speaker_name', 150)->nullable();
            $table->string('speaker_topic', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();
        });

        // Which alumni marked interest/attendance — powers the "Attendance"
        // column on the management table. No alumni-facing "I'm interested"
        // button exists yet to populate this organically; that's a natural
        // follow-up feature, not part of this admin CRUD page.
        Schema::create('notice_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained('notices', 'id')->onDelete('cascade');
            $table->foreignId('alumnus_id')->constrained('alumni', 'user_id')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['notice_id', 'alumnus_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notice_interests');
        Schema::dropIfExists('notices');
    }
};
