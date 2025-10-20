<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'title', 'slug', 'description', 'thumbnail',
        'level', 'price', 'duration', 'status',
        'teacher_id', 'passing_score'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function teacher() {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function modules() {
        return $this->hasMany(Module::class);
    }

    public function enrollments() {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews() {
        return $this->hasMany(Review::class);
    }

    public function quizzes() {
        return $this->hasMany(Quiz::class);
    }
}
