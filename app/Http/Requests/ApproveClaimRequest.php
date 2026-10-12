<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRole;

class ApproveClaimRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(
            UserRole::Admin,
            UserRole::Underwriter,
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
            'approved_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'salvage_value' => [
                'nullable',
                'numeric',
                'min:0',
                'lte:approved_amount',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'approved_amount.required' => 'The approved amount is required.',
            'salvage_value.lte'        => 'The salvage value cannot exceed the approved amount.',
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $claim = $this->route('claim');

            if (! $claim) {
                return;
            }
            if ($claim->assessed_amount && $this->input('approved_amount') > $claim->assessed_amount) {
                $validator->errors()->add(
                    'approved_amount',
                    'The approved amount cannot exceed the assessed amount of KES ' . number_format($claim->assessed_amount, 2) . '.'
                );
            }

            // Approved must exceed the deductible
            $deductible = (float) $claim->policy->deductible_amount;

            if ($this->input('approved_amount') <= $deductible) {
                $validator->errors()->add(
                    'approved_amount',
                    'The approved amount must exceed the policy deductible of KES ' . number_format($deductible, 2) . '.'
                );
            }
        });
    }
}
