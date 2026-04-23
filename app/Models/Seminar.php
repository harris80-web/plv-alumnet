<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    //
    protected $primaryKey = 'seminar_id';

    protected $fillable = [
        'seminar_title',
        'seminar_date',
        'seminar_speaker',
        'seminar_topic',
        'seminar_location',
        'seminar_description',
        'seminar_image'
    ];
}
