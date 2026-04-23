<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    //
    protected $primaryKey = 'user_id';

    public function getAuthPassword()
    {
        return $this->user_password;
    }

    protected $fillable = [
        'user_email',
        'user_password',
        'user_first_name',
        'user_last_name',
        'user_middle_name',
        'user_suffix',
        'user_number',
        'user_profile_picture',
        'user_active',
        'user_role'
    ];

    public function alumnus()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasOne(Alumnus::class, 'user_id', 'user_id');
    }

    public function employer()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasOne(Employer::class, 'user_id', 'user_id');
    }

    public function office()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasOne(Office::class, 'user_id', 'user_id');
    }

    public function getEmailForPasswordReset()
    {
        return $this->user_email;
    }

    // Tells Laravel where to send the "Password Changed" notification
    public function routeNotificationForMail($notification)
    {
        return $this->user_email;
    }
}
