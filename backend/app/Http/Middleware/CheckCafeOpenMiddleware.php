<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\BusinessHour;
use Symfony\Component\HttpFoundation\Response;

class CheckCafeOpenMiddleware
{
    /**
     * Prevent new customer orders when cafe is closed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Authenticated staff bypass customer closing locks
        if ($request->user() && $request->user()->hasAnyRole(['manager', 'cashier', 'chef', 'waiter', 'storekeeper'])) {
            return $next($request);
        }

        $now = now();
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
        $currentTime = $now->toTimeString();

        $businessHour = BusinessHour::where('day_of_week', $dayOfWeek)->first();

        if ($businessHour) {
            if ($businessHour->is_closed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fkr Cafe is currently closed for the day. Customer ordering is temporarily paused.',
                    'opening_hours' => "Opens at {$businessHour->open_time}",
                ], 423); // 423 Locked
            }

            if ($currentTime < $businessHour->open_time || $currentTime > $businessHour->close_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fkr Cafe is closed at this time. Ordering is available between ' . $businessHour->open_time . ' and ' . $businessHour->close_time . '.',
                    'open_time' => $businessHour->open_time,
                    'close_time' => $businessHour->close_time,
                ], 423);
            }
        }

        return $next($request);
    }
}
