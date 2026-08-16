<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use SoftDeletes;
    //
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $dates = ['deleted_at'];

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
        'user_role',
        'user_muted',
        'must_change_password',
    ];

    protected $casts = [
        'user_muted' => 'boolean',
        'must_change_password' => 'boolean',
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

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', 'user_id');
    }
 
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id', 'user_id');
    }
 
    public function chatTickets()
    {
        return $this->hasMany(ChatTicket::class, 'user_id', 'user_id');
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class, 'user_id', 'user_id');
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

    public function isSuperAdmin(): bool
    {
        return $this->user_role === 'super_admin';
    }
 
    public function isAdmin(): bool
    {
        return $this->user_role === 'admin';
    }
 
    public function isAlumni(): bool
    {
        return $this->user_role === 'alumni';
    }
 
    public function isEmployer(): bool
    {
        return $this->user_role === 'employer';
    }

    public function isMuted(): bool
    {
        return (bool) $this->user_muted;
    }
}
