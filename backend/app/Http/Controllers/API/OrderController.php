<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductAddon;
use App\Models\DiningTable;
use App\Models\OrderStatusHistory;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Submit New Order (Customer, Guest, or Staff)
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_type' => 'required|in:dine_in,takeaway,delivery',
            'table_id' => 'required_if:order_type,dine_in|nullable|exists:dining_tables,id',
            'guest_name' => 'nullable|string|max:100',
            'guest_phone' => 'nullable|string|max:30',
            'delivery_address' => 'required_if:order_type,delivery|nullable|string',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.addon_ids' => 'nullable|array',
            'items.*.addon_ids.*' => 'exists:product_addons,id',
            'items.*.special_instructions' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $branchId = $user?->branch_id ?? 1;

            $subtotal = 0.00;
            $orderItemsData = [];

            // 1. Calculate price snapshot and validate availability
            foreach ($request->items as $itemInput) {
                $product = Product::with(['translations', 'variants', 'addons'])->findOrFail($itemInput['product_id']);

                if (!$product->is_available) {
                    abort(422, "Product '{$product->getLocalizedName('en')}' is currently out of stock.");
                }

                $unitPrice = (float) $product->price;

                // Variant modifier
                $variant = null;
                if (!empty($itemInput['variant_id'])) {
                    $variant = ProductVariant::find($itemInput['variant_id']);
                    if ($variant) {
                        $unitPrice += (float) $variant->price_modifier;
                    }
                }

                // Addons calculation
                $selectedAddons = [];
                if (!empty($itemInput['addon_ids'])) {
                    $addons = ProductAddon::whereIn('id', $itemInput['addon_ids'])->get();
                    foreach ($addons as $addon) {
                        $unitPrice += (float) $addon->extra_price;
                        $selectedAddons[] = [
                            'addon_id' => $addon->id,
                            'name' => $addon->addon_name_en,
                            'price' => (float) $addon->extra_price,
                        ];
                    }
                }

                $itemTotal = $unitPrice * $itemInput['quantity'];
                $subtotal += $itemTotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'name_snapshot' => $product->getLocalizedName('en'),
                    'unit_price' => $unitPrice,
                    'quantity' => $itemInput['quantity'],
                    'total_price' => $itemTotal,
                    'instructions' => $itemInput['special_instructions'] ?? null,
                    'addons' => $selectedAddons,
                ];
            }

            // 2. Tax & Service Charge (Ethiopian standard: 15% VAT, 5% Service)
            $taxAmount = round($subtotal * 0.15, 2);
            $serviceCharge = round($subtotal * 0.05, 2);
            $totalAmount = $subtotal + $taxAmount + $serviceCharge;

            $orderNumber = 'FKR-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            $orderUuid = (string) Str::uuid();

            // 3. Create Order
            $order = Order::create([
                'order_uuid' => $orderUuid,
                'branch_id' => $branchId,
                'order_number' => $orderNumber,
                'customer_id' => $user?->id,
                'table_id' => $request->order_type === 'dine_in' ? $request->table_id : null,
                'order_type' => $request->order_type,
                'guest_name' => $request->guest_name ?? $user?->name,
                'guest_phone' => $request->guest_phone ?? $user?->phone,
                'delivery_address' => $request->delivery_address,
                'order_status' => 'submitted',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'discount_amount' => 0.00,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
            ]);

            // 4. Create Order Items & Options
            foreach ($orderItemsData as $itemData) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'product_variant_id' => $itemData['product_variant_id'],
                    'product_name_snapshot' => $itemData['name_snapshot'],
                    'unit_price_snapshot' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'total_price' => $itemData['total_price'],
                    'special_instructions' => $itemData['instructions'],
                ]);

                foreach ($itemData['addons'] as $addonData) {
                    OrderItemOption::create([
                        'order_item_id' => $orderItem->id,
                        'product_addon_id' => $addonData['addon_id'],
                        'addon_name_snapshot' => $addonData['name'],
                        'price_snapshot' => $addonData['price'],
                    ]);
                }
            }

            // 5. Initial Status History
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'submitted',
                'changed_by_user_id' => $user?->id,
                'comment' => 'Order submitted via ' . ($user ? 'User Account' : 'Guest Checkout'),
                'created_at' => now(),
            ]);

            // 6. Update dining table status if dine-in
            if ($order->table_id) {
                DiningTable::where('id', $order->table_id)->update(['status' => 'occupied']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully. Please proceed with payment.',
                'data' => [
                    'order_uuid' => $order->order_uuid,
                    'order_number' => $order->order_number,
                    'subtotal' => (float) $order->subtotal,
                    'tax_amount' => (float) $order->tax_amount,
                    'service_charge' => (float) $order->service_charge,
                    'total_amount' => (float) $order->total_amount,
                    'order_status' => $order->order_status,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at->toIso8601String(),
                ],
            ], 201);
        });
    }

    /**
     * Get Order Live Tracking Status
     */
    public function show(string $uuid)
    {
        $order = Order::with(['items.options', 'table', 'payments', 'receipt', 'statusHistory'])
            ->where('order_uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Staff: List All Orders with Filters
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $paymentStatus = $request->query('payment_status');

        $query = Order::with(['items', 'table', 'customer', 'payments'])
            ->orderBy('id', 'desc');

        if ($status) {
            $query->where('order_status', $status);
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }

    /**
     * Cancel an Order (Authorized Staff or Allowed Customer State)
     */
    public function cancel(Request $request, string $uuid)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $order = Order::where('order_uuid', $uuid)->firstOrFail();

        if (in_array($order->order_status, ['delivered', 'completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => "Order cannot be cancelled in state '{$order->order_status}'.",
            ], 422);
        }

        DB::transaction(function () use ($order, $request) {
            $prevStatus = $order->order_status;
            $order->update([
                'order_status' => 'cancelled',
                'cancellation_reason' => $request->reason,
                'cancelled_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $prevStatus,
                'to_status' => 'cancelled',
                'changed_by_user_id' => $request->user()?->id,
                'comment' => "Cancellation: {$request->reason}",
                'created_at' => now(),
            ]);

            // Reverse inventory if it had been confirmed/deducted
            if ($order->payment_status === 'paid' || $prevStatus === 'confirmed') {
                $this->inventoryService->reverseStockForOrder($order, $request->user()?->id, $request->reason);
            }

            // Free table if dine-in
            if ($order->table_id) {
                DiningTable::where('id', $order->table_id)->update(['status' => 'vacant']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
        ]);
    }
}
