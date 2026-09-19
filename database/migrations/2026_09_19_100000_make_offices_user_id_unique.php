<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * offices.user_id was a plain (non-unique) foreign key, so nothing stopped
 * two office rows for the same account even though every reader treats it
 * as one-to-one (User::office() is a hasOne, OfficeController uses
 * updateOrCreate, the chat code uses firstOrCreate(['user_id' => ...])).
 * Verified before writing this: 0 duplicate user_ids, 0 soft-deleted rows,
 * and no restore path that could recreate an office for a soft-deleted user.
 *
 * The unique index also serves the foreign key, and the engine drops the
 * old non-unique index it had created for that FK on its own — so there is
 * nothing to clean up by hand (an explicit dropIndex fails: it's already
 * gone). The hasIndex guard keeps this safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('offices', 'offices_user_id_unique')) {
            Schema::table('offices', function (Blueprint $table) {
                $table->unique('user_id');
            });
        }

        // Usually already removed by the engine when the unique index was
        // added (see above); present again after a down()/up() round trip.
        if (Schema::hasIndex('offices', 'offices_user_id_foreign')) {
            Schema::table('offices', function (Blueprint $table) {
                $table->dropIndex('offices_user_id_foreign');
            });
        }
    }

    public function down(): void
    {
        // The FK needs an index on user_id at all times, so add a plain one
        // before dropping the unique.
        if (! Schema::hasIndex('offices', 'offices_user_id_foreign')) {
            Schema::table('offices', function (Blueprint $table) {
                $table->index('user_id', 'offices_user_id_foreign');
            });
        }

        Schema::table('offices', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};
