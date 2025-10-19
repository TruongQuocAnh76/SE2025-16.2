<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'id', 'title', 'description', 'quiz_type',
        'passing_score', 'time_limit', 'max_attempts',
        'order_index', 'is_active', 'course_id'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function questions() {
        return $this->hasMany(Question::class);
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }
}
