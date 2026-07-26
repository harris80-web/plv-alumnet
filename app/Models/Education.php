<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    //
    protected $table = 'education';
    protected $primaryKey = 'education_id';
 
    protected $fillable = [
        'user_id',
        'program_id',
        'section_id',
        'gwa',
        'start_year',
        'end_year',
    ];
 
    protected $casts = [
        'gwa' => 'decimal:2',
        'start_year' => 'integer',
        'end_year' => 'integer',
    ];
 
    public function alumnus()
    {
        return $this->belongsTo(Alumnus::class, 'user_id', 'user_id');
    }
 
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }
 
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }
}
