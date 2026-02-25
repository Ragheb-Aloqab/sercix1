{{-- Admin nav links. Rendered only when $role === 'admin' --}}
@if ($role === 'admin')
    {{-- Management --}}
    <p class="sidebar-nav-section-header">{{ __('admin_dashboard.sidebar_section_management') }}</p>
    <a href="{{ route('admin.companies.index') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.companies.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('admin_dashboard.companies_overview') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-building"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('admin_dashboard.companies_overview') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('admin_dashboard.all_companies') }}</p>
        </div>
    </a>
    <a href="{{ route('admin.vehicles.index') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.vehicles.index') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('admin_dashboard.vehicles_overview') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-car"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('admin_dashboard.vehicles_overview') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('admin_dashboard.all_vehicles') }}</p>
        </div>
        @if($expiringDocumentsCount > 0)
            <span class="sidebar-nav-badge sidebar-nav-badge--warning" title="{{ __('vehicles.expiring_documents') }}">{{ $expiringDocumentsCount > 99 ? '99+' : $expiringDocumentsCount }}</span>
        @endif
    </a>
    <a href="{{ route('admin.customers.index') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.customers.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('dashboard.customers') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-users"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('dashboard.customers') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.customers_desc') }}</p>
        </div>
    </a>
    <a href="{{ route('admin.users.index') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.users.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('admin_dashboard.admin_users') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-user-shield"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('admin_dashboard.admin_users') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('admin_dashboard.admin_users_desc') }}</p>
        </div>
    </a>

    {{-- Operations --}}
    <p class="sidebar-nav-section-header">{{ __('admin_dashboard.sidebar_section_operations') }}</p>
    <a href="{{ route('admin.orders.index') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.orders.index') || $this->isActive('admin.orders.show') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('dashboard.orders') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-receipt"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('dashboard.orders') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.orders_desc') }}</p>
        </div>
    </a>
    <a href="{{ route('admin.quota-requests.index') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.quota-requests.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('admin_dashboard.quota_requests') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-clipboard-list"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('admin_dashboard.quota_requests') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('admin_dashboard.quota_requests_desc') }}</p>
        </div>
        @if($pendingQuotaRequests > 0)
            <span class="sidebar-nav-badge sidebar-nav-badge--warning">{{ $pendingQuotaRequests > 99 ? '99+' : $pendingQuotaRequests }}</span>
        @endif
    </a>
    @if(config('servx.payments_enabled', false))
    <a href="{{ route('admin.bank-transfers.index') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.bank-transfers.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('dashboard.bank_transfers') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-landmark"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('dashboard.bank_transfers') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.bank_transfers_desc') }}</p>
        </div>
    </a>
    @endif
    <a href="{{ route('admin.services.index') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.services.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('dashboard.services') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('dashboard.services') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.services_desc') }}</p>
        </div>
    </a>

    {{-- System --}}
    <p class="sidebar-nav-section-header">{{ __('admin_dashboard.sidebar_section_system') }}</p>
    <a href="{{ route('admin.announcements.index') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.announcements.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('admin_dashboard.announcements') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-bullhorn"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('admin_dashboard.announcements') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('admin_dashboard.announcements_desc') }}</p>
        </div>
    </a>
    <a href="{{ route('admin.activities.index') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.activities.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('dashboard.activity_log') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('dashboard.activity_log') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.activity_log_desc') }}</p>
        </div>
    </a>
    <a href="{{ route('admin.notifications.index') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.notifications.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('common.notifications') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-bell"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('common.notifications') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.notifications_desc') ?? 'View notifications' }}</p>
        </div>
        @if($unreadNotifications > 0)
            <span class="sidebar-nav-badge sidebar-nav-badge--info">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
        @endif
    </a>
    <a href="{{ route('admin.settings') }}"
       class="sidebar-nav-item {{ $this->isActive('admin.settings') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('dashboard.settings') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-gear"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('dashboard.settings') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('dashboard.settings_desc') }}</p>
        </div>
    </a>
@endif
