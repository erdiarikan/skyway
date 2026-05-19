<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CreateFlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'legs' => ['required', 'array', 'min:1'],
            'legs.*.segments' => ['required', 'array', 'min:1'],
            'legs.*.segments.*.origin' => ['required', 'string', 'size:3', 'alpha'],
            'legs.*.segments.*.destination' => ['required', 'string', 'size:3', 'alpha'],
            'legs.*.segments.*.departure' => ['required', 'date_format:Y-m-d\TH:i:s'],
            'legs.*.segments.*.arrival' => ['required', 'date_format:Y-m-d\TH:i:s'],
            'legs.*.segments.*.cabinClass' => ['required', 'string', 'max:2'],
            'legs.*.segments.*.airline' => ['required', 'string', 'size:2', 'alpha'],
            'legs.*.segments.*.flightNumber' => ['required', 'string', 'max:10'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('legs', []) as $legIndex => $leg) {
                foreach ($leg['segments'] ?? [] as $segmentIndex => $segment) {
                    if (isset($segment['departure'], $segment['arrival']) && $segment['arrival'] <= $segment['departure']) {
                        $validator->errors()->add(
                            "legs.{$legIndex}.segments.{$segmentIndex}.arrival",
                            'The arrival must be after the departure.'
                        );
                    }
                }
            }
        });
    }
}
