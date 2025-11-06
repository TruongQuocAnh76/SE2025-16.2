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
        'google_id', 'facebook_id'
    ];

    protected $hidden = ['password', 'remember_token'];

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
}
