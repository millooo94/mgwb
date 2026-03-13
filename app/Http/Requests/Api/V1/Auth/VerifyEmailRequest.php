<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Models\Utente;
use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
            'hash' => $this->route('hash'),
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer'],
            'hash' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'ID utente mancante.',
            'id.integer' => 'ID utente non valido.',
            'hash.required' => 'Hash di verifica mancante.',
            'hash.string' => 'Hash di verifica non valido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'id utente',
            'hash' => 'hash di verifica',
        ];
    }

    public function verifiedUser(): ?Utente
    {
        $id = $this->validated('id');

        return Utente::find((int) $id);
    }

    public function routeHash(): string
    {
        return (string) $this->validated('hash');
    }
}