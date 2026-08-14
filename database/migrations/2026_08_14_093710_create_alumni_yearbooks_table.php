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
        Schema::create('alumni_yearbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumnus_id')->unique()->constrained('alumni', 'user_id')->onDelete('cascade');
            $table->enum('distribution_status', ['pending', 'on_hand'])->default('pending');
            $table->dateTime('distribution_scheduled_at')->nullable();
            $table->string('distribution_building')->nullable();
            $table->string('distribution_room_floor')->nullable();
            $table->enum('claiming_status', ['pending', 'on_hand', 'ready_to_claim', 'claimed', 'not_yet_claimed'])->default('pending');
            $table->timestamp('status_updated_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_yearbooks');
    }
};
