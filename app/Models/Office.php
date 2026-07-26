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
        'user_id',
        'office_address',
        'office_created_at',
        'office_birth_date',
        'office_last_log',
    ];

    protected $casts = [
        'office_created_at' => 'datetime',
        'office_birth_date' => 'date',
        'office_last_log' => 'datetime',
    ];

    public function user()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function chatTickets()
    {
        return $this->hasMany(ChatTicket::class, 'office_id', 'office_id');
    }
    
}
