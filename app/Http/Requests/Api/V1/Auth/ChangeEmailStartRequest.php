<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeEmailStartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'new_email' => is_string($this->new_email)
                ? mb_strtolower(trim($this->new_email))
                : $this->new_email,

            'current_password' => is_string($this->current_password)
                ? trim($this->current_password)
                : $this->current_password,
        ]);
    }

    public function rules(): array
    {
        return [
            'new_email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('utenti', 'email')->ignore($this->user()?->id),
            ],
            'current_password' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_email.required' => 'La nuova email è obbligatoria.',
            'new_email.string' => 'La nuova email deve essere una stringa valida.',
            'new_email.email' => 'Inserisci un indirizzo email valido.',
            'new_email.max' => 'La nuova email non può superare i 150 caratteri.',
            'new_email.unique' => 'Questa email è già utilizzata da un altro account.',

            'current_password.string' => 'La password attuale deve essere una stringa valida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'new_email' => 'nuova email',
            'current_password' => 'password attuale',
        ];
    }
}
