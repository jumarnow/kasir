<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage_products') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'import_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:2048',
            ],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'import_file.required' => 'Silakan pilih file Excel untuk diunggah.',
            'import_file.mimes' => 'Format file harus .xlsx atau .xls.',
            'import_file.max' => 'Ukuran file maksimal 2MB.',
        ];
    }
}

