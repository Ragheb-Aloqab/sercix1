<?php

namespace App\Livewire\Company;

use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Settings extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    private function company()
    {
        return Auth::guard('company')->user();
    }

    public function mount()
    {
        $company = $this->company();
        abort_unless($company, 403);

        $this->name  = (string) ($company->company_name ?? $company->name ?? '');
        $this->email = (string) ($company->email ?? '');
        $this->phone = (string) ($company->phone ?? '');
    }

    public function saveProfile()
    {
        $company = $this->company();
        abort_unless($company, 403);

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => [
                'nullable',
                'email',
                Rule::unique('companies', 'email')->ignore($company->id),
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique('companies', 'phone')->ignore($company->id),
            ],
        ]);

        if (isset($company->company_name)) {
            $company->company_name = $this->name;
        } else {
            $company->name = $this->name;
        }

        $company->email = $this->email ?: null;
        $company->phone = $this->phone;
        $company->save();

        session()->flash('success', 'تم تحديث بيانات الشركة ');
    }

    public function changePassword()
    {
        $company = $this->company();
        abort_unless($company, 403);

        $this->validate([
            'current_password' => ['required', 'current_password:company'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $company->password = Hash::make($this->password);
        $company->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('success', 'تم تغيير كلمة المرور ');
    }

    public function render()
    {
        return view('livewire.company.settings')
            ->extends('admin.layouts.app') 
            ->section('content');
    }
}
