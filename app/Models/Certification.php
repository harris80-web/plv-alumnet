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
 
    public function alumnus()
    {
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'user_id');
    }
}
