<?php

namespace App\Http\Middleware;

use App\Http\Resources\Api\V1\TransferResource;
use App\Models\Transfer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $key = $request->header('Idempotency-Key');

        if (!$key) {
            return response()->json([
                'message' => 'Missing Idempotency-Key header.'
            ], 400);
        }

        $existing = Transfer::where('idempotency_key', $key)->first();

        if ($existing) {
            return response()->json([
                'message' => 'Duplicate request.',
                'data' => [
                    'transfer' => TransferResource::make($existing),
                ],
            ]);
        }

        $request->merge(['idempotency_key' => $key]);

        return $next($request);
    }
}
