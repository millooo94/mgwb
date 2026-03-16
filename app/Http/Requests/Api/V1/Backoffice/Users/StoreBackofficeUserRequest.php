<?php

namespace App\Http\Requests\Api\V1\Backoffice\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBackofficeUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'stato' => $this->input('stato', 1),
            'assign_customer_role' => (bool) $this->input('assign_customer_role', false),
            'email_verified' => (bool) $this->input('email_verified', true),
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'cognome' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:150', 'unique:utenti,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'stato' => ['required', 'integer', Rule::in([1, 3])],
            'role' => ['required', 'string', Rule::in(['admin', 'staff'])],
            'assign_customer_role' => ['required', 'boolean'],
            'email_verified' => ['required', 'boolean'],
        ];
    }
}
