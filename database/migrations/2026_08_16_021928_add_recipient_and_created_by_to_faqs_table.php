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
        Schema::table('faqs', function (Blueprint $table) {
            $table->enum('faq_recipient', ['everyone', 'alumni', 'employer'])->default('everyone')->after('faq_answer');
            $table->foreignId('created_by')->nullable()->after('faq_recipient')->constrained('users', 'user_id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['faq_recipient', 'created_by']);
        });
    }
};
