<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => is_string($this->email)
                ? mb_strtolower(trim($this->email))
                : $this->email,
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L’email è obbligatoria.',
            'email.string' => 'L’email deve essere una stringa.',
            'email.email' => 'Inserisci un indirizzo email valido.',

            'password.required' => 'La password è obbligatoria.',
            'password.string' => 'La password deve essere una stringa.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'email',
            'password' => 'password',
        ];
    }
}