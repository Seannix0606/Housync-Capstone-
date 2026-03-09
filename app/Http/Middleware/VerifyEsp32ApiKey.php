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
        $expectedKey = config('services.esp32.key');

        // Check if the server has the key configured
        if (empty($expectedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error: Application configuration is missing or invalid.'
            ], 500);
        }

        // Check if the request contains the correct key
        $providedKey = $request->header('X-ESP32-Key');

        if (!is_string($providedKey) || !hash_equals((string) $expectedKey, $providedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing authorization credentials.'
            ], 401);
        }

        return $next($request);
    }
}