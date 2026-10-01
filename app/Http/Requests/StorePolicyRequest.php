<?php

namespace App\Http\Requests;

use App\Enums\PaymentFrequency;
use App\Enums\PolicyStatus;
use App\Enums\PolicyType;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $vehicle = $this->route('vehicle');

        return [
            'policy_type' => [
                'required',
                Rule::enum(PolicyType::class),
            ],

            'payment_frequency' => [
                'required',
                Rule::enum(PaymentFrequency::class),
            ],

            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'add_on_ids' => [
                'nullable',
                'array',
            ],

            'add_on_ids.*' => [
                'integer',
                Rule::exists('add_ons', 'id')->where('is_active', true),
            ],

            'is_endorsement' => [
                'boolean',
            ],

            'endorsement_reason' => [
                'nullable',
                'string',
                'max:500',
                Rule::requiredIf($this->boolean('is_endorsement')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'policy_type.required' => 'Select a policy type.',
            'start_date.after_or_equal' => 'The start date cannot be in the past.',
            'add_on_ids.*.exists' => 'One or more selected add-ons are no longer available.',
            'endorsement_reason.required' => 'Provide a reason for the endorsement.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_endorsement' => $this->boolean('is_endorsement'),
        ]);
    }

    /**
     * Business rule: a vehicle cannot have two active policies.
     *
     * This is checked here rather than in rules() because it needs
     * the vehicle model, which is resolved by route binding.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $vehicle = $this->route('vehicle');

            if (! $vehicle instanceof Vehicle) {
                return;
            }

            // If not an endorsement, check no active policy exists
            if (! $this->boolean('is_endorsement')) {
                $activePolicy = $vehicle->policies()
                    ->where('status', PolicyStatus::Active->value)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($activePolicy) {
                    $validator->errors()->add(
                        'policy_type',
                        'This vehicle already has an active policy. Cancel or endorse the existing policy first.'
                    );
                }
            }
        });
    }
}