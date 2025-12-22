<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Lesson extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id', 'title', 'content_type', 'content_url', 'description', 'text_content',
        'duration', 'order_index', 'is_free', 'module_id'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function module() {
        return $this->belongsTo(Module::class);
    }

    public function progresses() {
        return $this->hasMany(Progress::class);
    }
}
