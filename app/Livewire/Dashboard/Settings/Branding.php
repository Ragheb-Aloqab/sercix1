<?php

namespace App\Livewire\Dashboard\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Branding extends Component
{
    public string $site_name = 'SERV.X';
    public ?string $company_name = null;
    public ?string $email = null;
    public ?string $phone = null;

    public function mount(): void
    {
        $user = Auth::user();

        // Admin/Technician من جدول users
        if ($user) {
            $this->email = $user->email;
        }

        // لو كنت تدخل الشركة بـ guard ثاني (company) خليها هنا
        // $company = auth('company')->user();
        // if ($company) { ... }

        // أو لو الشركة مربوطة مع المستخدم (اختياري)
        // $this->company_name = $user?->company?->company_name;
    }

    public function save(): void
    {
        // هنا فقط مثال، انت لاحقاً اربطه بجدول settings أو companies حسب دور المستخدم
        session()->flash('success', 'تم حفظ الإعدادات بنجاح.');
    }

    public function render()
    {
        return view('livewire.dashboard.settings.branding');
    }
}
