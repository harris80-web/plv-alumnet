<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Testimonial extends Model
{
    //
     use SoftDeletes;
    protected $primaryKey = 'testimonial_id';
    public $incrementing = true; 
    protected $keyType = 'int';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'testimonial_body',
        'testimonial_post',
    ];

    protected $casts = [
        'testimonial_post' => 'boolean', // whether it's approved/published
    ];

    public function alumnus()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->belongsTo(Alumnus::class, 'user_id', 'user_id');
    }

    public function scopePublished($query)
    {
        return $query->where('testimonial_post', true);
    }
}
