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
            'technician_id' => ['nullable', 'integer', 'exists:users,id'],

            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],

            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
