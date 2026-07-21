<?php

use GuzzleHttp\Psr7\DroppingStream;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Console\DownCommand;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id('experience_id');
            $table->foreignId('alumnus_id')->constrained('alumni', 'user_id')->onDelete('cascade');
            $table->enum('type', ['work', 'project'])->default('work');
            $table->foreignId('industry_id')->constrained('industries', 'industry_id');
            $table->integer('duration_months');
            $table->string('job_title');
            $table->text('job_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
