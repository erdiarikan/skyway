<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('app.api_key');

        if (empty($configuredKey) || $request->header('Api-Key') !== $configuredKey) {
            return response()->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
