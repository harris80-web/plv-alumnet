<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    //
    protected $primaryKey = 'faq_id';

    protected $fillable = [
        'faq_question', 
        'faq_answer'
    ];
}
