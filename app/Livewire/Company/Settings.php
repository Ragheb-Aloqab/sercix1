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
    }

    public function saveProfile()
    {
        $company = $this->company();
        abort_unless($company, 403);

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => [
                'required',
                'email',
                Rule::unique('companies', 'email')->ignore($company->id),
            ],
        ]);

        // حسب أسماء الأعمدة عندك
        if (isset($company->company_name)) {
            $company->company_name = $this->name;
        } else {
            $company->name = $this->name;
        }

        $company->email = $this->email;
        $company->save();

        session()->flash('success', 'تم تحديث بيانات الشركة ✅');
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

        session()->flash('success', 'تم تغيير كلمة المرور ✅');
    }

    public function render()
    {
        return view('livewire.company.settings')
            ->extends('admin.layouts.app') // نفس Layout لوحة التحكم
            ->section('content');
    }
}
