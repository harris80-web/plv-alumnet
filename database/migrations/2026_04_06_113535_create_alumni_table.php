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
            $table->year('alumnus_batch');
            $table->text('alumnus_skills')->nullable();
            $table->boolean('alumnus_is_public')->default(true);
            $table->string('alumnus_resume', 255)->nullable();
            $table->boolean('alumnus_change_password')->default(false);
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
