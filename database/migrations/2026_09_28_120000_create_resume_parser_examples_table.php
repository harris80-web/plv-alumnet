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
        Schema::create('resume_parser_examples', function (Blueprint $table) {
            $table->id('resume_parser_example_id');

            // synthetic  - rendered from known data, so labels are exact
            // gemini     - labelled offline by the teacher, developer-run only
            // correction - derived from a real alumnus editing an import
            $table->string('source', 20);
            $table->string('split', 10)->default('train');

            // Set for correction examples only. One example per parse: a user
            // who saves repeatedly should leave their final state, not three
            // conflicting rows, so writes upsert on this.
            $table->foreignId('resume_parse_id')->nullable()
                ->constrained('resume_parses', 'resume_parse_id')->nullOnDelete();

            // Splits are assigned by whole profile and whole layout, never per
            // line, so the same content cannot appear in both train and test
            // wearing a different layout.
            $table->string('profile_key', 64)->nullable();
            $table->string('variant_key', 64)->nullable();
            $table->string('extraction_mode', 20)->default('layout');

            $table->longText('lines');
            // Parallel to lines; a null entry is masked out of training.
            $table->longText('gold_labels');
            // Cached featurization so training never re-reads or re-renders a
            // PDF. Invalid as soon as the featurizer config changes, which is
            // what features_fingerprint detects.
            $table->longText('features')->nullable();
            $table->string('features_fingerprint', 40)->nullable();

            // Corrections are counted more than once during training so a few
            // dozen real examples are not drowned by ~900 synthetic ones.
            $table->unsignedSmallInteger('weight')->default(1);

            $table->timestamps();

            $table->index(['source', 'split']);
            $table->index('profile_key');
            $table->index('variant_key');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE resume_parser_examples MODIFY source ENUM('synthetic','gemini','correction') NOT NULL");
            DB::statement("ALTER TABLE resume_parser_examples MODIFY split ENUM('train','dev','test') NOT NULL DEFAULT 'train'");
            DB::statement("ALTER TABLE resume_parser_examples MODIFY extraction_mode ENUM('layout','text_only') NOT NULL DEFAULT 'layout'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_parser_examples');
    }
};
