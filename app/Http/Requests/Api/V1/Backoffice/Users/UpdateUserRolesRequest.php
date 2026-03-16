<?php

namespace App\Http\Requests\Api\V1\Backoffice\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'add_roles' => array_values(array_unique($this->input('add_roles', []))),
            'remove_roles' => array_values(array_unique($this->input('remove_roles', []))),
        ]);
    }

    public function rules(): array
    {
        return [
            'add_roles' => ['nullable', 'array'],
            'add_roles.*' => ['string', Rule::in(['admin', 'staff', 'customer'])],
            'remove_roles' => ['nullable', 'array'],
            'remove_roles.*' => ['string', Rule::in(['admin', 'staff', 'customer'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $addRoles = $this->input('add_roles', []);
            $removeRoles = $this->input('remove_roles', []);

            if (empty($addRoles) && empty($removeRoles)) {
                $validator->errors()->add('roles', 'At least one role change is required.');
            }
        });
    }
}
