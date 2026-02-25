<?php

namespace App\Livewire\Dashboard;

use App\Models\Company;
use App\Models\VehicleQuotaRequest;
use App\Services\ExpiryMonitoringService;
use App\Services\VehicleInspectionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    public string $role = 'guest';

    public $companies = [];
    public ?int $selectedCompanyId = null;

    public int $pendingQuotaRequests = 0;
    public int $unreadNotifications = 0;
    public int $expiringDocumentsCount = 0;
    public int $inspectionPendingCount = 0;

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
            $this->pendingQuotaRequests = VehicleQuotaRequest::where('status', VehicleQuotaRequest::STATUS_PENDING)->count();
            $user = auth('web')->user();
            $this->unreadNotifications = $user ? (int) $user->unreadNotifications()->count() : 0;
            $this->expiringDocumentsCount = app(ExpiryMonitoringService::class)->countExpiringForAdmin();
        } elseif ($this->role === 'company') {
            $company = auth('company')->user();
            if ($company) {
                $this->expiringDocumentsCount = app(ExpiryMonitoringService::class)->countExpiringForCompany($company->id);
                $this->inspectionPendingCount = app(VehicleInspectionService::class)->getPendingCount($company);
            }
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
        if ($webUser && in_array($webUser->role ?? '', ['admin', 'super_admin'])) {
            return 'admin';
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
        $isCompany = $role === 'company';
        $link = $isCompany
            ? 'group flex items-center gap-3 px-3 py-3 rounded-2xl text-slate-400 hover:bg-sky-500/10 hover:text-sky-400 transition-colors'
            : 'group flex items-center gap-3 px-3 py-3 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800';
        $active = $isCompany
            ? 'group flex items-center gap-3 px-3 py-3 rounded-2xl bg-sky-500/20 text-sky-400 border border-sky-500/40'
            : 'group flex items-center gap-3 px-3 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900';
        $iconWrap = $isCompany
            ? 'w-9 h-9 rounded-xl bg-slate-700/50 flex items-center justify-center text-slate-400'
            : 'w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center';
        $iconWrapActive = $isCompany
            ? 'w-9 h-9 rounded-xl bg-sky-500/40 flex items-center justify-center text-sky-400'
            : 'w-9 h-9 rounded-xl bg-white/15 dark:bg-slate-900/10 flex items-center justify-center';
        $overviewHref = match ($role) {
            'admin' => route('admin.dashboard'),
            'company' => route('company.dashboard'),
            'driver' => route('driver.dashboard'),
            default => url('/'),
        };
        $overviewActive = match ($role) {
            'admin' => request()->routeIs('admin.dashboard'),
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
