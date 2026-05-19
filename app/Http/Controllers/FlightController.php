<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Flight\CreateFlightAction;
use App\Http\Requests\CreateFlightRequest;
use App\Http\Requests\UpdateFlightRequest;
use App\Http\Resources\FlightResource;
use App\Jobs\UpdateFlightJob;
use App\Models\Flight;
use Illuminate\Http\JsonResponse;

final class FlightController extends Controller
{
    public function store(CreateFlightRequest $request, CreateFlightAction $action): JsonResponse
    {
        $flight = $action->execute($request->toData());
        $flight->load('legs.segments');

        return (new FlightResource($flight))
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    public function show(Flight $flight): FlightResource
    {
        $flight->load('legs.segments');

        return new FlightResource($flight);
    }

    public function update(UpdateFlightRequest $request, Flight $flight): JsonResponse
    {
        UpdateFlightJob::dispatch($flight, $request->toData());

        return new JsonResponse(['flightId' => $flight->uuid], JsonResponse::HTTP_ACCEPTED);
    }
}
