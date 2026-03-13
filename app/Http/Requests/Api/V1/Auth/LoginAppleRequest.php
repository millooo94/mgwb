<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginAppleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'identity_token' => is_string($this->identity_token)
                ? trim($this->identity_token)
                : $this->identity_token,

            'authorization_code' => is_string($this->authorization_code)
                ? trim($this->authorization_code)
                : $this->authorization_code,

            'nonce' => is_string($this->nonce)
                ? trim($this->nonce)
                : $this->nonce,

            'given_name' => is_string($this->given_name)
                ? trim($this->given_name)
                : $this->given_name,

            'family_name' => is_string($this->family_name)
                ? trim($this->family_name)
                : $this->family_name,
        ]);
    }

    public function rules(): array
    {
        return [
            'identity_token' => ['required', 'string'],
            'authorization_code' => ['required', 'string'],
            'nonce' => ['nullable', 'string'],
            'given_name' => ['nullable', 'string', 'max:255'],
            'family_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'identity_token.required' => 'Il token Apple è obbligatorio.',
            'identity_token.string' => 'Il token Apple deve essere una stringa valida.',

            'authorization_code.required' => 'Il codice di autorizzazione Apple è obbligatorio.',
            'authorization_code.string' => 'Il codice di autorizzazione Apple deve essere una stringa valida.',

            'nonce.string' => 'Il nonce deve essere una stringa valida.',
            'given_name.string' => 'Il nome deve essere una stringa valida.',
            'given_name.max' => 'Il nome non può superare i 255 caratteri.',
            'family_name.string' => 'Il cognome deve essere una stringa valida.',
            'family_name.max' => 'Il cognome non può superare i 255 caratteri.',
        ];
    }

    public function attributes(): array
    {
        return [
            'identity_token' => 'token Apple',
            'authorization_code' => 'codice Apple',
            'nonce' => 'nonce',
            'given_name' => 'nome',
            'family_name' => 'cognome',
        ];
    }
}
