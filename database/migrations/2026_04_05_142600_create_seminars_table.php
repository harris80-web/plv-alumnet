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
        Schema::create('seminars', function (Blueprint $table) {
            $table->id('seminar_id');
            $table->string('seminar_title', 150)->nullable();
            $table->dateTime('seminar_date')->nullable();
            $table->string('seminar_speaker', 100)->nullable();
            $table->string('seminar_topic', 150)->nullable();
            $table->string('seminar_location', 255)->nullable();
            $table->text('seminar_description')->nullable();
            $table->string('seminar_image', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seminars');
    }
};
