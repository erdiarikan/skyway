<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FlightFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

final class Flight extends Model
{
    /** @use HasFactory<FlightFactory> */
    use HasFactory;

    protected $fillable = [];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** @return HasMany<Leg, $this> */
    public function legs(): HasMany
    {
        return $this->hasMany(Leg::class)->orderBy('position');
    }

    protected static function booted(): void
    {
        self::creating(function (self $flight): void {
            $flight->uuid = (string) Str::uuid();
        });
    }
}
