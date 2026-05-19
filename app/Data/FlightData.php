<?php

declare(strict_types=1);

namespace App\Data;

final readonly class FlightData
{
    /**
     * @param  LegData[]  $legs
     */
    public function __construct(
        public array $legs,
    ) {}

    public static function fromArray(array $validated): self
    {
        return new self(
            legs: array_map(
                static fn (array $leg): LegData => LegData::fromArray($leg),
                $validated['legs'],
            ),
        );
    }
}
