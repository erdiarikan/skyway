<?php

declare(strict_types=1);

use App\Models\Flight;
use App\Models\Leg;
use App\Models\Segment;

it('returns the flight with legs and segments', function () {
    $flight = Flight::factory()->create();
    $leg = Leg::factory()->for($flight)->create(['origin' => 'BCN', 'destination' => 'LHR', 'position' => 1]);
    Segment::factory()->for($leg)->create();

    $this->getJson("/api/flights/{$flight->uuid}", apiHeaders())
        ->assertOk()
        ->assertJsonStructure(['flightId', 'legs' => [['segments']]])
        ->assertJsonPath('flightId', $flight->uuid)
        ->assertJsonCount(1, 'legs')
        ->assertJsonCount(1, 'legs.0.segments');
});

it('requires an api key', function () {
    $flight = Flight::factory()->create();

    $this->getJson("/api/flights/{$flight->uuid}")
        ->assertUnauthorized();
});

it('returns 404 for an unknown flight', function () {
    $this->getJson('/api/flights/non-existent-uuid', apiHeaders())
        ->assertNotFound();
});
