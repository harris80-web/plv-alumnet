<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Yearbook claiming is a time window, not a single moment —
 * distribution_scheduled_at holds the date and the window's start time,
 * this holds when it ends (e.g. 9:00 AM – 5:00 PM, office closing time).
 * Existing schedules get the office's 5:00 PM closing time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_yearbooks', function (Blueprint $table) {
            $table->time('distribution_end_time')->nullable()->after('distribution_scheduled_at');
        });

        DB::table('alumni_yearbooks')->whereNotNull('distribution_scheduled_at')->update(['distribution_end_time' => '17:00:00']);
    }

    public function down(): void
    {
        Schema::table('alumni_yearbooks', function (Blueprint $table) {
            $table->dropColumn('distribution_end_time');
        });
    }
};
