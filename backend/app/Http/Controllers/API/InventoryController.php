<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\StockMovement;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\WastageRecord;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Get All Ingredients with Low-Stock Flags
     */
    public function getIngredients(Request $request)
    {
        $ingredients = Ingredient::orderBy('name_en')->get()->map(function ($i) {
            return [
                'id' => $i->id,
                'name_en' => $i->name_en,
                'name_am' => $i->name_am,
                'sku' => $i->sku,
                'unit' => $i->unit_of_measure,
                'current_stock' => (float) $i->current_stock,
                'minimum_stock' => (float) $i->minimum_stock_level,
                'reorder_level' => (float) $i->reorder_level,
                'cost_per_unit' => (float) $i->cost_per_unit,
                'is_low_stock' => $i->isLowStock(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $ingredients,
        ]);
    }

    /**
     * Record Supplier Purchase (Stock Intake)
     */
    public function recordPurchase(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string|max:50',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_cost' => 'required|numeric|min:0.01',
        ]);

        return DB::transaction(function () use ($request) {
            $totalAmount = 0.00;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_cost'];
            }

            $purchase = Purchase::create([
                'branch_id' => $request->user()->branch_id ?? 1,
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $totalAmount,
                'payment_status' => 'paid',
                'received_by_user_id' => $request->user()->id,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['unit_cost'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity_received' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                // Increment stock in ingredient
                $ingredient = Ingredient::lockForUpdate()->find($item['ingredient_id']);
                $newStock = $ingredient->current_stock + $item['quantity'];

                $ingredient->update([
                    'current_stock' => $newStock,
                    'cost_per_unit' => $item['unit_cost'],
                ]);

                // Record stock movement
                StockMovement::create([
                    'branch_id' => $purchase->branch_id,
                    'ingredient_id' => $ingredient->id,
                    'movement_type' => 'purchase',
                    'quantity_change' => $item['quantity'],
                    'balance_after' => $newStock,
                    'reference_type' => 'Purchase',
                    'reference_id' => $purchase->id,
                    'created_by_user_id' => $request->user()->id,
                    'reason' => "Purchase Invoice #{$purchase->invoice_number}",
                    'created_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase successfully recorded and inventory updated.',
                'data' => $purchase->load('items'),
            ], 201);
        });
    }

    /**
     * Record Spoilage or Wastage
     */
    public function recordWastage(Request $request)
    {
        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'wasted_quantity' => 'required|numeric|min:0.001',
            'reason_code' => 'required|string|max:50', // expired, burned, spoiled, spilled
        ]);

        return DB::transaction(function () use ($request) {
            $ingredient = Ingredient::lockForUpdate()->findOrFail($request->ingredient_id);
            $lossCost = $ingredient->cost_per_unit * $request->wasted_quantity;

            $newStock = max(0, $ingredient->current_stock - $request->wasted_quantity);
            $ingredient->update(['current_stock' => $newStock]);

            $wastage = WastageRecord::create([
                'branch_id' => $request->user()->branch_id ?? 1,
                'ingredient_id' => $ingredient->id,
                'wasted_quantity' => $request->wasted_quantity,
                'estimated_cost_loss' => $lossCost,
                'reason_code' => $request->reason_code,
                'reported_by_user_id' => $request->user()->id,
            ]);

            StockMovement::create([
                'branch_id' => $wastage->branch_id,
                'ingredient_id' => $ingredient->id,
                'movement_type' => 'wastage',
                'quantity_change' => -$request->wasted_quantity,
                'balance_after' => $newStock,
                'reference_type' => 'WastageRecord',
                'reference_id' => $wastage->id,
                'created_by_user_id' => $request->user()->id,
                'reason' => "Wastage: {$request->reason_code}",
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wastage recorded and stock deducted.',
                'data' => $wastage,
            ]);
        });
    }

    /**
     * Get Immutable Stock Movement History
     */
    public function getMovements(Request $request)
    {
        $movements = StockMovement::with('ingredient')
            ->orderBy('id', 'desc')
            ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $movements,
        ]);
    }
}
