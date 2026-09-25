<?php

namespace App\Http\Requests;

use App\Enums\ValuationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreValuationRequest extends FormRequest
{
    /**
     * Only authenticated users may submit valuations.
     * Fine-grained role enforcement is applied at the route level.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        $vehicle = $this->route('vehicle');

        return [
            'valuation_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'valuation_firm' => [
                'required',
                'string',
                'max:150',
            ],

            'valuer_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'valuation_type' => [
                'required',
                Rule::enum(ValuationType::class),
                // Non-admins may not choose 'disputed' — that's an admin-only override.
                Rule::when(
                    ! $this->user()?->isAdmin(),
                    [Rule::notIn([ValuationType::Disputed->value])],
                    []
                ),
            ],

            'actual_cash_value' => [
                'required',
                'numeric',
                'min:1',
            ],

            'forced_sale_value' => [
                'nullable',
                'numeric',
                'min:0',
                'lte:actual_cash_value',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            // Optional PDF upload — the extraction is mocked for now
            'report' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240', // 10 MB
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'valuation_date.before_or_equal'  => 'A valuation date cannot be in the future.',
            'actual_cash_value.min'           => 'The actual cash value must be greater than zero.',
            'forced_sale_value.lte'           => 'The forced sale value cannot exceed the actual cash value.',
            'valuation_type.not_in'           => 'Only an admin can record a disputed valuation.',
            'report.mimes'                    => 'The valuation report must be a PDF.',
            'report.max'                      => 'The report PDF cannot exceed 10 MB.',
        ];
    }

    /**
     * Normalize and prepare data before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'valuation_firm' => trim((string) $this->valuation_firm),
            'valuer_name'    => $this->valuer_name ? trim((string) $this->valuer_name) : null,
            'notes'          => $this->notes ? trim((string) $this->notes) : null,
        ]);
    }

    /**
     * Fields allowed to be validated.
     * Excludes `vehicle_id`, `created_by`, and `report_path` — those
     * are set by the controller, never by the client.
     */
    protected function passedValidation(): void
    {
        // Nothing else needed — kept as an extension hook.
    }
}