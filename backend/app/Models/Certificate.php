<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'id', 'certificate_number', 'student_id', 'course_id',
        'final_score', 'pdf_url', 'pdf_hash', 'status',
        'issued_at', 'revoked_at', 'revocation_reason'
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
