<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use illuminate\Validation\Rule;
use App\Enums\UserRole;
use App\Enums\RejectionReason;

class RejectClaimRequest extends FormRequest
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
            'rejection_reason' => [
                'required',
                Rule::enum(RejectionReason::class),
            ],
            'rejection_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Please select a reason for rejection.',
            'rejection_reason.enum'     => 'The selected reason is not valid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $reason = RejectionReason::tryFrom($this->input('rejection_reason', ''));

            if (! $reason) {
                return;
            }

            // Fraud-based rejections require evidence in the notes
            if ($reason->requiresEvidence() && empty(trim((string) $this->input('rejection_notes')))) {
                $validator->errors()->add(
                    'rejection_notes',
                    'Fraud-based rejections require documented evidence in the notes field.'
                );
            }
        });
    }    

}
