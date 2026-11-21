<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\QrCode;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TableSessionController extends Controller
{
    /**
     * Get All Dining Tables with Status
     */
    public function index()
    {
        $tables = DiningTable::with('qrCode')
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tables,
        ]);
    }

    /**
     * Verify Table QR Token scanned by customer
     */
    public function verifyQr(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $qr = QrCode::with('table')->where('signed_token', $request->token)->first();

        if (!$qr || !$qr->table || !$qr->table->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired table QR code.',
            ], 404);
        }

        // Increment scan count
        $qr->increment('scan_count');
        $qr->update(['last_scanned_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => [
                'table_id' => $qr->table->id,
                'table_number' => $qr->table->table_number,
                'zone' => $qr->table->zone,
                'capacity' => $qr->table->seating_capacity,
            ],
        ]);
    }
}
