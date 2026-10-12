<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRole;
use Override;
use Illuminate\Validation\Rule;

class AssignClaimRequest extends FormRequest
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
            'assigned_to' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', [
                        UserRole::ClaimsOfficer->value,
                        UserRole::Underwriter->value,
                        UserRole::Admin->value,
                    ]);
                }),
            ],
        ];
        
    }
    #[Override]
    public function messages():array
    {
        return [
            'assigned_to.required' => 'Please select a claims officer to handle this claim.',
            'assigned_to.exists'   => 'The selected user is not a claims officer or underwriter.',
        ];
        
    }
}
