<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Flight\UpdateFlightAction;
use App\Data\FlightData;
use App\Models\Flight;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class UpdateFlightJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Flight $flight,
        public readonly FlightData $flightData,
    ) {}

    public function handle(UpdateFlightAction $action): void
    {
        $action->execute($this->flight, $this->flightData);
    }
}
