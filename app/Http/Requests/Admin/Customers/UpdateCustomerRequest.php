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
        $companyId = $this->route('customer')?->id;
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'in:active,suspended'],
            'plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'vehicle_quota' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'white_label_enabled' => ['nullable', 'boolean'],
            'subdomain' => ['nullable', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/', 'unique:companies,subdomain,' . $companyId],
            'primary_color' => ['nullable', 'string', 'max:20', 'regex:/^#([0-9A-Fa-f]{3}){1,2}$/'],
            'secondary_color' => ['nullable', 'string', 'max:20', 'regex:/^#([0-9A-Fa-f]{3}){1,2}$/'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status') ? 'active' : 'suspended',
            'white_label_enabled' => $this->boolean('white_label_enabled'),
        ]);
    }
}
