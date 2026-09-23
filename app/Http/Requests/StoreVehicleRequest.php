<?php

namespace App\Http\Requests;

use App\Enums\VehicleUse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    /**
     * Whether the user is allowed to make this request.
     * (Will be tightened once auth middleware is wired in.)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'registration_number' => [
                'required', 'string', 'max:20',
                Rule::unique('vehicles', 'registration_number')->whereNull('deleted_at'),
            ],
            'chassis_number' => [
                'required', 'string', 'max:50',
                Rule::unique('vehicles', 'chassis_number')->whereNull('deleted_at'),
            ],
            'logbook_number' => [
                'required', 'string', 'max:50',
                Rule::unique('vehicles', 'logbook_number')->whereNull('deleted_at'),
            ],
            'make'                => ['required', 'string', 'max:80'],
            'model'               => ['required', 'string', 'max:80'],
            'year_of_manufacture' => ['required', 'integer', 'min:1950', 'max:' . (date('Y') + 1)],
            'initial_estimated_value' => ['required', 'numeric', 'min:10000'],
            'vehicle_use'         => ['required', Rule::enum(VehicleUse::class)],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'registration_number.unique' => 'This registration number is already on file.',
            'chassis_number.unique'      => 'This chassis number is already on file.',
            'logbook_number.unique'      => 'This logbook number is already on file.',
            'year_of_manufacture.max'    => 'Year cannot be in the future.',
            'initial_estimated_value.min' => 'Declared value must be at least KES 10,000.',
        ];
    }

    /**
     * Prepare data for validation — normalize before rules run.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'registration_number' => strtoupper(trim((string) $this->registration_number)),
            'chassis_number'      => strtoupper(trim((string) $this->chassis_number)),
            'logbook_number'      => strtoupper(trim((string) $this->logbook_number)),
        ]);
    }
}