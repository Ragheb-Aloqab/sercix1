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
        // Order matters: company, maintenance_center, then web roles
        if (auth('company')->check()) {
            return 'company';
        }
        if (auth('maintenance_center')->check()) {
            return 'maintenance_center';
        }
        $user = auth('web')->user();
        return $user?->role ?? 'guest';
    }

    public function getNavItems(): array
    {
        $role = $this->getRole();
        $is = fn(string $pattern) => request()->routeIs($pattern);

        $overviewHref = match ($role) {
            'admin', 'super_admin' => route('admin.dashboard'),
            'company' => route('company.dashboard'),
            'maintenance_center' => route('maintenance-center.dashboard'),
            default => url('/'),
        };

        $adminItems = [
            ['href' => $overviewHref, 'label' => __('dashboard.overview'), 'icon' => 'fa-chart-line', 'active' => $is('admin.dashboard')],
            ['href' => route('admin.companies.index'), 'label' => __('admin_dashboard.companies_overview'), 'icon' => 'fa-building', 'active' => $is('admin.companies.*')],
            ['href' => route('admin.vehicles.index'), 'label' => __('admin_dashboard.vehicles_overview'), 'icon' => 'fa-car', 'active' => $is('admin.vehicles.index')],
            ['href' => route('admin.orders.index'), 'label' => __('dashboard.orders'), 'icon' => 'fa-receipt', 'active' => $is('admin.orders.*')],
            ['href' => route('admin.services.index'), 'label' => __('dashboard.services'), 'icon' => 'fa-screwdriver-wrench', 'active' => $is('admin.services.*')],
            ['href' => route('admin.customers.index'), 'label' => __('dashboard.customers'), 'icon' => 'fa-users', 'active' => $is('admin.customers.*')],
            ['href' => route('admin.activities.index'), 'label' => __('dashboard.activity_log'), 'icon' => 'fa-clock-rotate-left', 'active' => $is('admin.activities.*')],
            ['href' => route('admin.notifications.index'), 'label' => __('common.notifications'), 'icon' => 'fa-bell', 'active' => $is('admin.notifications.*')],
            ['href' => route('admin.settings'), 'label' => __('dashboard.settings'), 'icon' => 'fa-gear', 'active' => $is('admin.settings')],
        ];
        if (config('servx.payments_enabled', false)) {
            $adminItems[] = ['href' => route('admin.bank-transfers.index'), 'label' => __('dashboard.bank_transfers'), 'icon' => 'fa-landmark', 'active' => $is('admin.bank-transfers.*')];
        }

        $maintenanceCenterItems = [
            ['href' => $overviewHref, 'label' => __('maintenance.assigned_rfqs'), 'icon' => 'fa-warehouse', 'active' => $is('maintenance-center.dashboard')],
            ['href' => route('maintenance-center.history.index'), 'label' => __('maintenance.history'), 'icon' => 'fa-clock-rotate-left', 'active' => $is('maintenance-center.history.*')],
        ];

        $companyItems = [
            ['href' => $overviewHref, 'label' => __('fleet.dashboard'), 'icon' => 'fa-chart-line', 'active' => $is('company.dashboard')],
            ['href' => route('company.vehicles.index'), 'label' => __('fleet.my_vehicles'), 'icon' => 'fa-car', 'active' => $is('company.vehicles.*')],
            ['href' => route('company.maintenance-requests.index'), 'label' => __('fleet.maintenance_requests'), 'icon' => 'fa-screwdriver-wrench', 'active' => $is('company.maintenance-requests.*')],
            ['href' => route('company.fuel-balance'), 'label' => __('fleet.fuel'), 'icon' => 'fa-gas-pump', 'active' => $is('company.fuel-balance')],
            ['href' => route('company.tracking.index'), 'label' => __('fleet.tracking'), 'icon' => 'fa-location-dot', 'active' => $is('company.tracking.*')],
            ['href' => route('company.reports.index'), 'label' => __('fleet.reports'), 'icon' => 'fa-chart-pie', 'active' => $is('company.reports.*')],
            ['href' => route('company.settings'), 'label' => __('fleet.settings'), 'icon' => 'fa-gear', 'active' => $is('company.settings')],
        ];

        return match ($role) {
            'admin', 'super_admin' => $adminItems,
            'maintenance_center' => $maintenanceCenterItems,
            'company' => $companyItems,
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
