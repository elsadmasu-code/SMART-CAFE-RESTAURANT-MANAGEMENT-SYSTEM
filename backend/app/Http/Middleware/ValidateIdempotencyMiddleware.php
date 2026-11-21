<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\IdempotencyRecord;
use Symfony\Component\HttpFoundation\Response;

class ValidateIdempotencyMiddleware
{
    /**
     * Intercept idempotent requests
     */
    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = $request->header('X-Idempotency-Key');

        if (!$idempotencyKey || !$request->isMethodSafe() === false) {
            return $next($request);
        }

        $existing = IdempotencyRecord::where('idempotency_key', $idempotencyKey)
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return response()->json($existing->response_body, $existing->response_status_code)
                ->header('X-Cache-Lookup', 'HIT-IDEMPOTENT');
        }

        $response = $next($request);

        // Cache successful response for 24 hours
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $content = json_decode($response->getContent(), true) ?? [];
            IdempotencyRecord::create([
                'idempotency_key' => $idempotencyKey,
                'endpoint' => $request->path(),
                'request_hash' => hash('sha256', json_encode($request->all())),
                'response_body' => $content,
                'response_status_code' => $response->getStatusCode(),
                'expires_at' => now()->addHours(24),
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}
