<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One labelled training sequence: the lines of a resume plus the label of each.
 *
 * A null entry in gold_labels means "not known", not "none of the above". Those
 * positions are masked out of the weight update rather than guessed at, which
 * is what keeps a partially-corrected import from teaching the model nonsense.
 */
class ResumeParserExample extends Model
{
    protected $table = 'resume_parser_examples';

    protected $primaryKey = 'resume_parser_example_id';

    public const SOURCE_SYNTHETIC = 'synthetic';
    public const SOURCE_GEMINI = 'gemini';
    public const SOURCE_CORRECTION = 'correction';

    public const SPLIT_TRAIN = 'train';
    public const SPLIT_DEV = 'dev';
    public const SPLIT_TEST = 'test';

    protected $fillable = [
        'source',
        'split',
        'resume_parse_id',
        'profile_key',
        'variant_key',
        'extraction_mode',
        'lines',
        'gold_labels',
        'features',
        'features_fingerprint',
        'weight',
    ];

    protected $casts = [
        'lines' => 'array',
        'gold_labels' => 'array',
        'features' => 'array',
        'weight' => 'integer',
    ];

    public function parse()
    {
        return $this->belongsTo(ResumeParse::class, 'resume_parse_id', 'resume_parse_id');
    }

    public function scopeSplit($query, string $split)
    {
        return $query->where('split', $split);
    }

    public function scopeFromSources($query, array $sources)
    {
        return $query->whereIn('source', $sources);
    }

    /** Cached features are only reusable while the featurizer config is unchanged. */
    public function hasUsableFeatures(string $fingerprint): bool
    {
        return $this->features !== null && $this->features_fingerprint === $fingerprint;
    }

    public function labelledPositionCount(): int
    {
        return count(array_filter($this->gold_labels ?? [], fn ($label) => $label !== null));
    }
}
