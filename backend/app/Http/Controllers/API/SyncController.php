<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\SyncService;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __construct(
        protected SyncService $syncService
    ) {}

    /**
     * Push offline local events to cloud
     */
    public function push(Request $request)
    {
        $request->validate([
            'events' => 'required|array',
            'events.*.event_uuid' => 'required|string',
            'events.*.aggregate_type' => 'required|string',
            'events.*.payload' => 'required|array',
        ]);

        $results = $this->syncService->processIncomingBatch($request->input('events'));

        return response()->json([
            'success' => true,
            'message' => 'Sync batch processed.',
            'data' => $results,
        ]);
    }

    /**
     * Pull pending events from node
     */
    public function pull(Request $request)
    {
        $limit = $request->query('limit', 50);
        $events = $this->syncService->getPendingSyncBatch((int) $limit);

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }
}
