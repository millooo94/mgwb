<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Concerns\NormalizesPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class PhoneVerificationConfirmRequest extends FormRequest
{
    use NormalizesPhoneNumber;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->normalizePhone($this->phone),
            'code' => is_string($this->code)
                ? trim($this->code)
                : $this->code,
        ]);
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:32'],
            'code' => ['required', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Il numero di telefono è obbligatorio.',
            'phone.string' => 'Il numero di telefono deve essere una stringa valida.',
            'phone.max' => 'Il numero di telefono non può superare i 32 caratteri.',

            'code.required' => 'Il codice è obbligatorio.',
            'code.digits' => 'Il codice deve contenere 6 cifre.',
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'numero di telefono',
            'code' => 'codice OTP',
        ];
    }
}
