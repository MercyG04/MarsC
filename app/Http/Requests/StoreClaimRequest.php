<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Enums\ClaimType;
use App\Models\Policy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClaimRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        
            return [
            // ─── Incident details ───
            'incident_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:' . now()->subYear()->toDateString(),
            ],
            'description' => [
                'required',
                'string',
                'min:20',
                'max:2000',
            ],
            'location' => [
                'nullable',
                'string',
                'max:200',
            ],
            'police_abstract_ref' => [
                'nullable',
                'string',
                'max:100',
            ],
            'claim_type' => [
                'required',
                Rule::enum(ClaimType::class),
            ],

            // ─── Financial ───
            'claimed_amount' => [
                'required',
                'numeric',
                'min:1000',
            ],
            'driver_id' => [
                'nullable',
                'integer',
                Rule::exists('vehicle_drivers', 'id')
                    ->whereNull('deleted_at'),
            ],

            
        ];
    }
    public function messages(): array
    {
        return [
            'incident_date.before_or_equal' => 'The incident date cannot be in the future.',
            'incident_date.after'           => 'Claims must be filed within one year of the incident.',
            'description.min'               => 'Please describe the incident in at least 20 characters.',
            'claimed_amount.min'            => 'The claimed amount must be at least KES 1,000.',
            'driver_id.exists'              => 'The selected driver is no longer on this vehicle.',
            'documents.*.mimes'             => 'Documents must be PDF, image, or Word files.',
            'documents.*.max'               => 'Each file cannot exceed 10 MB.',
        ];
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'description'          => trim((string) $this->description),
            'location'             => $this->location ? trim((string) $this->location) : null,
            'police_abstract_ref'  => $this->police_abstract_ref
                ? strtoupper(trim((string) $this->police_abstract_ref))
                : null,
            'claim_type'           => $this->claim_type ? strtolower(trim((string) $this->claim_type)) : null,
        ]);
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            if ($validator->errors()->isNotEmpty()) {
                return;   // don't pile on if basic rules failed
            }

            /** @var Policy|null $policy */
            $policy = $this->route('policy');
            if (! $policy) {
                return;
            }

            $incidentDate = $this->input('incident_date');

            // 1. The incident must fall within the policy period
            if ($incidentDate) {
                if ($incidentDate < $policy->start_date->toDateString()) {
                    $validator->errors()->add(
                        'incident_date',
                        'The incident predates the policy start date.'
                    );
                }
            if ($incidentDate > $policy->end_date->toDateString()) {
                    $validator->errors()->add(
                        'incident_date',
                        'The incident occurred after the policy end date.'
                    );
                }
            }

            // 2. If a driver is provided, they must be authorized for this vehicle
            $driverId = $this->input('driver_id');

            if ($driverId) {
                $belongsToVehicle = $policy->vehicle
                    ->drivers()
                    ->where('id', $driverId)
                    ->exists(); 
            if (! $belongsToVehicle) {
                    $validator->errors()->add(
                        'driver_id',
                        'The selected driver is not authorized for this vehicle.'
                    );
                }
            }

        });
    }           
}
