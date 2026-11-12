<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * alumnus_gender mirrors the registrar's records, which only ever record
 * male or female — "prefer_not_to_say" was never something an admin could
 * legitimately enter for someone else, so it's dropped from the enum
 * (added in 2026_09_18_190000_tighten_odd_column_types).
 *
 * Existing rows holding it become NULL rather than being guessed into a
 * gender: the column is already nullable, and gender breakdowns skip rows
 * without one instead of showing them as a third category. NULL can't
 * otherwise occur — both entry paths (UserController::addAlumnus() and the
 * CSV import) require a gender.
 *
 * Raw ALTER rather than a schema builder call because doctrine/dbal isn't
 * installed, matching the other enum migrations in this project.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Must run before the ALTER: MySQL would otherwise coerce these rows
        // to an empty string when the value leaves the enum.
        DB::table('alumni')->where('alumnus_gender', 'prefer_not_to_say')->update(['alumnus_gender' => null]);

        DB::statement("ALTER TABLE alumni MODIFY alumnus_gender ENUM('male','female') NULL");
    }

    public function down(): void
    {
        // The rows that were reset can't be identified again, so they stay
        // NULL — only the accepted value set is restored.
        DB::statement("ALTER TABLE alumni MODIFY alumnus_gender ENUM('male','female','prefer_not_to_say') NULL");
    }
};
