<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTechnicianRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id ?? null;

        return [
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190', "unique:users,email,{$userId}"],
            'phone' => ['nullable', 'string', 'max:30', "unique:users,phone,{$userId}"],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,suspended'],

        ];
    }
}
