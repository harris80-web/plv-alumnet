<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One resume import: what was extracted, what the model predicted, and later
 * what the alumnus corrected it to.
 *
 * This row is what links an import to the save that follows it, which is the
 * only way a correction can be attributed back to specific lines.
 */
class ResumeParse extends Model
{
    protected $table = 'resume_parses';

    protected $primaryKey = 'resume_parse_id';

    protected $fillable = [
        'alumnus_id',
        'resume_parser_model_id',
        'source_filename',
        'source_page_count',
        'extraction_mode',
        'raw_text',
        'lines',
        'predicted_labels',
        'predicted_margins',
        'predicted_payload',
        'provenance',
        'corrected_payload',
        'corrected_at',
        'is_unreviewed',
        'gold_labels',
        'label_coverage',
        'example_extracted_at',
    ];

    protected $casts = [
        'lines' => 'array',
        'predicted_labels' => 'array',
        'predicted_margins' => 'array',
        'predicted_payload' => 'array',
        'provenance' => 'array',
        'corrected_payload' => 'array',
        'gold_labels' => 'array',
        'label_coverage' => 'float',
        'is_unreviewed' => 'boolean',
        'corrected_at' => 'datetime',
        'example_extracted_at' => 'datetime',
    ];

    public function alumnus()
    {
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'user_id');
    }

    public function parserModel()
    {
        return $this->belongsTo(ResumeParserModel::class, 'resume_parser_model_id', 'resume_parser_model_id');
    }

    public function example()
    {
        return $this->hasOne(ResumeParserExample::class, 'resume_parse_id', 'resume_parse_id');
    }

    /** A save that actually changed something, as opposed to the post-import auto-draft. */
    public function wasCorrected(): bool
    {
        return $this->corrected_at !== null && ! $this->is_unreviewed;
    }

    /**
     * Drops the stored resume text. The lines and labels keep all the training
     * and audit value; the raw blob is the only part that is personal data.
     */
    public function pruneRawText(): void
    {
        if ($this->raw_text === null) {
            return;
        }

        $this->forceFill(['raw_text' => null])->save();
    }
}
