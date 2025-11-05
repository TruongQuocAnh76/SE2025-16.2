<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'id', 'level', 'message', 'context',
        'user_id', 'ip_address', 'user_agent'
    ];

    protected $casts = ['context' => 'array'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function user() {
        return $this->belongsTo(User::class);
    }
}
