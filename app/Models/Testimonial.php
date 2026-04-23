<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    //
    protected $primaryKey = 'testimonial_id';

    protected $fillable = [
        'testimonial_name',
        'testimonial_program',
        'testimonial_batch',
        'testimonial_body'
    ];
}
