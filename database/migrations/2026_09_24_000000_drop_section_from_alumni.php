<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section is being removed from the system entirely — CSV bulk-add required
 * an exact, case-sensitive match against sections.section_name with no way
 * to see or manage what names existed (SectionController was empty
 * scaffolding, never wired to any UI), so it rejected almost every row.
 * Unlike college/program (validated with a case-insensitive, name-or-code
 * lookup) there's no reasonable way to make a free-text CSV column this
 * fragile safe without a management screen nobody asked for; removing the
 * concept is the fix, matching AlumnusController/UserController's dropped
 * references to alumni.section_id and Alumnus::section().
 *
 * Raw SQL, not Schema::table()->dropForeign(), to match this project's
 * existing convention for FK/column changes (doctrine/dbal isn't
 * installed, which Schema::dropColumn() needs to inspect an existing
 * column before altering it).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('alumni', 'section_id')) {
            DB::statement('ALTER TABLE alumni DROP FOREIGN KEY alumni_section_id_foreign, DROP COLUMN section_id');
        }

        Schema::dropIfExists('sections');
    }

    public function down(): void
    {
        Schema::create('sections', function ($table) {
            $table->id('section_id');
            $table->string('section_name');
            $table->timestamps();
        });

        if (! Schema::hasColumn('alumni', 'section_id')) {
            DB::statement('ALTER TABLE alumni ADD COLUMN section_id BIGINT UNSIGNED NULL AFTER program_id');
            DB::statement('ALTER TABLE alumni ADD CONSTRAINT alumni_section_id_foreign FOREIGN KEY (section_id) REFERENCES sections(section_id)');
        }
    }
};
