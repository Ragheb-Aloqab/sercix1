<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class Sidebar extends Component
{
    public string $role = 'admin';

    public $companies = [];
    public ?int $selectedCompanyId = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->role = $user?->role ?? 'admin'; 

        if ($this->role === 'admin') {
            $this->companies = Company::query()
                ->select('id', 'company_name')
                ->orderBy('company_name')
                ->get()
                ->toArray();
            $this->selectedCompanyId = session('admin_company_id');
        }
    }

    public function setCompany(int $companyId): void
    {
        session(['admin_company_id' => $companyId]);
        $this->selectedCompanyId = $companyId;

        $this->dispatch('company-changed', companyId: $companyId);
    }

    public function render()
    {
        return view('livewire.dashboard.sidebar');
    }
}
