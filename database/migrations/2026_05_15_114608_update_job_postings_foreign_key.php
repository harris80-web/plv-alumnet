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
        Schema::table('job_postings', function (Blueprint $table) {
            //
            $table->dropForeign(['employer_id']);
            $table->renameColumn('employer_id', 'user_id');
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            //
            $table->dropForeign(['user_id']);
            $table->renameColumn('user_id', 'employer_id');
            $table->foreign('employer_id')->references('user_id')->on('employers')->onDelete('cascade');
        });
    }
};
