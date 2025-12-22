<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Module extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['id', 'title', 'order_index', 'course_id'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function lessons() {
        return $this->hasMany(Lesson::class);
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }
}
