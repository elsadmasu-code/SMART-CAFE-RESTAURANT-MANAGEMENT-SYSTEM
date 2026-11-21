<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\KitchenTicket;
use App\Models\KitchenTicketItem;
use App\Models\KitchenStation;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class PaymentVerificationService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Verify payment and transition order to confirmed state.
     */
    public function verifyPayment(
        Order $order,
        string $paymentMethod,
        float $amountPaid,
        ?string $transactionReference = null,
        ?int $cashierUserId = null,
        ?string $proofImageUrl = null
    ): Payment {
        return DB::transaction(function () use (
            $order,
            $paymentMethod,
            $amountPaid,
            $transactionReference,
            $cashierUserId,
            $proofImageUrl
        ) {
            // 1. Record or update Payment
            $payment = Payment::create([
                'payment_uuid' => (string) Str::uuid(),
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'payment_method' => $paymentMethod,
                'amount' => $amountPaid,
                'currency' => 'ETB',
                'transaction_reference' => $transactionReference,
                'status' => 'verified',
                'proof_image_url' => $proofImageUrl,
                'verified_by_user_id' => $cashierUserId,
                'verified_at' => now(),
            ]);

            // 2. Update Order Status
            $previousStatus = $order->order_status;
            $order->update([
                'order_status' => 'confirmed',
                'payment_status' => 'paid',
                'confirmed_by_user_id' => $cashierUserId,
                'confirmed_at' => now(),
            ]);

            // 3. Status History
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $previousStatus,
                'to_status' => 'confirmed',
                'changed_by_user_id' => $cashierUserId,
                'comment' => "Payment verified via {$paymentMethod} (Ref: {$transactionReference})",
                'created_at' => now(),
            ]);

            // 4. Generate Official Receipt
            $receiptNumber = 'REC-' . date('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            Receipt::create([
                'order_id' => $order->id,
                'receipt_number' => $receiptNumber,
                'issued_by_user_id' => $cashierUserId,
                'total_amount' => $amountPaid,
                'payment_method_snapshot' => $paymentMethod,
                'printed_count' => 1,
                'created_at' => now(),
            ]);

            // 5. Automatic Recipe-based Inventory Depletion
            $this->inventoryService->deductStockForOrder($order, $cashierUserId);

            // 6. Automatically dispatch order items to Kitchen Ticket
            $this->createKitchenTickets($order);

            return $payment;
        });
    }

    /**
     * Dispatch confirmed order to Kitchen Display System tickets
     */
    protected function createKitchenTickets(Order $order): void
    {
        // Group items or assign default station
        $defaultStation = KitchenStation::where('branch_id', $order->branch_id)->first();

        $ticket = KitchenTicket::create([
            'ticket_uuid' => (string) Str::uuid(),
            'order_id' => $order->id,
            'kitchen_station_id' => $defaultStation?->id,
            'status' => 'new',
            'target_preparation_time_minutes' => 15,
        ]);

        foreach ($order->items as $item) {
            KitchenTicketItem::create([
                'kitchen_ticket_id' => $ticket->id,
                'order_item_id' => $item->id,
                'item_name' => $item->product_name_snapshot,
                'quantity' => $item->quantity,
                'notes' => $item->special_instructions,
                'is_ready' => false,
            ]);
        }
    }
}
