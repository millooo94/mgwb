<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Concerns\NormalizesPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class AccountRecoveryStartRequest extends FormRequest
{
    use NormalizesPhoneNumber;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->normalizePhone($this->phone),
        ]);
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:32'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Il numero di telefono è obbligatorio.',
            'phone.string' => 'Il numero di telefono deve essere una stringa valida.',
            'phone.max' => 'Il numero di telefono non può superare i 32 caratteri.',
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'numero di telefono',
        ];
    }
}
