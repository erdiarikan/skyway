<?php

declare(strict_types=1);

namespace App\Data;

final readonly class LegData
{
    /**
     * @param  SegmentData[]  $segments
     */
    public function __construct(
        public string $origin,
        public string $destination,
        public array $segments,
    ) {}

    public static function fromArray(array $leg): self
    {
        $segments = array_map(
            static fn (array $segment): SegmentData => SegmentData::fromArray($segment),
            $leg['segments'],
        );

        return new self(
            origin: $segments[array_key_first($segments)]->origin,
            destination: $segments[array_key_last($segments)]->destination,
            segments: $segments,
        );
    }
}
