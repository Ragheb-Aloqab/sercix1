{{-- Company Panel Sidebar — Fleet Management (Company only) --}}
@if ($role === 'company')
    {{-- 1. Dashboard --}}
    <a href="{{ route('company.dashboard') }}"
       class="sidebar-nav-item {{ $this->isActive('company.dashboard') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('fleet.dashboard') }}">
        <span class="sidebar-nav-icon sidebar-nav-icon--sky"><i class="fa-solid fa-gauge-high"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('fleet.dashboard') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('fleet.dashboard_desc') }}</p>
        </div>
    </a>

    {{-- 2. My Vehicles --}}
    <a href="{{ route('company.vehicles.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.vehicles.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('fleet.my_vehicles') }}">
        <span class="sidebar-nav-icon sidebar-nav-icon--blue"><i class="fa-solid fa-car"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('fleet.my_vehicles') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('fleet.my_vehicles_desc') }}</p>
        </div>
        @if($expiringDocumentsCount > 0)
            <span class="sidebar-nav-badge sidebar-nav-badge--warning" title="{{ __('vehicles.expiring_documents') }}">{{ $expiringDocumentsCount > 99 ? '99+' : $expiringDocumentsCount }}</span>
        @endif
    </a>

    {{-- 3. Maintenance (collapsible section) --}}
    <div x-data="{ maintenanceOpen: {{ ($this->isActive('company.maintenance-requests.*') || $this->isActive('company.maintenance-offers.*') || $this->isActive('company.maintenance-invoices.*')) ? 'true' : 'false' }} }" class="sidebar-nav-group">
        <button type="button" @click="maintenanceOpen = !maintenanceOpen"
                class="sidebar-nav-item w-full text-start {{ ($this->isActive('company.maintenance-requests.*') || $this->isActive('company.maintenance-offers.*') || $this->isActive('company.maintenance-invoices.*')) ? 'sidebar-nav-item--active' : '' }}"
                title="{{ __('fleet.maintenance') }}">
            <span class="sidebar-nav-icon sidebar-nav-icon--amber"><i class="fa-solid fa-screwdriver-wrench"></i></span>
            <div class="sidebar-nav-text flex-1">
                <p class="sidebar-nav-label">{{ __('fleet.maintenance') }}</p>
                <p class="sidebar-nav-sublabel">{{ __('fleet.maintenance_desc') }}</p>
            </div>
            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200 text-amber-400 dark:text-amber-400 shrink-0" :class="maintenanceOpen ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="maintenanceOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="overflow-hidden">
            <div class="ps-4 ms-9 border-s border-slate-600/50 space-y-0.5 py-1">
                <a href="{{ route('company.maintenance-requests.index') }}"
                   class="sidebar-nav-item sidebar-nav-item--sub {{ $this->isActive('company.maintenance-requests.*') ? 'sidebar-nav-item--active' : '' }}"
                   title="{{ __('fleet.maintenance_requests') }}">
                    <span class="sidebar-nav-icon sidebar-nav-icon--sky"><i class="fa-solid fa-clipboard-list"></i></span>
                    <div class="sidebar-nav-text">
                        <p class="sidebar-nav-label">{{ __('fleet.maintenance_requests') }}</p>
                    </div>
                </a>
                <a href="{{ route('company.maintenance-offers.index') }}"
                   class="sidebar-nav-item sidebar-nav-item--sub {{ $this->isActive('company.maintenance-offers.*') ? 'sidebar-nav-item--active' : '' }}"
                   title="{{ __('fleet.maintenance_offers') }}">
                    <span class="sidebar-nav-icon sidebar-nav-icon--amber"><i class="fa-solid fa-tags"></i></span>
                    <div class="sidebar-nav-text">
                        <p class="sidebar-nav-label">{{ __('fleet.maintenance_offers') }}</p>
                    </div>
                </a>
                <a href="{{ route('company.maintenance-invoices.index') }}"
                   class="sidebar-nav-item sidebar-nav-item--sub {{ $this->isActive('company.maintenance-invoices.*') ? 'sidebar-nav-item--active' : '' }}"
                   title="{{ __('fleet.maintenance_invoices') }}">
                    <span class="sidebar-nav-icon sidebar-nav-icon--blue"><i class="fa-solid fa-file-invoice"></i></span>
                    <div class="sidebar-nav-text">
                        <p class="sidebar-nav-label">{{ __('fleet.maintenance_invoices') }}</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- 4. Fuel (collapsible section) --}}
    @php
        $fuelActive = request()->routeIs('company.fuel.index') || request()->routeIs('company.fuel-balance') || (request()->routeIs('company.invoices.index') && request()->get('invoice_type') === 'fuel');
    @endphp
    <div x-data="{ fuelOpen: {{ $fuelActive ? 'true' : 'false' }} }" class="sidebar-nav-group">
        <button type="button" @click="fuelOpen = !fuelOpen"
                class="sidebar-nav-item w-full text-start {{ $fuelActive ? 'sidebar-nav-item--active' : '' }}"
                title="{{ __('fleet.fuel') }}">
            <span class="sidebar-nav-icon sidebar-nav-icon--orange"><i class="fa-solid fa-gas-pump"></i></span>
            <div class="sidebar-nav-text flex-1">
                <p class="sidebar-nav-label">{{ __('fleet.fuel') }}</p>
                <p class="sidebar-nav-sublabel">{{ __('fleet.fuel_desc') }}</p>
            </div>
            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200 text-orange-400 dark:text-orange-400 shrink-0" :class="fuelOpen ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="fuelOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="overflow-hidden">
            <div class="ps-4 ms-9 border-s border-slate-600/50 space-y-0.5 py-1">
                <a href="{{ route('company.fuel-balance') }}"
                   class="sidebar-nav-item sidebar-nav-item--sub {{ request()->routeIs('company.fuel-balance') ? 'sidebar-nav-item--active' : '' }}"
                   title="{{ __('fleet.fuel_balance') }}">
                    <span class="sidebar-nav-icon sidebar-nav-icon--emerald"><i class="fa-solid fa-wallet"></i></span>
                    <div class="sidebar-nav-text">
                        <p class="sidebar-nav-label">{{ __('fleet.fuel_balance') }}</p>
                    </div>
                </a>
                <a href="{{ route('company.fuel.index') }}"
                   class="sidebar-nav-item sidebar-nav-item--sub {{ request()->routeIs('company.fuel.index') ? 'sidebar-nav-item--active' : '' }}"
                   title="{{ __('fleet.fuel_refills') }}">
                    <span class="sidebar-nav-icon sidebar-nav-icon--orange"><i class="fa-solid fa-gas-pump"></i></span>
                    <div class="sidebar-nav-text">
                        <p class="sidebar-nav-label">{{ __('fleet.fuel_refills') }}</p>
                    </div>
                </a>
                <a href="{{ route('company.invoices.index', ['invoice_type' => 'fuel']) }}"
                   class="sidebar-nav-item sidebar-nav-item--sub {{ (request()->routeIs('company.invoices.index') && request()->get('invoice_type') === 'fuel') ? 'sidebar-nav-item--active' : '' }}"
                   title="{{ __('fleet.fuel_invoices') }}">
                    <span class="sidebar-nav-icon sidebar-nav-icon--blue"><i class="fa-solid fa-file-invoice"></i></span>
                    <div class="sidebar-nav-text">
                        <p class="sidebar-nav-label">{{ __('fleet.fuel_invoices') }}</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- 5. Tracking --}}
    <a href="{{ route('company.tracking.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.tracking.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('fleet.tracking') }}">
        <span class="sidebar-nav-icon sidebar-nav-icon--emerald"><i class="fa-solid fa-location-dot"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('fleet.tracking') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('fleet.tracking_desc') }}</p>
        </div>
    </a>

    {{-- 6. Reports --}}
    <a href="{{ route('company.reports.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.reports.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('fleet.reports') }}">
        <span class="sidebar-nav-icon sidebar-nav-icon--violet"><i class="fa-solid fa-chart-pie"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('fleet.reports') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('fleet.reports_desc') }}</p>
        </div>
    </a>

    {{-- 7. Notifications --}}
    <a href="{{ route('company.notifications.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.notifications.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('common.notifications') }}">
        <span class="sidebar-nav-icon sidebar-nav-icon--rose"><i class="fa-solid fa-bell"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('common.notifications') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('fleet.notifications_desc') }}</p>
        </div>
        @if($unreadNotifications > 0)
            <span class="sidebar-nav-badge sidebar-nav-badge--info">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
        @endif
    </a>

    {{-- 8. My Insurance --}}
    <a href="{{ route('company.insurances.index') }}"
       class="sidebar-nav-item {{ $this->isActive('company.insurances.*') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('fleet.my_insurance') }}">
        <span class="sidebar-nav-icon sidebar-nav-icon--cyan"><i class="fa-solid fa-shield-halved"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('fleet.my_insurance') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('fleet.my_insurance_desc') }}</p>
        </div>
    </a>

    {{-- 9. Settings --}}
    <a href="{{ route('company.settings') }}"
       class="sidebar-nav-item {{ $this->isActive('company.settings') ? 'sidebar-nav-item--active' : '' }}"
       title="{{ __('fleet.settings') }}">
        <span class="sidebar-nav-icon sidebar-nav-icon--slate"><i class="fa-solid fa-gear"></i></span>
        <div class="sidebar-nav-text">
            <p class="sidebar-nav-label">{{ __('fleet.settings') }}</p>
            <p class="sidebar-nav-sublabel">{{ __('fleet.settings_desc') }}</p>
        </div>
    </a>
@endif
