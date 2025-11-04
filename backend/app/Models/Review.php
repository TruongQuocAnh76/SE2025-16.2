<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Thêm dòng này
use Illuminate\Database\Eloquent\Concerns\HasUuids; // 1. Thêm dòng này

class Review extends Model
{
    use HasFactory, HasUuids; // 2. Thêm HasFactory và HasUuids

    // 3. Thêm 2 dòng này để UUIDs hoạt động
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['student_id', 'course_id', 'rating', 'comment'];

    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }
}