<?php

namespace App\Http\Requests\Admin\Customers;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'unique:companies,phone'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'in:active,suspended'],
            'vehicle_quota' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
{
    $this->merge([
        'status' => $this->has('status') ? 'active' : 'suspended',
    ]);
}
}
