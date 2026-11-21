<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    protected $fillable = [
        'dining_table_id',
        'signed_token',
        'qr_url',
        'scan_count',
        'last_scanned_at',
    ];

    protected $casts = [
        'scan_count' => 'integer',
        'last_scanned_at' => 'datetime',
    ];

    public function table()
    {
        return $this->belongsTo(DiningTable::class, 'dining_table_id');
    }
}
