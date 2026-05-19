<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Leg;
use App\Models\Segment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Segment>
 */
final class SegmentFactory extends Factory
{
    private const array AIRPORTS = ['BCN', 'LHR', 'JFK', 'CDG', 'AMS', 'FRA', 'MAD', 'DXB', 'SIN', 'IST'];

    private const array AIRLINES = ['UA', 'BA', 'IB', 'AF', 'LH', 'KL', 'EK', 'SQ', 'TK'];

    public function definition(): array
    {
        $departure = fake()->dateTimeBetween('+1 week', '+3 months');
        $arrival = (clone $departure)->modify('+'.fake()->numberBetween(1, 12).' hours');

        return [
            'leg_id' => Leg::factory(),
            'origin' => fake()->randomElement(self::AIRPORTS),
            'destination' => fake()->randomElement(self::AIRPORTS),
            'departure' => $departure,
            'arrival' => $arrival,
            'cabin_class' => fake()->randomElement(['Y', 'C', 'F', 'W']),
            'airline' => fake()->randomElement(self::AIRLINES),
            'flight_number' => (string) fake()->numberBetween(100, 999),
            'position' => 1,
        ];
    }
}
