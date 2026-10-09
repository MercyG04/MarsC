<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PolicyType;
use App\Enums\VehicleUse;
use Illuminate\Validation\Rule;
use Override;

class StoreQuotationRequest extends FormRequest
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
            'prospect_name' => [
                'required',
                'string',
                'max:150',
            ],
            'prospect_phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'prospect_email' => [
                'nullable',
                'email',
                'max:150',
            ],
            'client_id' => [
                'nullable',
                'integer',
                Rule::exists('clients', 'id')->whereNull('deleted_at'),
            ],
            'vehicle_details' => [
                'required',
                'array',
            ],
            'vehicle_details.make' => [
                'required',
                'string',
                'max:80',
            ],
            'vehicle_details.model' => [
                'required',
                'string',
                'max:80',
            ],
            'vehicle_details.year_of_manufacture' => [
                'required',
                'integer',
                'min:1800',
                'max:' . (date('Y') + 1),
            ],
            'vehicle_details.registration_number' => [
                'nullable',
                'string',
                'max:20',
            ],
            'vehicle_details.chassis_number' => [
                'nullable',
                'string',
                'max:50',
            ],
            'vehicle_details.estimated_value' => [
                'required',
                'numeric',
                'min:10000',
            ],
            'vehicle_details.vehicle_use' => [
                'required',
                Rule::enum(VehicleUse::class),
            ],
            'policy_type' => [
                'required',
                Rule::enum(PolicyType::class),
            ],

            // ─── Add-ons (optional) ───
            'selected_add_on_ids' => [
                'nullable',
                'array',
            ],
            'selected_add_on_ids.*' => [
                'integer',
                Rule::exists('add_ons', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

        ];
    }
    #[Override]
    public function messages():array
    {
        return [
            'prospect_name.required'                    => 'The prospect\'s name is required.',
            'prospect_email.email'                      => 'Please provide a valid email address.',
            'vehicle_details.make.required'             => 'The vehicle make is required.',
            'vehicle_details.model.required'            => 'The vehicle model is required.',
            'vehicle_details.year_of_manufacture.min'   => 'The year seems too far in the past.',
            'vehicle_details.year_of_manufacture.max'   => 'The year cannot be in the future.',
            'vehicle_details.estimated_value.min'       => 'The estimated value must be at least KES 10,000.',
            'vehicle_details.vehicle_use.required'      => 'Please select the vehicle use.',
            'policy_type.required'                      => 'Please select a cover type.',
            'selected_add_on_ids.*.exists'              => 'One or more selected add-ons are not available.',
        ];
    }
   
   
#[Override] 
   protected function prepareForValidation() : void
    {
       $vehicleDetails = $this->input('vehicle_details', []);

        
        if (is_array($vehicleDetails)) {
            $vehicleDetails['make'] = isset($vehicleDetails['make'])
                ? trim((string) $vehicleDetails['make'])
                : null;

            $vehicleDetails['model'] = isset($vehicleDetails['model'])
                ? trim((string) $vehicleDetails['model'])
                : null;
             $vehicleDetails['registration_number'] = isset($vehicleDetails['registration_number'])
                ? strtoupper(trim((string) $vehicleDetails['registration_number']))
                : null;

            $vehicleDetails['chassis_number'] = isset($vehicleDetails['chassis_number'])
                ? strtoupper(trim((string) $vehicleDetails['chassis_number']))
                : null;
        }
        $this->merge([
            'prospect_name'   => trim((string) $this->prospect_name),
            'prospect_phone'  => $this->prospect_phone ? trim((string) $this->prospect_phone) : null,
            'prospect_email'  => $this->prospect_email ? strtolower(trim((string) $this->prospect_email)) : null,
            'vehicle_details' => $vehicleDetails,
        ]);
    }    
    
}
