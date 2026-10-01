<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
//use Illuminate\Contracts\Validation;

class StoreCancellationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
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
            'reason.required' => 'You must provide a reason for cancellation.',
            'reason.min'      => 'The reason must be at least 10 characters.',
        ];
    }
}