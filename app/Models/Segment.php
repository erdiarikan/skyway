<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SegmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Segment extends Model
{
    /** @use HasFactory<SegmentFactory> */
    use HasFactory;

    protected $fillable = [
        'leg_id',
        'origin',
        'destination',
        'departure',
        'arrival',
        'cabin_class',
        'airline',
        'flight_number',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'departure' => 'datetime',
            'arrival' => 'datetime',
        ];
    }

    public function leg(): BelongsTo
    {
        return $this->belongsTo(Leg::class);
    }
}
