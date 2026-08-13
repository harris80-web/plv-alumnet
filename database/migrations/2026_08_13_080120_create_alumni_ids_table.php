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
        Schema::create('alumni_ids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumnus_id')->unique()->constrained('alumni', 'user_id')->onDelete('cascade');
            $table->enum('status', ['pending', 'under_review', 'ready_to_claim', 'claimed'])->default('pending');
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_ids');
    }
};
