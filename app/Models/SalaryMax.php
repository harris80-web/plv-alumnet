<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryMax extends Model
{
    //


    public function jobPostings()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasMany(JobPosting::class, 'salary_max_id', 'salary_range_max_id');
    }
}


