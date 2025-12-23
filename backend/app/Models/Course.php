<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    /**
     * Tính giá sau giảm giá dựa trên phần trăm discount (0-100)
     * Nếu discount null hoặc <=0 thì trả về giá gốc
     */
    public function getDiscountedPrice()
    {
        if (isset($this->discount) && $this->discount > 0 && $this->discount < 100) {
            return round($this->price * (1 - $this->discount / 100), 2);
        }
        return $this->price;
    }
    use HasFactory;

    protected $fillable = [
        'id', 
        'title', 
        'slug', 
        'description', 
        'thumbnail',
        'level', 
        'price', 
        'duration', 
        'status',
        'teacher_id', 
        'passing_score',
        'long_description',
        'category',
        'language',
        'discount',
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
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'course_tag', 'course_id', 'tag_id');
    }
}
