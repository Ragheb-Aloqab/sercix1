{{-- Maintenance Center nav links. Rendered only when $role === 'maintenance_center' --}}
@if ($role === 'maintenance_center')
    <p class="sidebar-nav-section-header">{{ __('maintenance.assigned_rfqs') ?? 'Assigned RFQs' }}</p>
    <a href="{{ route('maintenance-center.dashboard') }}"
       class="sidebar-nav-item {{ $this->isActive('maintenance-center.dashboard') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('maintenance.center_dashboard') }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-warehouse"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('maintenance.assigned_rfqs') }}</p>
        </div>
    </a>
    <a href="{{ route('maintenance-center.history.index') }}"
       class="sidebar-nav-item {{ $this->isActive('maintenance-center.history.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('maintenance.history') ?? 'Maintenance History' }}">
        <span class="sidebar-nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('maintenance.history') ?? 'History' }}</p>
        </div>
    </a>
@endif
