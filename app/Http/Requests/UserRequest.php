<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');
        $passwordRules = ['nullable', 'confirmed', 'min:6'];

        if ($this->isMethod('post')) {
            $passwordRules[0] = 'required';
        }

        return [
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($userId)],
            'password' => $passwordRules,
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
