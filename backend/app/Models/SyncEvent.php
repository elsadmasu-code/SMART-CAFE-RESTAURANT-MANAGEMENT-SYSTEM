<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_uuid',
        'branch_id',
        'aggregate_type',
        'aggregate_uuid',
        'event_type',
        'payload',
        'sync_status',
        'retry_count',
        'error_message',
        'synced_at',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'retry_count' => 'integer',
        'synced_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
