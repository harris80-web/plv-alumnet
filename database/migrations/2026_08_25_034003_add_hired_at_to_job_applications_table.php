<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->timestamp('hired_at')->nullable()->after('application_status');
        });

        // Best-effort backfill for rows already hired before this column
        // existed — updated_at is the same proxy the Hires per Month chart
        // used previously, so this preserves its historical look exactly;
        // every hire from this point forward gets a real hired_at instead.
        DB::table('job_applications')
            ->where('application_status', 'hired')
            ->whereNull('hired_at')
            ->update(['hired_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('hired_at');
        });
    }
};
