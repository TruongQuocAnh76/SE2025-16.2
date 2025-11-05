<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainTransaction extends Model
{
    protected $fillable = [
        'id', 'transaction_hash', 'network',
        'certificate_hash', 'metadata', 'status',
        'certificate_id', 'confirmed_at'
    ];

    protected $casts = ['metadata' => 'array'];

    public $incrementing = false;
    protected $keyType = 'string';

    public function certificate() {
        return $this->belongsTo(Certificate::class);
    }
}
