<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    //
    protected $primaryKey = 'section_id';

    protected $fillable = [
        'section_name'
    ];

    public function alumni()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasMany(Alumnus::class, 'section_id', 'section_id');
    }
}
