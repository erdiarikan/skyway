<?php

declare(strict_types=1);

use App\Http\Controllers\FlightController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function (): void {
    Route::post('flights', [FlightController::class, 'store'])->middleware('idempotency');
    Route::get('flights/{flight}', [FlightController::class, 'show']);
    Route::put('flights/{flight}', [FlightController::class, 'update'])->middleware('idempotency');
});
