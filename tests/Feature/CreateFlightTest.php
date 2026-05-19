<?php

declare(strict_types=1);

use App\Models\Flight;
use App\Models\Leg;
use App\Models\Segment;

it('creates a flight and returns 201 with the flight id', function () {
    $response = $this->postJson('/api/flights', validPayload(), withIdempotency());

    $response
        ->assertCreated()
        ->assertJsonStructure(['flightId'])
        ->assertJsonMissingPath('legs');

    expect(Flight::count())->toBe(1)
        ->and(Leg::count())->toBe(1)
        ->and(Segment::count())->toBe(1);
});

it('returns the flight id in the response', function () {
    $response = $this->postJson('/api/flights', validPayload(), withIdempotency());

    $flightId = $response->json('flightId');

    expect($flightId)->toBeString()->not->toBeEmpty()
        ->and(Flight::where('uuid', $flightId)->exists())->toBeTrue();
});

it('requires an api key', function () {
    $this->postJson('/api/flights', validPayload(), ['Idempotency-Key' => 'key'])
        ->assertUnauthorized();
});

it('requires an idempotency key', function () {
    $this->postJson('/api/flights', validPayload(), apiHeaders())
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Idempotency-Key header is required.');
});

it('deduplicates requests with the same idempotency key', function () {
    $headers = withIdempotency('same-key');

    $this->postJson('/api/flights', validPayload(), $headers)->assertCreated();
    $this->postJson('/api/flights', validPayload(), $headers)->assertNoContent();

    expect(Flight::count())->toBe(1);
});

it('validates required fields', function () {
    $this->postJson('/api/flights', [], withIdempotency())
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['legs']);
});

it('rejects arrival before departure', function () {
    $payload = validPayload();
    $payload['legs'][0]['segments'][0]['arrival'] = '2026-06-09T05:00:00';

    $this->postJson('/api/flights', $payload, withIdempotency())
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['legs.0.segments.0.arrival']);
});
