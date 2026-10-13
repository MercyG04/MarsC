<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ClaimDocumentType;
use Illuminate\Validation\Rule;

class StoreClaimDocumentRequest extends FormRequest
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
                 'type' => [
                'required',
                Rule::enum(ClaimDocumentType::class),
            ],
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:10240',   // 10 MB
                ],
        ];
    }
public function messages(): array
    {
        return [
            'type.required' => 'Select the document type.',
            'type.enum'     => 'The selected document type is not valid.',
            'file.required' => 'Choose a file to upload.',
            'file.mimes'    => 'The file must be a PDF, image, or Word document.',
            'file.max'      => 'The file cannot exceed 10 MB.',
        ];
    }

}
