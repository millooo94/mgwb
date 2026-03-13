<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Concerns\NormalizesPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class ChangePhoneConfirmRequest extends FormRequest
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
            'code' => is_string($this->code)
                ? trim($this->code)
                : $this->code,
        ]);
    }

    public function rules(): array
    {
        return [
            'new_phone' => ['required', 'string', 'max:32'],
            'code' => ['required', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_phone.required' => 'Il nuovo numero di telefono è obbligatorio.',
            'new_phone.string' => 'Il nuovo numero di telefono deve essere una stringa valida.',
            'new_phone.max' => 'Il nuovo numero di telefono non può superare i 32 caratteri.',

            'code.required' => 'Il codice è obbligatorio.',
            'code.digits' => 'Il codice deve contenere 6 cifre.',
        ];
    }

    public function attributes(): array
    {
        return [
            'new_phone' => 'nuovo numero di telefono',
            'code' => 'codice OTP',
        ];
    }
}
