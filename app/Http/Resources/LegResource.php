<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Leg;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Leg */
final class LegResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'segments' => SegmentResource::collection($this->segments),
        ];
    }
}
