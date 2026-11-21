<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'idempotency_key',
        'endpoint',
        'request_hash',
        'response_body',
        'response_status_code',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'response_body' => 'array',
        'response_status_code' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
