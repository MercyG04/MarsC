<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Enums\PolicyStatus;
use App\Enums\PolicyType;
use App\Models\Policy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;



class StoreEndorsementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        
        {
        return $this->user() !== null;
        }

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'policy_type' => [
                'required',
                Rule::enum(PolicyType::class),
            ],

            'add_on_ids' => [
                'nullable',
                'array',
            ],

            'add_on_ids.*' => [
                'integer',
                Rule::exists('add_ons', 'id')->where('is_active', true),
            ],
            'driver_ids' => [
                'nullable',
                'array',
            ],

            'driver_ids.*' => [
                'integer',
                Rule::exists('vehicle_drivers', 'id')->whereNull('deleted_at'),
            ],

            'reason' => [
                'required',
                'string',
                'min:10',
                'max:500',
                ],
        ];
    }
    public function messages(): array
    {
        return [
            'policy_type.required'   => 'Select a cover type.',
            'add_on_ids.*.exists'    => 'One or more selected add-ons are no longer available.',
            'driver_ids.*.exists'    => 'One or more selected drivers are no longer on this vehicle.',
            'reason.required'        => 'You must provide a reason for the endorsement.',
            'reason.min'             => 'The reason must be at least 10 characters.',
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $policy = $this->route('policy');

            if (! $policy instanceof Policy) {
                return;
            }

            // Only active policies can be endorsed
            if ($policy->status !== PolicyStatus::Active) {
                $validator->errors()->add(
                    'policy_type',
                    'Only active policies can be endorsed.'
                );
                return;
            }

            // The endorsement must change at least one thing
            if (! $this->hasAnyChange($policy)) {
                $validator->errors()->add(
                    'policy_type',
                    'The endorsement must change at least one thing — cover type, add-ons, or drivers.'
                );
            }
        });
    }
    protected function hasAnyChange(Policy $policy): bool
    {
        // Policy type change
        if ($this->input('policy_type') !== $policy->policy_type->value) {
            return true;
        }

        // Add-on change
        if ($this->hasArrayChange(
            $this->input('add_on_ids', []),
            $policy->addOns->pluck('id')->toArray()
        )) {
            return true;
            }

        // Driver change
        if ($this->hasArrayChange(
            $this->input('driver_ids', []),
            $policy->vehicle->drivers->pluck('id')->toArray()
        )) {
            return true;
        }

        return false;
    }
    protected function hasArrayChange(array $new, array $old): bool
    {
        return collect($new)->sort()->values()->toArray()
            !== collect($old)->sort()->values()->toArray();
    }
}
