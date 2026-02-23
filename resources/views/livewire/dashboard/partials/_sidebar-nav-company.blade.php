{{-- Company nav links. Rendered only when $role === 'company' --}}
@if ($role === 'company')
    <a href="{{ route('company.orders.index') }}" class="mt-2 {{ $this->isActive('company.orders.*') ? $active : $link }}">
        <span class="{{ $this->isActive('company.orders.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-receipt"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.orders') }}</p>
            <p class="text-xs text-servx-silver">{{ __('dashboard.company_orders_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('company.invoices.index') }}" class="mt-2 {{ $this->isActive('company.invoices.*') ? $active : $link }}">
        <span class="{{ $this->isActive('company.invoices.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-file-invoice"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.invoices') }}</p>
            <p class="text-xs text-servx-silver">{{ __('dashboard.invoices_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('company.vehicles.index') }}" class="mt-2 {{ $this->isActive('company.vehicles.*') ? $active : $link }}">
        <span class="{{ $this->isActive('company.vehicles.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-car"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.vehicles') }}</p>
            <p class="text-xs text-servx-silver">{{ __('dashboard.vehicles_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('company.tracking.index') }}" class="mt-2 {{ $this->isActive('company.tracking.index') ? $active : $link }}">
        <span class="{{ $this->isActive('company.tracking.index') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-location-dot"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('tracking.tracking_page') }}</p>
            <p class="text-xs text-servx-silver">{{ __('tracking.live_updates') }}</p>
        </div>
    </a>

    <a href="{{ route('company.fuel_balance') }}" class="mt-2 {{ $this->isActive('company.fuel_balance') ? $active : $link }}">
        <span class="{{ $this->isActive('company.fuel_balance') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-gauge-high"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('company.fuel_balance_page') }}</p>
            <p class="text-xs text-servx-silver">{{ __('company.fuel_balance_placeholder_desc') }}</p>
        </div>
    </a>

    <p class="px-3 text-xs font-semibold text-servx-silver mt-4 mb-2">{{ __('reports.reports') }}</p>
    <a href="{{ route('company.reports.index') }}" class="mt-1 {{ $this->isActive('company.reports.index') ? $active : $link }}">
        <span class="{{ $this->isActive('company.reports.index') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-chart-pie"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('reports.all_reports') }}</p>
            <p class="text-xs text-servx-silver">{{ __('reports.reports_subtitle') }}</p>
        </div>
    </a>
    <a href="{{ route('company.fuel.index') }}" class="mt-1 {{ $this->isActive('company.fuel.*') ? $active : $link }}">
        <span class="{{ $this->isActive('company.fuel.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-gas-pump"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('reports.fuel_report') }}</p>
            <p class="text-xs text-servx-silver">{{ __('reports.fuel_report_desc') }}</p>
        </div>
    </a>
    <a href="{{ route('company.reports.service') }}" class="mt-1 {{ $this->isActive('company.reports.service') ? $active : $link }}">
        <span class="{{ $this->isActive('company.reports.service') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-screwdriver-wrench"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('reports.service_report') }}</p>
            <p class="text-xs text-servx-silver">{{ __('reports.service_report_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('company.branches.index') }}" class="mt-2 {{ $this->isActive('company.branches.*') ? $active : $link }}">
        <span class="{{ $this->isActive('company.branches.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-code-branch"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.branches') }}</p>
            <p class="text-xs text-servx-silver">{{ __('dashboard.branches_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('company.settings') }}" class="mt-2 {{ $this->isActive('company.settings') ? $active : $link }}">
        <span class="{{ $this->isActive('company.settings') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-gear"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.settings') }}</p>
            <p class="text-xs text-servx-silver">{{ __('dashboard.company_settings_desc') }}</p>
        </div>
    </a>
@endif
