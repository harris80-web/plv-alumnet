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
        Schema::create('resume_parser_models', function (Blueprint $table) {
            $table->id('resume_parser_model_id');
            // Monotonic and human-facing: what resume:parser-model list shows
            // and what an activate/rollback is given.
            $table->unsignedInteger('version')->unique();
            $table->string('label', 120)->nullable();

            // longText + an 'array' cast rather than ->json(): these blobs are
            // always written and read whole, never queried inside, and this
            // avoids MySQL JSON-column differences across XAMPP versions.
            // Shape: {"featureId*labelCount+labelId": weight, ...} after pruning.
            $table->longText('weights');
            $table->unsignedInteger('feature_count');
            // The ordered label set the weights were trained against. A model
            // whose label set no longer matches the code must not be loaded.
            $table->longText('label_set');
            $table->longText('hyperparameters');
            $table->longText('training_example_counts');

            $table->longText('metrics')->nullable();
            // Denormalised out of metrics purely so the activation gate can
            // compare a candidate against the live model without decoding.
            $table->decimal('macro_f1', 6, 4)->nullable();
            $table->decimal('field_f1', 6, 4)->nullable();
            $table->unsignedInteger('trained_seconds')->nullable();

            // Exactly one row is expected to have this set; that row is live.
            $table->timestamp('activated_at')->nullable();
            // Set when the gate refused to activate a retrain, so a silent
            // regression leaves an explanation behind.
            $table->string('rejected_reason')->nullable();

            $table->timestamps();

            $table->index('activated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_parser_models');
    }
};
