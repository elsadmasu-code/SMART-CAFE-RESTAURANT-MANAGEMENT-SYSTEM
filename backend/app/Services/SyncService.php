<?php

namespace App\Services;

use App\Models\SyncEvent;
use App\Models\SyncConflict;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Attendance;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class SyncService
{
    /**
     * Enqueue a local event for cloud synchronization
     */
    public function enqueueEvent(int $branchId, string $aggregateType, string $aggregateUuid, string $eventType, array $payload): SyncEvent
    {
        return SyncEvent::create([
            'event_uuid' => (string) Str::uuid(),
            'branch_id' => $branchId,
            'aggregate_type' => $aggregateType,
            'aggregate_uuid' => $aggregateUuid,
            'event_type' => $eventType,
            'payload' => $payload,
            'sync_status' => 'pending',
            'created_at' => now(),
        ]);
    }

    /**
     * Pull pending events to push to cloud
     */
    public function getPendingSyncBatch(int $limit = 50): array
    {
        return SyncEvent::where('sync_status', 'pending')
            ->orderBy('id', 'asc')
            ->take($limit)
            ->get()
            ->toArray();
    }

    /**
     * Replay incoming cloud events on local edge or vice versa
     */
    public function processIncomingBatch(array $events): array
    {
        $results = ['synced' => 0, 'conflicts' => 0, 'errors' => 0];

        foreach ($events as $eventData) {
            try {
                DB::transaction(function () use ($eventData, &$results) {
                    $aggregateType = $eventData['aggregate_type'];
                    $payload = $eventData['payload'];

                    // Check for duplicate or existing
                    $existingEvent = SyncEvent::where('event_uuid', $eventData['event_uuid'])->first();
                    if ($existingEvent && $existingEvent->sync_status === 'synced') {
                        return; // already processed
                    }

                    // Apply mutation based on aggregate type
                    match ($aggregateType) {
                        'Order' => $this->syncOrder($payload),
                        'Payment' => $this->syncPayment($payload),
                        default => null,
                    };

                    if ($existingEvent) {
                        $existingEvent->update(['sync_status' => 'synced', 'synced_at' => now()]);
                    }
                    $results['synced']++;
                });
            } catch (Exception $e) {
                $results['errors']++;
            }
        }

        return $results;
    }

    protected function syncOrder(array $payload): void
    {
        if (empty($payload['order_uuid'])) {
            return;
        }

        Order::updateOrCreate(
            ['order_uuid' => $payload['order_uuid']],
            [
                'branch_id' => $payload['branch_id'] ?? 1,
                'order_number' => $payload['order_number'] ?? 'ORD-' . time(),
                'order_type' => $payload['order_type'] ?? 'dine_in',
                'order_status' => $payload['order_status'] ?? 'submitted',
                'payment_status' => $payload['payment_status'] ?? 'pending',
                'subtotal' => $payload['subtotal'] ?? 0,
                'total_amount' => $payload['total_amount'] ?? 0,
                'is_synced' => true,
            ]
        );
    }

    protected function syncPayment(array $payload): void
    {
        if (empty($payload['payment_uuid'])) {
            return;
        }

        Payment::updateOrCreate(
            ['payment_uuid' => $payload['payment_uuid']],
            [
                'order_id' => $payload['order_id'] ?? 1,
                'branch_id' => $payload['branch_id'] ?? 1,
                'payment_method' => $payload['payment_method'] ?? 'cash',
                'amount' => $payload['amount'] ?? 0,
                'transaction_reference' => $payload['transaction_reference'] ?? null,
                'status' => $payload['status'] ?? 'verified',
            ]
        );
    }
}
