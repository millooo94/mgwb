<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Il token di reset è obbligatorio.',
            'token.string' => 'Il token di reset deve essere una stringa.',

            'email.required' => 'L\'email è obbligatoria.',
            'email.string' => 'L\'email deve essere una stringa.',
            'email.email' => 'Inserisci un indirizzo email valido.',

            'password.required' => 'La nuova password è obbligatoria.',
            'password.string' => 'La nuova password deve essere una stringa.',
            'password.min' => 'La nuova password deve contenere almeno 8 caratteri.',
            'password.confirmed' => 'La conferma della nuova password non corrisponde.',
        ];
    }

    public function attributes(): array
    {
        return [
            'token' => 'token di reset',
            'email' => 'email',
            'password' => 'nuova password',
            'password_confirmation' => 'conferma nuova password',
        ];
    }
}