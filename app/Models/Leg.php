<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LegFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Leg extends Model
{
    /** @use HasFactory<LegFactory> */
    use HasFactory;

    protected $fillable = ['flight_id', 'origin', 'destination', 'position'];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function segments(): HasMany
    {
        return $this->hasMany(Segment::class)->orderBy('position');
    }
}
