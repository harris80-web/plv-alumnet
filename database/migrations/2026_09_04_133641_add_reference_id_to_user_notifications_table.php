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
        Schema::table('user_notifications', function (Blueprint $table) {
            // Id of whatever record this notification is about (a notice,
            // job posting, application, etc.) — which table depends on
            // `type`, so this deliberately isn't a foreign key. Lets
            // targetUrl() deep-link to the specific thing that triggered
            // the notification instead of just its general list page.
            $table->unsignedBigInteger('reference_id')->nullable()->after('body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropColumn('reference_id');
        });
    }
};
