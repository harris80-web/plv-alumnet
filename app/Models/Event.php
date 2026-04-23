<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $primaryKey = 'event_id';

    protected $fillable = [
        'event_title', 
        'event_date', 
        'event_location	',
        'event_description',
        'event_image'
    ];

    
}
