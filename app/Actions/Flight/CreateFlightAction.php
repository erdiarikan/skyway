<?php

declare(strict_types=1);

namespace App\Actions\Flight;

use App\Data\FlightData;
use App\Data\LegData;
use App\Data\SegmentData;
use App\Models\Flight;
use App\Models\Leg;
use Illuminate\Support\Facades\DB;

final class CreateFlightAction
{
    public function execute(FlightData $flightData): Flight
    {
        return DB::transaction(function () use ($flightData): Flight {
            $flight = Flight::create([]);

            foreach ($flightData->legs as $legPosition => $legData) {
                $leg = $this->createLeg($flight, $legData, $legPosition + 1);

                foreach ($legData->segments as $segmentPosition => $segmentData) {
                    $this->createSegment($leg, $segmentData, $segmentPosition + 1);
                }
            }

            return $flight;
        });
    }

    private function createLeg(Flight $flight, LegData $legData, int $position): Leg
    {
        return $flight->legs()->create([
            'origin' => $legData->origin,
            'destination' => $legData->destination,
            'position' => $position,
        ]);
    }

    private function createSegment(Leg $leg, SegmentData $segmentData, int $position): void
    {
        $leg->segments()->create([
            'origin' => $segmentData->origin,
            'destination' => $segmentData->destination,
            'departure' => $segmentData->departure,
            'arrival' => $segmentData->arrival,
            'cabin_class' => $segmentData->cabinClass,
            'airline' => $segmentData->airline,
            'flight_number' => $segmentData->flightNumber,
            'position' => $position,
        ]);
    }
}
