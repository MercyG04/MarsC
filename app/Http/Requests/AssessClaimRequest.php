<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use Override;

class AssessClaimRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(
            UserRole::Admin,
            UserRole::Underwriter,
            UserRole::ClaimsOfficer,
        ) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'assessor_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'assessed_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'adjustment_type' => [
                'required',
                Rule::in(['repair', 'write_off', 'cash_in_lieu', 'total_loss']),
            ],
            'salvage_value' => [
                'nullable',
                'numeric',
                'min:0',
                'lte:assessed_amount',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
    #[Override]
    public function messages():array
    {
        return [
            'assessed_amount.required'   => 'The assessed amount is required.',
            'assessed_amount.min'        => 'The assessed amount cannot be negative.',
            'adjustment_type.required'   => 'Please select the type of adjustment.',
            'salvage_value.lte'          => 'The salvage value cannot exceed the assessed amount.',
            'adjustment_type.in'         => 'The adjustment type must be repair, write-off, cash-in-lieu, or total loss.',
        ];
    }
    protected function prepareForValidation():void
    {
        if ($this->has('adjustment_type')) {
            $this->merge([
                'adjustment_type' => strtolower(trim((string) $this->adjustment_type)),
            ]);
        }
    }
}
