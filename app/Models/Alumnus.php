<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumnus extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'program_id',
        'section_id',
        'alumnus_employment_status',
        'alumnus_batch',
        'alumnus_skills',
        'alumnus_is_public',
        'alumnus_resume',
    ];

    protected $casts = [
        'alumnus_resume' => 'array',
    ];
    
    public function user()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function program()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function section()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }
}
