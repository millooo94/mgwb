<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginGoogleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id_token' => is_string($this->id_token)
                ? trim($this->id_token)
                : $this->id_token,
        ]);
    }

    public function rules(): array
    {
        return [
            'id_token' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_token.required' => 'Il token Google è obbligatorio.',
            'id_token.string' => 'Il token Google deve essere una stringa valida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_token' => 'token Google',
        ];
    }
}
