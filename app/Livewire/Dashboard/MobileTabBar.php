<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class MobileTabBar extends Component
{
    public bool $showMoreModal = false;

    public const MAX_VISIBLE_TABS = 4;

    public function getRole(): string
    {
        // Order matters: company first, then web roles (prevents cross-role menu leakage)
        if (auth('company')->check()) {
            return 'company';
        }
        $user = auth('web')->user();
        return $user?->role ?? 'guest';
    }

    public function getNavItems(): array
    {
        $role = $this->getRole();
        $is = fn(string $pattern) => request()->routeIs($pattern);

        $overviewHref = match ($role) {
            'admin' => route('admin.dashboard'),
            'technician' => route('tech.dashboard'),
            'company' => route('company.dashboard'),
            default => url('/'),
        };

        return match ($role) {
            'admin' => [
                ['href' => $overviewHref, 'label' => __('dashboard.overview'), 'icon' => 'fa-chart-line', 'active' => $is('admin.dashboard')],
                ['href' => route('admin.orders.index'), 'label' => __('dashboard.orders'), 'icon' => 'fa-receipt', 'active' => $is('admin.orders.*')],
                ['href' => route('admin.services.index'), 'label' => __('dashboard.services'), 'icon' => 'fa-screwdriver-wrench', 'active' => $is('admin.services.*')],
                ['href' => route('admin.bank-transfers.index'), 'label' => __('dashboard.bank_transfers'), 'icon' => 'fa-landmark', 'active' => $is('admin.bank-transfers.*')],
                ['href' => route('admin.technicians.index'), 'label' => __('dashboard.technicians'), 'icon' => 'fa-user-gear', 'active' => $is('admin.technicians.*')],
                ['href' => route('admin.maps.technicians'), 'label' => __('dashboard.technicians_map'), 'icon' => 'fa-map-location-dot', 'active' => $is('admin.maps.technicians')],
                ['href' => route('admin.customers.index'), 'label' => __('dashboard.customers'), 'icon' => 'fa-users', 'active' => $is('admin.customers.*')],
                ['href' => route('admin.inventory.index'), 'label' => __('dashboard.inventory'), 'icon' => 'fa-boxes-stacked', 'active' => $is('admin.inventory.index')],
                ['href' => route('admin.inventory.movements'), 'label' => __('dashboard.movements'), 'icon' => 'fa-arrows-left-right', 'active' => $is('admin.inventory.movements')],
                ['href' => route('admin.activities.index'), 'label' => __('dashboard.activity_log'), 'icon' => 'fa-clock-rotate-left', 'active' => $is('admin.activities.*')],
                ['href' => route('admin.settings'), 'label' => __('dashboard.settings'), 'icon' => 'fa-gear', 'active' => $is('admin.settings')],
            ],
            'technician' => [
                ['href' => $overviewHref, 'label' => __('dashboard.overview'), 'icon' => 'fa-chart-line', 'active' => $is('tech.dashboard')],
                ['href' => route('tech.tasks.index'), 'label' => __('dashboard.tasks'), 'icon' => 'fa-list-check', 'active' => $is('tech.tasks.*')],
                ['href' => route('tech.settings'), 'label' => __('dashboard.settings'), 'icon' => 'fa-gear', 'active' => $is('tech.settings')],
            ],
            'company' => [
                ['href' => $overviewHref, 'label' => __('dashboard.overview'), 'icon' => 'fa-chart-line', 'active' => $is('company.dashboard')],
                ['href' => route('company.orders.index'), 'label' => __('dashboard.orders'), 'icon' => 'fa-receipt', 'active' => $is('company.orders.*')],
                ['href' => route('company.vehicles.index'), 'label' => __('dashboard.vehicles'), 'icon' => 'fa-car', 'active' => $is('company.vehicles.*')],
                ['href' => route('company.invoices.index'), 'label' => __('dashboard.invoices'), 'icon' => 'fa-file-invoice', 'active' => $is('company.invoices.*')],
                ['href' => route('company.reports.index'), 'label' => __('reports.all_reports'), 'icon' => 'fa-chart-pie', 'active' => $is('company.reports.index')],
                ['href' => route('company.fuel.index'), 'label' => __('reports.fuel_report'), 'icon' => 'fa-gas-pump', 'active' => $is('company.fuel.*')],
                ['href' => route('company.reports.service'), 'label' => __('reports.service_report'), 'icon' => 'fa-screwdriver-wrench', 'active' => $is('company.reports.service')],
                ['href' => route('company.branches.index'), 'label' => __('dashboard.branches'), 'icon' => 'fa-code-branch', 'active' => $is('company.branches.*')],
                ['href' => route('company.settings'), 'label' => __('dashboard.settings'), 'icon' => 'fa-gear', 'active' => $is('company.settings')],
            ],
            default => [],
        };
    }

    public function getVisibleTabs(): array
    {
        $items = $this->getNavItems();
        return array_slice($items, 0, self::MAX_VISIBLE_TABS);
    }

    public function getMoreItems(): array
    {
        $items = $this->getNavItems();
        return array_slice($items, self::MAX_VISIBLE_TABS);
    }

    public function toggleMoreModal(): void
    {
        $this->showMoreModal = !$this->showMoreModal;
    }

    public function closeMoreModal(): void
    {
        $this->showMoreModal = false;
    }

    public function render()
    {
        $visibleTabs = $this->getVisibleTabs();
        $moreItems = $this->getMoreItems();
        $hasMore = count($moreItems) > 0;
        $hasNav = count($visibleTabs) > 0 || $hasMore;

        return view('livewire.dashboard.mobile-tab-bar', [
            'visibleTabs' => $visibleTabs,
            'moreItems' => $moreItems,
            'hasMore' => $hasMore,
            'hasNav' => $hasNav,
            'role' => $this->getRole(),
        ]);
    }
}
