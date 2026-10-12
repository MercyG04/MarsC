<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class MarkClaimPaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Admin only — this is a financial commitment.
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}