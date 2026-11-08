<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    //
    protected $table = 'certifications';
    protected $primaryKey = 'certification_id';
 
    protected $fillable = [
        'certification_type',
        'certification_name',
        'certification_from',
        'certification_date',
        'alumnus_id',
    ];
 
    protected $casts = [
        'certification_date' => 'date',
    ];
 
    /**
     * The text after the name on a resume line:
     * " — TESDA (Training), Jun 2023". Kept on one line so ResumeTextParser
     * can read each entry back from an exported PDF.
     */
    public function resumeDetailText(array $typeLabels): string
    {
        $type = $typeLabels[$this->certification_type] ?? ucfirst((string) $this->certification_type);

        return ($this->certification_from ? ' — ' . $this->certification_from : '')
            . ($type ? " ({$type})" : '')
            . ($this->certification_date ? ', ' . $this->certification_date->format('M Y') : '');
    }

    public function alumnus()
    {
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'user_id');
    }
}
