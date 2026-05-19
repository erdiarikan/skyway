<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SegmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'origin' => $this->origin,
            'destination' => $this->destination,
            'departure' => $this->departure->format('Y-m-d\TH:i:s'),
            'arrival' => $this->arrival->format('Y-m-d\TH:i:s'),
            'cabinClass' => $this->cabin_class,
            'airline' => $this->airline,
            'flightNumber' => $this->flight_number,
        ];
    }
}
