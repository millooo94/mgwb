<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'old_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'different:old_password',
                'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required' => 'La password attuale è obbligatoria.',
            'old_password.string' => 'La password attuale deve essere una stringa.',

            'new_password.required' => 'La nuova password è obbligatoria.',
            'new_password.string' => 'La nuova password deve essere una stringa.',
            'new_password.min' => 'La nuova password deve contenere almeno 8 caratteri.',
            'new_password.different' => 'La nuova password deve essere diversa da quella attuale.',
            'new_password.confirmed' => 'La conferma della nuova password non corrisponde.',
        ];
    }

    public function attributes(): array
    {
        return [
            'old_password' => 'password attuale',
            'new_password' => 'nuova password',
            'new_password_confirmation' => 'conferma nuova password',
        ];
    }
}
