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
        Schema::create('employer_addresses', function (Blueprint $table) {
            $table->id('address_id');
            // An employer can save as many company addresses as they want
            // (e.g. a head office plus branches) — picked from a dropdown
            // when posting a job instead of retyping the address every time.
            $table->foreignId('employer_id')->constrained('employers', 'user_id')->onDelete('cascade');
            $table->string('address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employer_addresses');
    }
};
