<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class Sidebar extends Component
{
    public string $role = 'admin';

    // للأدمن: قائمة الشركات
    public $companies = [];
    public ?int $selectedCompanyId = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->role = $user?->role ?? 'admin'; // admin|technician|company (حسب نظامك)

        // لو أدمن: اجلب الشركات
        if ($this->role === 'admin') {
            $this->companies = Company::query()
                ->select('id', 'company_name')
                ->orderBy('company_name')
                ->get()
                ->toArray();

            // اختيار شركة من session (اختياري)
            $this->selectedCompanyId = session('admin_company_id');
        }
    }

    public function setCompany(int $companyId): void
    {
        // حفظ اختيار الشركة للأدمن في session
        session(['admin_company_id' => $companyId]);
        $this->selectedCompanyId = $companyId;

        $this->dispatch('company-changed', companyId: $companyId);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.dashboard.sidebar');
    }
}
