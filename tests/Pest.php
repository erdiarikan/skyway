<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/** @return array<string, mixed> */
function apiHeaders(): array
{
    return ['Api-Key' => config('app.api_key')];
}

/** @return array<string, mixed> */
function withIdempotency(string $key = 'test-idempotency-key'): array
{
    return [...apiHeaders(), 'Idempotency-Key' => $key];
}

/** @return array<string, mixed> */
function validPayload(): array
{
    return [
        'legs' => [[
            'segments' => [[
                'origin' => 'BCN',
                'destination' => 'LHR',
                'departure' => '2026-06-09T06:45:00',
                'arrival' => '2026-06-09T10:55:00',
                'cabinClass' => 'Y',
                'airline' => 'UA',
                'flightNumber' => 'UA101',
            ]],
        ]],
    ];
}
