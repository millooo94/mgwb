<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginFacebookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'access_token' => is_string($this->access_token)
                ? trim($this->access_token)
                : $this->access_token,
        ]);
    }

    public function rules(): array
    {
        return [
            'access_token' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'access_token.required' => 'Il token Facebook è obbligatorio.',
            'access_token.string' => 'Il token Facebook deve essere una stringa valida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'access_token' => 'token Facebook',
        ];
    }
}
