<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    //
    protected $primaryKey = 'industry_id';

    protected $fillable = [
        'industry_name'
    ];

    public function employer()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasMany(Employer::class, 'industry_id', 'industry_id');
    }
}
