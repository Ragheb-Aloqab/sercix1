{{-- Company nav links. Rendered only when $role === 'company' --}}
@if ($role === 'company')
    <p class="sidebar-nav-section-header">{{ __('admin_dashboard.sidebar_section_operations') }}</p>
    <a href="{{ route('company.orders.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.orders.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('dashboard.orders') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-receipt"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('dashboard.orders') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.company_orders_desc') }}</p>
        </div>
    </a>
    <a href="{{ route('company.invoices.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.invoices.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('dashboard.invoices') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-file-invoice"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('dashboard.invoices') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.invoices_desc') }}</p>
        </div>
    </a>
    <a href="{{ route('company.vehicles.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.vehicles.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('dashboard.vehicles') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-car"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('dashboard.vehicles') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.vehicles_desc') }}</p>
        </div>
        @if($expiringDocumentsCount > 0)
            <span class="sidebar-nav-badge sidebar-nav-badge--warning" title="{{ __('vehicles.expiring_documents') }}">{{ $expiringDocumentsCount > 99 ? '99+' : $expiringDocumentsCount }}</span>
        @endif
    </a>
    <a href="{{ route('company.inspections.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.inspections.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('inspections.title') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-camera"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('inspections.title') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('inspections.gallery') }}</p>
        </div>
        @if($inspectionPendingCount > 0)
            <span class="sidebar-nav-badge sidebar-nav-badge--warning" title="{{ __('inspections.vehicles_pending') }}">{{ $inspectionPendingCount > 99 ? '99+' : $inspectionPendingCount }}</span>
        @endif
    </a>
    <a href="{{ route('company.tracking.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.tracking.index') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('tracking.tracking_page') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-location-dot"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('tracking.tracking_page') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('tracking.live_updates') }}</p>
        </div>
    </a>
    <a href="{{ route('company.fuel_balance') }}"
       class="sidebar-nav-item {{ $this->isActive('company.fuel_balance') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('company.fuel_balance_page') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-gauge-high"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('company.fuel_balance_page') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('company.fuel_balance_placeholder_desc') }}</p>
        </div>
    </a>

    <p class="sidebar-nav-section-header">{{ __('reports.reports') }}</p>
    <a href="{{ route('company.reports.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.reports.index') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('reports.all_reports') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('reports.all_reports') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('reports.reports_subtitle') }}</p>
        </div>
    </a>
    <a href="{{ route('company.fuel.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.fuel.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('reports.fuel_report') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-gas-pump"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('reports.fuel_report') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('reports.fuel_report_desc') }}</p>
        </div>
    </a>
    <a href="{{ route('company.reports.service') }}"
       class="sidebar-nav-item {{ $this->isActive('company.reports.service') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('reports.service_report') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('reports.service_report') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('reports.service_report_desc') }}</p>
        </div>
    </a>

    <p class="sidebar-nav-section-header">{{ __('admin_dashboard.sidebar_section_management') }}</p>
    <a href="{{ route('company.branches.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.branches.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('dashboard.branches') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-code-branch"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('dashboard.branches') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.branches_desc') }}</p>
        </div>
    </a>

    <p class="sidebar-nav-section-header">{{ __('admin_dashboard.sidebar_section_system') }}</p>
    <a href="{{ route('company.settings') }}"
       class="sidebar-nav-item {{ $this->isActive('company.settings') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('dashboard.settings') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-gear"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('dashboard.settings') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.company_settings_desc') }}</p>
        </div>
    </a>
@endif
