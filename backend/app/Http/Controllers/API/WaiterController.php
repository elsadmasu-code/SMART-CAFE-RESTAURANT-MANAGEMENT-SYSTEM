<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WaiterAssignment;
use App\Models\ServiceCall;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;

class WaiterController extends Controller
{
    /**
     * Get Ready Orders and Pending Table Service Calls
     */
    public function getActiveTasks(Request $request)
    {
        $branchId = $request->user()->branch_id ?? 1;

        // Ready orders awaiting waiter pickup or delivery
        $readyOrders = Order::with(['table', 'items'])
            ->where('branch_id', $branchId)
            ->whereIn('order_status', ['ready', 'assigned_to_waiter'])
            ->orderBy('updated_at', 'asc')
            ->get();

        // Customer service calls (water, bill, call waiter)
        $serviceCalls = ServiceCall::with('table')
            ->where('branch_id', $branchId)
            ->whereIn('status', ['pending', 'acknowledged'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ready_orders' => $readyOrders,
                'service_calls' => $serviceCalls,
            ],
        ]);
    }

    /**
     * Mark Order as Delivered to Table
     */
    public function markDelivered(Request $request, int $orderId)
    {
        $order = Order::with('table')->findOrFail($orderId);
        $waiterId = $request->user()->id;

        $order->update([
            'order_status' => 'delivered',
        ]);

        WaiterAssignment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'waiter_user_id' => $waiterId,
                'delivered_at' => now(),
                'status' => 'delivered',
            ]
        );

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => 'ready',
            'to_status' => 'delivered',
            'changed_by_user_id' => $waiterId,
            'comment' => "Delivered to Table " . ($order->table?->table_number ?? 'Takeaway'),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order marked as delivered.',
        ]);
    }

    /**
     * Customer Service Call: Call Waiter / Request Water or Bill
     */
    public function callService(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:dining_tables,id',
            'request_type' => 'required|in:waiter_call,water,bill,napkins,clean_table,custom',
            'notes' => 'nullable|string|max:255',
        ]);

        $call = ServiceCall::create([
            'branch_id' => 1,
            'table_id' => $request->table_id,
            'request_type' => $request->request_type,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your request has been dispatched to floor waitstaff.',
            'data' => $call,
        ]);
    }

    /**
     * Waiter: Acknowledge or Complete Table Service Call
     */
    public function updateServiceCall(Request $request, int $callId)
    {
        $request->validate([
            'status' => 'required|in:acknowledged,completed,cancelled',
        ]);

        $call = ServiceCall::findOrFail($callId);
        $waiterId = $request->user()->id;

        $updates = ['status' => $request->status, 'handled_by_waiter_id' => $waiterId];
        if ($request->status === 'acknowledged') {
            $updates['acknowledged_at'] = now();
        } elseif ($request->status === 'completed') {
            $updates['completed_at'] = now();
        }

        $call->update($updates);

        return response()->json([
            'success' => true,
            'message' => "Service request marked as {$request->status}.",
            'data' => $call,
        ]);
    }
}
