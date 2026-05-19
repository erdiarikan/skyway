<?php

declare(strict_types=1);

namespace App\Data;

final readonly class SegmentData
{
    public function __construct(
        public string $origin,
        public string $destination,
        public string $departure,
        public string $arrival,
        public string $cabinClass,
        public string $airline,
        public string $flightNumber,
    ) {}

    public static function fromArray(array $segment): self
    {
        return new self(
            origin: $segment['origin'],
            destination: $segment['destination'],
            departure: $segment['departure'],
            arrival: $segment['arrival'],
            cabinClass: $segment['cabinClass'],
            airline: $segment['airline'],
            flightNumber: $segment['flightNumber'],
        );
    }
}
