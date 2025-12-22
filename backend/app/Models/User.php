<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasUuids;

    protected $fillable = [
        'id', 'first_name', 'last_name', 'email', 'username', 'password',
        'auth_provider', 'role', 'bio', 'avatar', 'is_active',
        'google_id', 'facebook_id', 'membership_tier', 'membership_expires_at'
    ];

    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'membership_expires_at' => 'datetime',
        'has_password' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['has_password'];
    
    /**
     * Determine if the user has a password set.
     */
    public function getHasPasswordAttribute(): bool
    {
        return !empty($this->password);
    }

    public $incrementing = false;
    protected $keyType = 'string';

    // Relationships
    public function coursesTaught() {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function enrollments() {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function reviews() {
        return $this->hasMany(Review::class, 'student_id');
    }

    public function certificates() {
        return $this->hasMany(Certificate::class, 'student_id');
    }

    public function hasRole($role) {
        return $this->role === $role;
    }
    
    /**
     * Check if user has Premium membership
     */
    public function isPremium(): bool {
        return $this->membership_tier === 'PREMIUM' 
            && ($this->membership_expires_at === null || $this->membership_expires_at->isFuture());
    }
    
    /**
     * Check if user can enroll in courses for free (Premium members)
     */
    public function canEnrollForFree(): bool {
        return $this->isPremium();
    }
}

