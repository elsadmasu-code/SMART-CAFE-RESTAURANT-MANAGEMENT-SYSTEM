<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'branch_id',
        'action',
        'entity_type',
        'entity_id',
        'payload_diff',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'payload_diff' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
