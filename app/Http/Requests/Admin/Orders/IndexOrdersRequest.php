<?php

namespace App\Http\Requests\Admin\Orders;

use Illuminate\Foundation\Http\FormRequest;

class IndexOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string'],

            'company_id' => ['nullable', 'integer', 'exists:companies,id'],

            // لا يوجد branch_id في جدول orders الحالي
            // 'branch_id' => ['nullable', 'integer'],

            'technician_id' => ['nullable', 'integer', 'exists:users,id'],

            // هذا فلتر على payments.method وليس على orders
            'payment_method' => ['nullable', 'in:cash,tap'],

            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],

            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
