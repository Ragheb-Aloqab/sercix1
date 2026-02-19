<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class Sidebar extends Component
{
    public string $role = 'guest';

    public $companies = [];
    public ?int $selectedCompanyId = null;

    public function mount(): void
    {
        $this->role = $this->resolveRole();

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

    public function isActive(string $pattern): bool
    {
        return request()->routeIs($pattern);
    }

    /**
     * Resolve the current user's role from auth state.
     * Order matters: company guard first, then web roles, then driver session.
     * Used in both mount() and render() so the public $role property and view data stay in sync.
     */
    private function resolveRole(): string
    {
        if (auth('company')->check()) {
            return 'company';
        }
        $webUser = auth('web')->user();
        if ($webUser && ($webUser->role ?? '') === 'admin') {
            return 'admin';
        }
        if ($webUser && ($webUser->role ?? '') === 'technician') {
            return 'technician';
        }
        if (session()->has('driver_phone')) {
            return 'driver';
        }
        return 'guest';
    }

    public function render()
    {
        $isCompany = auth('company')->check();
        $companyUser = auth('company')->user();
        $webUser = auth('web')->user();
        $role = $this->resolveRole();
        $link = 'group flex items-center gap-3 px-3 py-3 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800';
        $active = 'group flex items-center gap-3 px-3 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900';
        $iconWrap = 'w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center';
        $iconWrapActive = 'w-9 h-9 rounded-xl bg-white/15 dark:bg-slate-900/10 flex items-center justify-center';
        $overviewHref = match ($role) {
            'admin' => route('admin.dashboard'),
            'technician' => route('tech.dashboard'),
            'company' => route('company.dashboard'),
            'driver' => route('driver.dashboard'),
            default => url('/'),
        };
        $overviewActive = match ($role) {
            'admin' => request()->routeIs('admin.dashboard'),
            'technician' => request()->routeIs('tech.dashboard'),
            'company' => request()->routeIs('company.dashboard'),
            'driver' => request()->routeIs('driver.dashboard'),
            default => false,
        };
        $displayName = match (true) {
            $isCompany => $companyUser->company_name ?? 'Company',
            $role === 'driver' => __('driver.driver'),
            default => $webUser?->name ?? 'User',
        };
        $displayEmail = $isCompany ? ($companyUser->email ?? '') : ($webUser?->email ?? '');
        $avatarLetter = strtoupper(substr($displayName, 0, 1));

        return view('livewire.dashboard.sidebar', [
            'isCompany' => $isCompany,
            'companyUser' => $companyUser,
            'webUser' => $webUser,
            'role' => $role,
            'link' => $link,
            'active' => $active,
            'iconWrap' => $iconWrap,
            'iconWrapActive' => $iconWrapActive,
            'overviewHref' => $overviewHref,
            'overviewActive' => $overviewActive,
            'displayName' => $displayName,
            'displayEmail' => $displayEmail,
            'avatarLetter' => $avatarLetter,
        ]);
    }
}
