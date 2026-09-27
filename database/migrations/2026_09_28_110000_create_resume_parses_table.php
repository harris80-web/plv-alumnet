<?php

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
        Schema::create('resume_parses', function (Blueprint $table) {
            $table->id('resume_parse_id');
            // alumni keys on user_id, not an id column of its own.
            $table->foreignId('alumnus_id')->constrained('alumni', 'user_id')->onDelete('cascade');
            // Which model produced this parse, so accuracy can be attributed
            // after a retrain. Nulled rather than cascaded: losing a model row
            // must not delete the parse history that justifies it.
            $table->foreignId('resume_parser_model_id')->nullable()
                ->constrained('resume_parser_models', 'resume_parser_model_id')->nullOnDelete();

            $table->string('source_filename')->nullable();
            $table->unsignedSmallInteger('source_page_count')->nullable();
            $table->string('extraction_mode', 20)->default('layout');

            // Personal data: holds the alumnus's name, email, phone and address
            // as they appear in the PDF. Pruned once an example has been
            // extracted or after the retention window, whichever comes first.
            $table->longText('raw_text')->nullable();

            // All 'array' casts. lines/predicted_labels/predicted_margins are
            // parallel arrays indexed by line position.
            $table->longText('lines');
            $table->longText('predicted_labels');
            $table->longText('predicted_margins');
            $table->longText('predicted_payload');
            // field path => contributing line indexes, e.g.
            // {"experiences.0.job_description": [12,13,14]}. This is what makes
            // a correction attributable to specific lines without guessing.
            $table->longText('provenance');

            $table->longText('corrected_payload')->nullable();
            $table->timestamp('corrected_at')->nullable();
            // The builder wizard saves a draft immediately after import, before
            // the alumnus has reviewed anything. Such a save is not a
            // correction and must never become training data.
            $table->boolean('is_unreviewed')->default(false);

            // Line labels derived from the correction; a null entry means
            // "unknown", which is excluded from training rather than guessed.
            $table->longText('gold_labels')->nullable();
            $table->decimal('label_coverage', 5, 4)->nullable();
            $table->timestamp('example_extracted_at')->nullable();

            $table->timestamps();

            $table->index(['alumnus_id', 'created_at']);
            $table->index('corrected_at');
        });

        // doctrine/dbal is not installed, so enum columns are declared in raw
        // SQL the same way the other migrations in this project do it.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE resume_parses MODIFY extraction_mode ENUM('layout','text_only') NOT NULL DEFAULT 'layout'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_parses');
    }
};
