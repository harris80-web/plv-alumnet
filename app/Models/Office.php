<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use SoftDeletes; // <-- Use this trait
    protected $primaryKey = 'user_id';
    public $incrementing = false; 
    protected $keyType = 'int';
    protected $dates = ['deleted_at'];
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
