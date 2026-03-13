<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Concerns\NormalizesPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class ChangePhoneStartRequest extends FormRequest
{
    use NormalizesPhoneNumber;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'new_phone' => $this->normalizePhone($this->new_phone),
            'current_password' => is_string($this->current_password)
                ? trim($this->current_password)
                : $this->current_password,
        ]);
    }

    public function rules(): array
    {
        return [
            'new_phone' => ['required', 'string', 'max:32'],
            'current_password' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_phone.required' => 'Il nuovo numero di telefono è obbligatorio.',
            'new_phone.string' => 'Il nuovo numero di telefono deve essere una stringa valida.',
            'new_phone.max' => 'Il nuovo numero di telefono non può superare i 32 caratteri.',

            'current_password.string' => 'La password attuale deve essere una stringa valida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'new_phone' => 'nuovo numero di telefono',
            'current_password' => 'password attuale',
        ];
    }
}
