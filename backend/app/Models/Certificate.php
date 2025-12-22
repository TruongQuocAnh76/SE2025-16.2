<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'id', 'certificate_number', 'student_id', 'user_id', 'course_id',
        'final_score', 'pdf_url', 'pdf_hash', 'status',
        'issued_at', 'revoked_at', 'revocation_reason',
        'blockchain_status', 'blockchain_transaction_hash', 'blockchain_block_number', 
        'blockchain_confirmations', 'blockchain_gas_used', 'blockchain_error', 
        'blockchain_issued_at', 'student_wallet_address', 'certificate_data'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'certificate_data' => 'array',
        'issued_at' => 'datetime',
        'revoked_at' => 'datetime',
        'blockchain_issued_at' => 'datetime',
    ];

    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function blockchainTransaction()
    {
        return $this->hasOne(BlockchainTransaction::class, 'certificate_id', 'id');
    }
}
