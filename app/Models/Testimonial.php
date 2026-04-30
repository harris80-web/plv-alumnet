<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    //
    protected $primaryKey = 'testimonial_id';

    protected $fillable = [
        'user_id',
        'testimonial_body',
        'testimonial_post',
    ];

    public function alumnus()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->belongsTo(Alumnus::class, 'user_id', 'user_id');
    }
}
