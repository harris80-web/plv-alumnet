<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A rating/review targets whoever posted the job. That used to be only an
 * employer, but admin/super_admin postings can be rated too and those
 * accounts have no employers row, so the FK moves to users. Stored values are
 * unchanged: employers.user_id is the same id as users.user_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_reviews', function (Blueprint $table) {
            $table->dropForeign(['employer_id']);
        });
        Schema::table('employer_reviews', function (Blueprint $table) {
            $table->foreign('employer_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employer_reviews', function (Blueprint $table) {
            $table->dropForeign(['employer_id']);
        });
        Schema::table('employer_reviews', function (Blueprint $table) {
            $table->foreign('employer_id')->references('user_id')->on('employers')->cascadeOnDelete();
        });
    }
};
