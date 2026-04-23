<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryMin extends Model
{
    //

    public function jobPostings()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasMany(JobPosting::class, 'salary_min_id', 'salary_range_min_id');
    }
}
