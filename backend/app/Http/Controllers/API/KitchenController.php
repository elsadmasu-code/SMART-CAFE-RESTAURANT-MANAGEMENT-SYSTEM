<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\KitchenTicket;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Notification;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    /**
     * Get Active Kitchen Tickets for KDS Display
     */
    public function getTickets(Request $request)
    {
        $stationId = $request->query('station_id');

        $query = KitchenTicket::with(['order.table', 'items', 'chef', 'station'])
            ->whereIn('status', ['new', 'accepted', 'preparing', 'delayed'])
            ->orderBy('created_at', 'asc');

        if ($stationId) {
            $query->where('kitchen_station_id', $stationId);
        }

        $tickets = $query->get()->map(function ($ticket) {
            $elapsedMinutes = $ticket->created_at->diffInMinutes(now());
            $isOverdue = $elapsedMinutes > $ticket->target_preparation_time_minutes;

            return [
                'id' => $ticket->id,
                'ticket_uuid' => $ticket->ticket_uuid,
                'order_id' => $ticket->order_id,
                'order_number' => $ticket->order->order_number,
                'order_type' => $ticket->order->order_type,
                'table_number' => $ticket->order->table?->table_number ?? 'N/A',
                'status' => $ticket->status,
                'target_time_minutes' => $ticket->target_preparation_time_minutes,
                'elapsed_minutes' => $elapsedMinutes,
                'is_overdue' => $isOverdue,
                'items' => $ticket->items->map(fn($i) => [
                    'id' => $i->id,
                    'item_name' => $i->item_name,
                    'quantity' => $i->quantity,
                    'notes' => $i->notes,
                    'is_ready' => $i->is_ready,
                ]),
                'created_at' => $ticket->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    /**
     * Update Preparation Status: 'accepted', 'preparing', 'ready'
     */
    public function updateStatus(Request $request, int $ticketId)
    {
        $request->validate([
            'status' => 'required|in:accepted,preparing,ready,delayed',
        ]);

        $ticket = KitchenTicket::with('order.table')->findOrFail($ticketId);
        $chefId = $request->user()->id;
        $newStatus = $request->status;

        $updates = [
            'status' => $newStatus,
            'chef_user_id' => $chefId,
        ];

        if ($newStatus === 'accepted') {
            $updates['accepted_at'] = now();
            $ticket->order->update(['order_status' => 'accepted_by_kitchen']);
        } elseif ($newStatus === 'preparing') {
            $updates['preparing_at'] = now();
            $ticket->order->update(['order_status' => 'preparing']);
        } elseif ($newStatus === 'ready') {
            $updates['ready_at'] = now();
            $ticket->order->update(['order_status' => 'ready']);

            // Notify waitstaff that food is ready for delivery
            Notification::create([
                'role_target' => 'waiter',
                'title' => 'Order Ready for Delivery!',
                'body' => "Order #{$ticket->order->order_number} for Table " . ($ticket->order->table?->table_number ?? 'Takeaway') . " is ready.",
                'type' => 'order_ready',
                'metadata' => ['order_id' => $ticket->order->id, 'table_id' => $ticket->order->table_id],
            ]);
        }

        $ticket->update($updates);

        OrderStatusHistory::create([
            'order_id' => $ticket->order_id,
            'from_status' => $ticket->order->order_status,
            'to_status' => $newStatus,
            'changed_by_user_id' => $chefId,
            'comment' => "Kitchen ticket status updated to {$newStatus}",
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Ticket status updated to {$newStatus}.",
            'data' => $ticket->fresh(),
        ]);
    }
}
