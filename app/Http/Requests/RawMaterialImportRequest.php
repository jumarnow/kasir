<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RawMaterialImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'import_file.required' => 'Pilih file Excel yang ingin diimport.',
            'import_file.file' => 'File yang diunggah tidak valid.',
            'import_file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV.',
            'import_file.max' => 'Ukuran file maksimal adalah 5MB.',
        ];
    }
}
