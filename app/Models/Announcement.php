<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $primaryKey = 'announcement_id';

    protected $fillable = [
        'announcement_title', 
        'announcement_date', 
        'announcement_description',
        'announcement_image'
    ];
}
