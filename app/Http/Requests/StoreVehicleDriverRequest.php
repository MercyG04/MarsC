<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'national_id' => [
                'required',
                'string',
                'max:20',
            ],
            'dl_number' => [
                'required',
                'string',
                'max:30',
            ],
            'dl_class' => [
                'required',
                'string',
                'max:10',
            ],
            'date_of_birth' => [
                'required',
                'date',
                'before:today',
                'after:'.now()->subYears(100)->toDateString(),  // sanity floor
            ],
            'driving_experience' => [
                'required',
                'integer',
                'min:0',
                'max:80',
            ],
            'is_primary' => [
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'              => 'The driver\'s full name is required.',
            'national_id.required'       => 'National ID is required.',
            'dl_number.required'         => 'Driving licence number is required.',
            'dl_class.required'          => 'Driving licence class is required.',
            'date_of_birth.before'       => 'Date of birth must be in the past.',
            'date_of_birth.after'        => 'Date of birth is not plausible.',
            'driving_experience.min'     => 'Driving experience cannot be negative.',
            'driving_experience.max'     => 'Driving experience seems implausible.',
        ];
    }

    public function withValidator($validator): void
    {
    $validator->after(function ($validator) {
        $vehicle = $this->route('vehicle');

        if (! $vehicle) {
            return;
        }

        // PSV vehicles require PSV-licensed drivers
        if ($vehicle->vehicle_use === \App\Enums\VehicleUse::PSV
            && ! str_contains(strtoupper($this->input('dl_class', '')), 'D')) {
            $validator->errors()->add(
                'dl_class',
                'PSV vehicles require drivers with a PSV licence class (D).'
            );
        }
    });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'        => trim((string) $this->name),
            'national_id' => strtoupper(trim((string) $this->national_id)),
            'dl_number'   => strtoupper(trim((string) $this->dl_number)),
            'dl_class'    => strtoupper(trim((string) $this->dl_class)),
            'is_primary'  => $this->boolean('is_primary'),
        ]);
    }
}