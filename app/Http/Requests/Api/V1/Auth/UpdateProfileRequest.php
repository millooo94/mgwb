<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
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
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'cognome' => ['required', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Il nome è obbligatorio.',
            'nome.string' => 'Il nome deve essere una stringa valida.',
            'nome.max' => 'Il nome non può superare i 150 caratteri.',

            'cognome.required' => 'Il cognome è obbligatorio.',
            'cognome.string' => 'Il cognome deve essere una stringa valida.',
            'cognome.max' => 'Il cognome non può superare i 150 caratteri.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'cognome' => 'cognome',
        ];
    }
}
