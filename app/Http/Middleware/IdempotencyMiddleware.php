<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class IdempotencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (empty($key)) {
            return response()->json(['message' => 'Idempotency-Key header is required.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            IdempotencyKey::create(['key' => $key]);
        } catch (UniqueConstraintViolationException) {
            return response()->noContent();
        }

        return $next($request);
    }
}
