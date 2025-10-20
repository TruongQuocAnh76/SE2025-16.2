<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $fillable = [
        'student_id', 'lesson_id',
        'is_completed', 'time_spent', 'completed_at', 'last_accessed_at'
    ];

    public function lesson() {
        return $this->belongsTo(Lesson::class);
    }

    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }
}
