<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One saved company address for an employer (e.g. head office, a branch) — picked from a dropdown when posting a job. */
class EmployerAddress extends Model
{
    protected $primaryKey = 'address_id';

    protected $fillable = [
        'employer_id',
        'address',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class, 'employer_id', 'user_id');
    }
}
