<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLisApiKey
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $apiKey = (string) config('services.lis.api_key', '');

        $requestKey = (string) $request->header('x-api-key', '');

        if (
            $apiKey === '' ||
            $requestKey === '' ||
            !hash_equals($apiKey, $requestKey)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal disimpan karena API key tidak valid.'
            ], 401);
        }

        return $next($request);
    }
}