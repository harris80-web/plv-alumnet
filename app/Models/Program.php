<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    //
    protected $primaryKey = 'program_id';

    protected $fillable = [
        'program_name'
    ];

    public function alumni()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasMany(Alumnus::class, 'program_id', 'program_id');
    }

    public function jobPostings()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasMany(JobPosting::class, 'program_id', 'program_id');
    }
}
