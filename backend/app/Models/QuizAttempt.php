<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'id', 'student_id', 'quiz_id', 'attempt_number',
        'score', 'is_passed', 'started_at', 'submitted_at', 'time_spent'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function quiz() {
        return $this->belongsTo(Quiz::class);
    }

    public function answers() {
        return $this->hasMany(Answer::class, 'attempt_id');
    }
}
