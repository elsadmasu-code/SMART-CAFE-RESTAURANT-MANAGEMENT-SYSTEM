<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Receipt;
use App\Services\PaymentVerificationService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentVerificationService $paymentVerificationService
    ) {}

    /**
     * Customer: Submit Digital Payment Reference (Telebirr or CBE Birr)
     */
    public function submitPayment(Request $request)
    {
        $request->validate([
            'order_uuid' => 'required|exists:orders,order_uuid',
            'payment_method' => 'required|in:telebirr,cbe_birr,cash,chapa',
            'transaction_reference' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'proof_image_url' => 'nullable|string',
        ]);

        $order = Order::where('order_uuid', $request->order_uuid)->firstOrFail();

        // Customer submitted digital payment: moves to verification pending
        $order->update([
            'payment_status' => 'payment_verification_pending',
            'order_status' => 'payment_verification_pending',
        ]);

        $payment = Payment::create([
            'payment_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'order_id' => $order->id,
            'branch_id' => $order->branch_id,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'currency' => 'ETB',
            'transaction_reference' => $request->transaction_reference,
            'status' => 'verification_pending',
            'proof_image_url' => $request->proof_image_url,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment reference submitted. Awaiting cashier verification.',
            'data' => $payment,
        ]);
    }

    /**
     * Cashier: Verify and Confirm Payment
     * (SRS Section 9.3: Never treat a customer-uploaded payment screenshot as proof by itself; requires explicit confirmation)
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:cash,telebirr,cbe_birr,chapa',
            'amount_paid' => 'required|numeric|min:0.01',
            'transaction_reference' => 'nullable|string|max:100',
        ]);

        $order = Order::with('items.product.recipe.items')->findOrFail($request->order_id);
        $cashierId = $request->user()->id;

        $payment = $this->paymentVerificationService->verifyPayment(
            order: $order,
            paymentMethod: $request->payment_method,
            amountPaid: (float) $request->amount_paid,
            transactionReference: $request->transaction_reference,
            cashierUserId: $cashierId,
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment successfully verified. Order dispatched to Kitchen Display.',
            'data' => [
                'payment' => $payment,
                'order_status' => $order->fresh()->order_status,
                'receipt_number' => $order->fresh()->receipt?->receipt_number,
            ],
        ]);
    }

    /**
     * Get Receipt by Order Number or Receipt Number
     */
    public function getReceipt(string $number)
    {
        $receipt = Receipt::with(['order.items.options', 'order.table', 'order.customer', 'cashier'])
            ->where('receipt_number', $number)
            ->orWhereHas('order', fn($q) => $q->where('order_number', $number)->orWhere('order_uuid', $number))
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $receipt,
        ]);
    }
}
