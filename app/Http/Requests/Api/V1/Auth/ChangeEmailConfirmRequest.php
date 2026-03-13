<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangeEmailConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'token' => is_string($this->token)
                ? trim($this->token)
                : $this->token,
        ]);
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Il token di conferma è obbligatorio.',
            'token.string' => 'Il token di conferma deve essere una stringa valida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'token' => 'token di conferma',
        ];
    }
}
