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
        Schema::table('experiences', function (Blueprint $table) {
            // Item 21 — replaces the single duration_months number input in
            // both resume modals with a real start/end date range.
            // experience_end_date null (with a start_date set) means
            // "currently ongoing". Existing rows are left with both null —
            // no fabricated dates for historical entries that only ever
            // recorded a duration; they keep displaying via
            // experience_duration_months exactly as before.
            $table->date('experience_start_date')->nullable()->after('experience_type');
            $table->date('experience_end_date')->nullable()->after('experience_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn(['experience_start_date', 'experience_end_date']);
        });
    }
};
