<?php

use App\Models\Office;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('office_address');
        });

        // Every admin account created before this feature existed had
        // unrestricted access under the old binary admin/super_admin check —
        // backfill them to the full permission set so nobody already
        // working gets silently locked out. New admins going forward are
        // explicitly granted permissions by the super admin when created.
        DB::table('offices')->whereNull('permissions')->update([
            'permissions' => json_encode(array_keys(Office::PERMISSIONS)),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
