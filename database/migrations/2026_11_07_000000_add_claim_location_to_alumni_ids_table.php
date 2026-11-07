<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where an alumnus picks up their printed Alumni ID (e.g. "University
 * Registrar") — the ID's counterpart to alumni_yearbooks'
 * distribution_building / distribution_room_floor. Shown on the alumni
 * dashboard's Alumni ID status modal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_ids', function (Blueprint $table) {
            $table->string('claim_location', 255)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_ids', function (Blueprint $table) {
            $table->dropColumn('claim_location');
        });
    }
};
