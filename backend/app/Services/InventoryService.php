<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Ingredient;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Deduct inventory based on recipes when an order is confirmed.
     * Wrapped in database transaction with pessimistic locking to prevent race conditions.
     */
    public function deductStockForOrder(Order $order, ?int $userId = null): void
    {
        DB::transaction(function () use ($order, $userId) {
            // Check if stock has already been deducted for this order (prevent duplicate deduction)
            $alreadyDeducted = StockMovement::where('reference_type', 'Order')
                ->where('reference_id', $order->id)
                ->where('movement_type', 'order_consumption')
                ->exists();

            if ($alreadyDeducted) {
                return;
            }

            foreach ($order->items as $orderItem) {
                $product = $orderItem->product;
                if (!$product) {
                    continue;
                }

                $recipe = $product->recipe;
                if (!$recipe) {
                    continue; // Product has no recipe configured
                }

                foreach ($recipe->items as $recipeItem) {
                    $ingredient = Ingredient::where('id', $recipeItem->ingredient_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$ingredient) {
                        continue;
                    }

                    $totalDeduction = $recipeItem->quantity_required * $orderItem->quantity;
                    $newStock = $ingredient->current_stock - $totalDeduction;

                    $ingredient->update([
                        'current_stock' => $newStock,
                    ]);

                    StockMovement::create([
                        'branch_id' => $order->branch_id,
                        'ingredient_id' => $ingredient->id,
                        'movement_type' => 'order_consumption',
                        'quantity_change' => -$totalDeduction,
                        'balance_after' => $newStock,
                        'reference_type' => 'Order',
                        'reference_id' => $order->id,
                        'created_by_user_id' => $userId,
                        'reason' => "Auto-deduction for Order #{$order->order_number} ({$orderItem->product_name_snapshot} x{$orderItem->quantity})",
                        'created_at' => now(),
                    ]);
                }
            }
        });
    }

    /**
     * Reverses inventory deductions when an order is cancelled.
     */
    public function reverseStockForOrder(Order $order, ?int $userId = null, string $reason = 'Order cancelled'): void
    {
        DB::transaction(function () use ($order, $userId, $reason) {
            $deductionMovements = StockMovement::where('reference_type', 'Order')
                ->where('reference_id', $order->id)
                ->where('movement_type', 'order_consumption')
                ->get();

            foreach ($deductionMovements as $movement) {
                $ingredient = Ingredient::where('id', $movement->ingredient_id)
                    ->lockForUpdate()
                    ->first();

                if (!$ingredient) {
                    continue;
                }

                $restoreQuantity = abs($movement->quantity_change);
                $newStock = $ingredient->current_stock + $restoreQuantity;

                $ingredient->update([
                    'current_stock' => $newStock,
                ]);

                StockMovement::create([
                    'branch_id' => $order->branch_id,
                    'ingredient_id' => $ingredient->id,
                    'movement_type' => 'order_reversal',
                    'quantity_change' => $restoreQuantity,
                    'balance_after' => $newStock,
                    'reference_type' => 'Order',
                    'reference_id' => $order->id,
                    'created_by_user_id' => $userId,
                    'reason' => "Stock reversal: {$reason}",
                    'created_at' => now(),
                ]);
            }
        });
    }
}
