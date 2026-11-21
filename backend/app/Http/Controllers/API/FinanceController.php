<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * Executive Financial Summary & Analytics Dashboard
     */
    public function dashboard(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfDay()->toDateTimeString());
        $endDate = $request->query('end_date', now()->endOfDay()->toDateTimeString());

        // Orders within date range
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('order_status', ['confirmed', 'accepted_by_kitchen', 'preparing', 'ready', 'delivered', 'completed'])
            ->get();

        $grossSales = (float) $orders->sum('total_amount');
        $totalOrdersCount = $orders->count();

        // Payments breakdown
        $payments = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'verified')
            ->get();

        $cashSales = (float) $payments->where('payment_method', 'cash')->sum('amount');
        $telebirrSales = (float) $payments->where('payment_method', 'telebirr')->sum('amount');
        $cbeBirrSales = (float) $payments->where('payment_method', 'cbe_birr')->sum('amount');
        $chapaSales = (float) $payments->where('payment_method', 'chapa')->sum('amount');

        // Expenses within date range
        $expenses = Expense::whereBetween('expense_date', [
            \Carbon\Carbon::parse($startDate)->toDateString(),
            \Carbon\Carbon::parse($endDate)->toDateString()
        ])->get();

        $totalExpenses = (float) $expenses->sum('amount');

        // Estimated Cost of Goods Sold (COGS) at 40% benchmark if exact recipe history is not materialized
        $cogs = round($grossSales * 0.40, 2);
        $grossProfit = $grossSales - $cogs;
        $netProfit = $grossProfit - $totalExpenses;

        return response()->json([
            'success' => true,
            'data' => [
                'currency' => 'ETB',
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
                'summary' => [
                    'gross_sales' => $grossSales,
                    'total_orders' => $totalOrdersCount,
                    'cost_of_goods_sold' => $cogs,
                    'gross_profit' => $grossProfit,
                    'total_expenses' => $totalExpenses,
                    'estimated_net_profit' => $netProfit,
                ],
                'payments_breakdown' => [
                    'cash' => $cashSales,
                    'telebirr' => $telebirrSales,
                    'cbe_birr' => $cbeBirrSales,
                    'chapa' => $chapaSales,
                ],
            ],
        ]);
    }

    /**
     * Record Operating Expense
     */
    public function recordExpense(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,telebirr,check',
            'receipt_voucher_number' => 'nullable|string|max:50',
        ]);

        $expense = Expense::create([
            'branch_id' => $request->user()->branch_id ?? 1,
            'expense_category_id' => $request->expense_category_id,
            'amount' => $request->amount,
            'currency' => 'ETB',
            'description' => $request->description,
            'expense_date' => $request->expense_date,
            'payment_method' => $request->payment_method,
            'receipt_voucher_number' => $request->receipt_voucher_number,
            'paid_by_user_id' => $request->user()->id,
            'approved_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense recorded successfully.',
            'data' => $expense->load('category'),
        ], 201);
    }
}
