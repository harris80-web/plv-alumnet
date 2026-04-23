<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $primaryKey = 'user_id';
    //

    protected $fillable = [
        'office_address',
        'office_birth_date'
    ];

    public function user()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
