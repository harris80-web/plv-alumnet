<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One trained version of the resume line classifier.
 *
 * Weights live in the database rather than on disk because storage/app is
 * gitignored in this project; the committed baseline under resources/ml is
 * seeded into here on install.
 */
class ResumeParserModel extends Model
{
    protected $table = 'resume_parser_models';

    protected $primaryKey = 'resume_parser_model_id';

    protected $fillable = [
        'version',
        'label',
        'weights',
        'feature_count',
        'label_set',
        'hyperparameters',
        'training_example_counts',
        'metrics',
        'macro_f1',
        'field_f1',
        'trained_seconds',
        'activated_at',
        'rejected_reason',
    ];

    protected $casts = [
        'weights' => 'array',
        'label_set' => 'array',
        'hyperparameters' => 'array',
        'training_example_counts' => 'array',
        'metrics' => 'array',
        'macro_f1' => 'float',
        'field_f1' => 'float',
        'activated_at' => 'datetime',
    ];

    public function parses()
    {
        return $this->hasMany(ResumeParse::class, 'resume_parser_model_id', 'resume_parser_model_id');
    }

    public static function active(): ?self
    {
        return static::whereNotNull('activated_at')->orderByDesc('version')->first();
    }

    public static function nextVersion(): int
    {
        return (int) static::max('version') + 1;
    }

    /** Deactivates whatever was live and promotes this row in one step. */
    public function activate(): void
    {
        static::whereNotNull('activated_at')->update(['activated_at' => null]);

        $this->forceFill(['activated_at' => now(), 'rejected_reason' => null])->save();
    }
}
