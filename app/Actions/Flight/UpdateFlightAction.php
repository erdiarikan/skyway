<?php

declare(strict_types=1);

namespace App\Actions\Flight;

use App\Data\FlightData;
use App\Data\LegData;
use App\Data\SegmentData;
use App\Models\Flight;
use App\Models\Leg;
use Illuminate\Support\Facades\DB;

final class UpdateFlightAction
{
    public function execute(Flight $flight, FlightData $flightData): void
    {
        DB::transaction(function () use ($flight, $flightData): void {
            foreach ($flightData->legs as $legData) {
                $leg = $this->resolveMatchingLeg($flight, $legData);

                $leg->segments()->delete();

                foreach ($legData->segments as $segmentPosition => $segmentData) {
                    $this->createSegment($leg, $segmentData, $segmentPosition + 1);
                }
            }
        });
    }

    private function resolveMatchingLeg(Flight $flight, LegData $legData): Leg
    {
        return $flight->legs()
            ->where('origin', $legData->origin)
            ->where('destination', $legData->destination)
            ->firstOrFail();
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
