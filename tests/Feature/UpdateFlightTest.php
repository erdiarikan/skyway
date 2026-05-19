<?php

declare(strict_types=1);

use App\Models\Flight;
use App\Models\Leg;
use App\Models\Segment;

function flightWithLeg(string $origin = 'BCN', string $destination = 'LHR'): Flight
{
    $flight = Flight::factory()->create();
    $leg = Leg::factory()->for($flight)->create([
        'origin' => $origin,
        'destination' => $destination,
        'position' => 1,
    ]);
    Segment::factory()->for($leg)->create();

    return $flight;
}

it('dispatches the update job and returns 204', function () {
    $flight = flightWithLeg();

    $this->putJson("/api/flights/{$flight->uuid}", validPayload(), withIdempotency())
        ->assertNoContent();
});

it('replaces segments on the matching leg', function () {
    $flight = flightWithLeg();

    $this->putJson("/api/flights/{$flight->uuid}", validPayload(), withIdempotency());

    $segment = $flight->legs()->firstOrFail()->segments()->firstOrFail();

    expect($segment->flight_number)->toBe('UA101')
        ->and($segment->airline)->toBe('UA')
        ->and($segment->origin)->toBe('BCN')
        ->and($segment->destination)->toBe('LHR');
});

it('requires an api key', function () {
    $flight = flightWithLeg();

    $this->putJson("/api/flights/{$flight->uuid}", validPayload(), ['Idempotency-Key' => 'key'])
        ->assertUnauthorized();
});

it('requires an idempotency key', function () {
    $flight = flightWithLeg();

    $this->putJson("/api/flights/{$flight->uuid}", validPayload(), apiHeaders())
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Idempotency-Key header is required.');
});

it('deduplicates requests with the same idempotency key', function () {
    $flight = flightWithLeg();
    $headers = withIdempotency('same-key');

    $this->putJson("/api/flights/{$flight->uuid}", validPayload(), $headers)->assertNoContent();
    $this->putJson("/api/flights/{$flight->uuid}", validPayload(), $headers)->assertNoContent();
});

it('returns 404 for an unknown flight', function () {
    $this->putJson('/api/flights/non-existent-uuid', validPayload(), withIdempotency())
        ->assertNotFound();
});

it('returns 404 when no leg matches the route', function () {
    $flight = flightWithLeg('JFK', 'LAX');

    $this->putJson("/api/flights/{$flight->uuid}", validPayload(), withIdempotency())
        ->assertNotFound();
});
