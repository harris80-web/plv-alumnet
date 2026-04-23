<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryRange extends Model
{
    //
    protected $primaryKey = 'salary_range_id';

    protected $fillable = [
        'salary_range_min',
        'salary_range_max'
    ];
}
