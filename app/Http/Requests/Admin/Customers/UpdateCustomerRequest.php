<?php

namespace App\Http\Requests\Admin\Customers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:3000'],
           // 'status' => ['nullable', 'enum'],
            'status' => ['nullable', 'in:active,suspended'],        ];
    }

    protected function prepareForValidation(): void
{
    $this->merge([
        'status' => $this->has('status') ? 'active' : 'suspended',
    ]);
}
}
