<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Progress extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id', 'lesson_id',
        'is_completed', 'time_spent', 'completed_at', 'last_accessed_at'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function lesson() {
        return $this->belongsTo(Lesson::class);
    }

    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }
}
