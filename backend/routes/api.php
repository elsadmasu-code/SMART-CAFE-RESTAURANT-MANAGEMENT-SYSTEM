<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MenuController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\KitchenController;
use App\Http\Controllers\API\WaiterController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\FinanceController;
use App\Http\Controllers\API\AiAssistantController;
use App\Http\Controllers\API\SyncController;
use App\Http\Controllers\API\TableSessionController;

/*
|--------------------------------------------------------------------------
| SCRMS API Routes (v1) - Fkr Cafe (Kombolcha, Ethiopia)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // 1. Public & Customer Accessible Endpoints
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    Route::get('/menu', [MenuController::class, 'index']);
    Route::get('/tables', [TableSessionController::class, 'index']);
    Route::post('/tables/verify-qr', [TableSessionController::class, 'verifyQr']);

    // Order Submission (checks cafe open status, supports idempotency key)
    Route::post('/orders', [OrderController::class, 'store'])
        ->middleware(['cafe.open', 'idempotency']);
    Route::get('/orders/{uuid}', [OrderController::class, 'show']);
    Route::post('/orders/{uuid}/cancel', [OrderController::class, 'cancel']);

    // Payments
    Route::post('/payments/submit', [PaymentController::class, 'submitPayment'])
        ->middleware('idempotency');
    Route::get('/payments/receipt/{number}', [PaymentController::class, 'getReceipt']);

    // Table Service Call (Call Waiter / Request Water)
    Route::post('/waiter/service-call', [WaiterController::class, 'callService']);

    // Contextual Multilingual Gemini AI Chat
    Route::post('/ai/chat', [AiAssistantController::class, 'chat']);

    // 2. Authenticated Staff Endpoints
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/auth/profile', [AuthController::class, 'profile']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Orders Master List
        Route::get('/orders', [OrderController::class, 'index']);

        // Cashier Operations
        Route::middleware('role:manager,cashier')->group(function () {
            Route::post('/payments/verify', [PaymentController::class, 'verifyPayment']);
        });

        // Kitchen Display System (KDS)
        Route::middleware('role:manager,chef')->group(function () {
            Route::get('/kitchen/tickets', [KitchenController::class, 'getTickets']);
            Route::patch('/kitchen/tickets/{id}/status', [KitchenController::class, 'updateStatus']);
        });

        // Waiter Floor Operations
        Route::middleware('role:manager,waiter')->group(function () {
            Route::get('/waiter/tasks', [WaiterController::class, 'getActiveTasks']);
            Route::post('/waiter/orders/{id}/delivered', [WaiterController::class, 'markDelivered']);
            Route::patch('/waiter/service-call/{id}', [WaiterController::class, 'updateServiceCall']);
        });

        // Storekeeper & Inventory Management
        Route::middleware('role:manager,storekeeper')->group(function () {
            Route::get('/inventory/ingredients', [InventoryController::class, 'getIngredients']);
            Route::post('/inventory/purchases', [InventoryController::class, 'recordPurchase']);
            Route::post('/inventory/wastage', [InventoryController::class, 'recordWastage']);
            Route::get('/inventory/movements', [InventoryController::class, 'getMovements']);
        });

        // Attendance (Local Wi-Fi validation enforced in service)
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
        Route::get('/attendance', [AttendanceController::class, 'index'])
            ->middleware('role:manager');

        // Human Resources & Employees
        Route::post('/employees/leave-request', [EmployeeController::class, 'requestLeave']);
        Route::middleware('role:manager')->group(function () {
            Route::get('/employees', [EmployeeController::class, 'index']);
            Route::post('/employees', [EmployeeController::class, 'store']);
            Route::patch('/menu/availability/{id}', [MenuController::class, 'toggleAvailability']);
            Route::get('/finance/dashboard', [FinanceController::class, 'dashboard']);
            Route::post('/finance/expenses', [FinanceController::class, 'recordExpense']);
        });

        // Offline Edge / Cloud Sync
        Route::post('/sync/push', [SyncController::class, 'push']);
        Route::get('/sync/pull', [SyncController::class, 'pull']);
    });
});
