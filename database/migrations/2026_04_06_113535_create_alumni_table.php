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
        Schema::create('alumni', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->primary('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs', 'program_id');
            $table->foreignId('section_id')->constrained('sections', 'section_id');

            $table->boolean('alumnus_employment_status')->default(false);
            $table->text('resume_summary')->nullable()->after('alumnus_employment_status');
            $table->string('linkedin_url')->nullable()->after('resume_summary');
            $table->unsignedTinyInteger('resume_completeness')->default(0);
            
            $table->year('alumnus_batch');
            $table->boolean('alumnus_is_public')->default(true);
            $table->boolean('alumnus_change_password')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
