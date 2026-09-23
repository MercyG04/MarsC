<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // we'll wire auth middleware later
    }

    public function rules(): array
    {
        return [
            'national_id'        => ['required', 'string', 'max:20', Rule::unique('clients', 'national_id')],
            'dl_number'          => ['nullable', 'string', 'max:30'],
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'other_names'        => ['nullable', 'string', 'max:100'],
            'kra_pin'            => ['required', 'string', 'max:20'],
            'phone_number'       => ['required', 'string', 'max:20'],
            'email'              => ['required', 'email', 'max:150', Rule::unique('clients', 'email')],
            'date_of_birth'      => ['required', 'date', 'before:today'],
            'occupation'         => ['nullable', 'string', 'max:150'],
            'county'             => ['required', 'string', 'max:100'],
            'physical_location'  => ['required', 'string', 'max:200'],
            'gender'             => ['required', Rule::in(['male', 'female'])],
            'driving_experience' => ['required', 'integer', 'min:0', 'max:80'],
            'consent_given'      => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent_given.accepted' => 'You must obtain client consent before proceeding.',
        ];
    }
}