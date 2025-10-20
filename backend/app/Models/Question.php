<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'id', 'quiz_id', 'question_text',
        'question_type', 'options', 'correct_answer', 'points'
    ];

    protected $casts = ['options' => 'array'];

    public function quiz() {
        return $this->belongsTo(Quiz::class);
    }
}
