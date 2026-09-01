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

    /**
     * RBAC feature keys an `admin` account can be granted, matching the
     * staff sidebar sections one-to-one. Dashboard isn't listed — it's the
     * post-login landing page and stays accessible to every admin.
     * super_admin bypasses this entirely (see User::canAccessAdminFeature).
     */
    const PERMISSIONS = [
        'user_management' => 'User Management',
        'job_management' => 'Job Placement Management',
        'alumni_id_management' => 'Alumni ID & Yearbook Management',
        'notices' => 'Notices & Events Management',
        'messaging' => 'Chatbot & Messaging Management',
        'testimonials' => 'Testimonial Management',
        'faqs' => 'Manage FAQs',
    ];

    protected $fillable = [
        'user_id',
        'office_address',
        'office_created_at',
        'office_birth_date',
        'office_last_log',
        'permissions',
    ];

    protected $casts = [
        'office_created_at' => 'datetime',
        'office_birth_date' => 'date',
        'office_last_log' => 'datetime',
        'permissions' => 'array',
    ];

    public function hasPermission(string $feature): bool
    {
        return in_array($feature, $this->permissions ?? [], true);
    }

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
