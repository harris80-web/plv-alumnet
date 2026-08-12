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
        Schema::table('alumni', function (Blueprint $table) {
            $table->string('alumnus_gender')->nullable()->after('section_id');
            $table->string('alumnus_workplace')->nullable()->after('industry_id');
            $table->boolean('alumnus_workplace_undisclosed')->default(false)->after('alumnus_workplace');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn(['alumnus_gender', 'alumnus_workplace', 'alumnus_workplace_undisclosed']);
        });
    }
};
