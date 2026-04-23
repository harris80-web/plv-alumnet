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
        Schema::create('employers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->primary('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreignId('industry_id')->constrained('industries', 'industry_id')->nullable();
            $table->string('employer_position', 255)->nullable();
            $table->string('employer_company_name', 255);
            $table->string('employer_company_logo', 255)->nullable();
            $table->year('employer_year_established')->nullable();
            $table->string('employer_website_url', 255)->nullable();
            $table->unsignedBigInteger('employer_company_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
};
