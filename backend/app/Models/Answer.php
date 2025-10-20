<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'id', 'attempt_id', 'question_id',
        'answer_text', 'is_correct', 'points_awarded'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function question() {
        return $this->belongsTo(Question::class);
    }

    public function attempt() {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }
}
