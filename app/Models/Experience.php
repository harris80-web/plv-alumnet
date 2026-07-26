<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    //
    protected $table = 'experiences';
    protected $primaryKey = 'experience_id';
 
    protected $fillable = [
        'alumnus_id',
        'experience_type',
        'industry_id',
        'experience_duration_months',
        'experience_job_title',
        'experience_job_description',
    ];
 
    protected $casts = [
        'experience_duration_months' => 'integer',
    ];
 
    public function alumnus()
    {
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'user_id');
    }
 
    public function industry()
    {
        return $this->belongsTo(Industry::class, 'industry_id', 'industry_id');
    }
}
