<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherApplication extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'status',
        'certificate_title',
        'issuer',
        'issue_date',
        'expiry_date',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user who submitted the application
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin who reviewed the application
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
