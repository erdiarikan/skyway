<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Flight;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Flight */
final class FlightResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'flightId' => $this->uuid,
            'legs' => LegResource::collection($this->legs),
        ];
    }
}
