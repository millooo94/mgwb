<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'La password è obbligatoria.',
            'password.string' => 'La password deve essere una stringa valida.',
            'password.min' => 'La password deve contenere almeno 8 caratteri.',
            'password.confirmed' => 'La conferma della password non coincide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'password' => 'password',
            'password_confirmation' => 'conferma password',
        ];
    }
}
