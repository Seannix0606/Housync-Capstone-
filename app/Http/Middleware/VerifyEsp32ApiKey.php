<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyEsp32ApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = env('ESP32_API_KEY');

        // Check if the server has the key configured
        if (empty($expectedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Server configuration error: ESP32_API_KEY is not set.'
            ], 500);
        }

        // Check if the request contains the correct key
        $providedKey = $request->header('X-ESP32-Key');

        if (empty($providedKey) || $providedKey !== $expectedKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing X-ESP32-Key header.'
            ], 401);
        }

        return $next($request);
    }
}