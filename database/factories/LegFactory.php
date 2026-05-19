<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Flight;
use App\Models\Leg;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Leg>
 */
final class LegFactory extends Factory
{
    private const array AIRPORTS = ['BCN', 'LHR', 'JFK', 'CDG', 'AMS', 'FRA', 'MAD', 'DXB', 'SIN', 'IST'];

    public function definition(): array
    {
        return [
            'flight_id' => Flight::factory(),
            'origin' => fake()->randomElement(self::AIRPORTS),
            'destination' => fake()->randomElement(self::AIRPORTS),
            'position' => 1,
        ];
    }
}
