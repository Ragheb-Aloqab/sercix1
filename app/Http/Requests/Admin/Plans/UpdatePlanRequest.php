<?php

namespace App\Http\Requests\Admin\Plans;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $planId = $this->route('plan')?->id;
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/', Rule::unique('subscription_plans', 'slug')->ignore($planId)],
            'tag' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_unit' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', Rule::in(\App\Models\SubscriptionPlan::FEATURES)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'features' => $this->input('features', []) ?: [],
        ]);
    }
}
