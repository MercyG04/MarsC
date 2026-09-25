<?php

namespace App\Http\Requests;

use App\Enums\AddOnRateType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAddOnRequest extends FormRequest
{
    /**
     * Only admins may create add-ons.
     * Enforced at the route level too, but double-checked here.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z][a-z0-9_]*$/',   // snake_case, starts with a letter
                Rule::unique('add_ons', 'code')->whereNull('deleted_at'),
            ],

            'rate_type' => [
                'required',
                Rule::enum(AddOnRateType::class),
            ],

            'rate_value' => [
                'required',
                'numeric',
                'min:0',
                // Percentage add-ons can't exceed 100%
                Rule::when(
                    $this->input('rate_type') === AddOnRateType::Percentage->value,
                    ['max:100'],
                    []
                ),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex'  => 'The code must be lowercase snake_case and start with a letter (e.g. windshield, roadside_assist).',
            'code.unique' => 'An add-on with this code already exists.',
            'rate_value.max' => 'A percentage rate cannot exceed 100%.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'        => trim((string) $this->name),
            'code'        => strtolower(trim((string) $this->code)),
            'description' => $this->description ? trim((string) $this->description) : null,
            'is_active'   => $this->boolean('is_active', true),
        ]);
    }
}