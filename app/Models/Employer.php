<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'industry_id', 
        'employer_position', 
        'employer_company_name',
        'employer_company_logo',
        'mployer_year_established',
        'employer_website_url',
        'employer_company_size',
        'employer_id_picture',
        'employer_id_picture_selfie',
        'employer_company_id_picture',
        'employer_company_id_picture_selfie'
    ];

    public function user()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function industry()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Industry::class, 'industry_id', 'industry_id');
    }
}
