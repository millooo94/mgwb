<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nome' => is_string($this->nome)
                ? trim($this->nome)
                : $this->nome,

            'cognome' => is_string($this->cognome)
                ? trim($this->cognome)
                : $this->cognome,

            'email' => is_string($this->email)
                ? mb_strtolower(trim($this->email))
                : $this->email,
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cognome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:utenti,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Il nome è obbligatorio.',
            'nome.string' => 'Il nome deve essere una stringa valida.',
            'nome.max' => 'Il nome non può superare i 255 caratteri.',

            'cognome.required' => 'Il cognome è obbligatorio.',
            'cognome.string' => 'Il cognome deve essere una stringa valida.',
            'cognome.max' => 'Il cognome non può superare i 255 caratteri.',

            'email.required' => 'L\'email è obbligatoria.',
            'email.string' => 'L\'email deve essere una stringa valida.',
            'email.email' => 'Inserisci un indirizzo email valido.',
            'email.max' => 'L\'email non può superare i 255 caratteri.',
            'email.unique' => 'Questa email è già registrata.',

            'password.required' => 'La password è obbligatoria.',
            'password.string' => 'La password deve essere una stringa valida.',
            'password.min' => 'La password deve contenere almeno 8 caratteri.',
            'password.confirmed' => 'La conferma della password non coincide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'cognome' => 'cognome',
            'email' => 'email',
            'password' => 'password',
            'password_confirmation' => 'conferma password',
        ];
    }
}