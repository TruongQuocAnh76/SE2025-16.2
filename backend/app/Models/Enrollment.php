<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'student_id', 'course_id', 'status',
        'progress', 'completed_at'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }
}
