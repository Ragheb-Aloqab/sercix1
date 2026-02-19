{{-- Admin nav links. Rendered only when $role === 'admin' --}}
@if ($role === 'admin')
    <a href="{{ route('admin.orders.index') }}" class="mt-2 {{ $this->isActive('admin.orders.index') || $this->isActive('admin.orders.show') ? $active : $link }}">
        <span class="{{ $this->isActive('admin.orders.index') || $this->isActive('admin.orders.show') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-receipt"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.orders') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.orders_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('admin.bank-transfers.index') }}" class="mt-2 {{ $this->isActive('admin.bank-transfers.*') ? $active : $link }}">
        <span class="{{ $this->isActive('admin.bank-transfers.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-landmark"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.bank_transfers') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.bank_transfers_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('admin.services.index') }}" class="mt-2 {{ $this->isActive('admin.services.*') ? $active : $link }}">
        <span class="{{ $this->isActive('admin.services.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-screwdriver-wrench"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.services') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.services_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('admin.technicians.index') }}" class="mt-2 {{ $this->isActive('admin.technicians.*') ? $active : $link }}">
        <span class="{{ $this->isActive('admin.technicians.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-user-gear"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.technicians') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.technicians_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('admin.maps.technicians') }}" class="mt-2 {{ $this->isActive('admin.maps.technicians') ? $active : $link }}">
        <span class="{{ $this->isActive('admin.maps.technicians') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-map-location-dot"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.technicians_map') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.technicians_map_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('admin.customers.index') }}" class="mt-2 {{ $this->isActive('admin.customers.*') ? $active : $link }}">
        <span class="{{ $this->isActive('admin.customers.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-users"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.customers') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.customers_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('admin.inventory.index') }}" class="mt-2 {{ $this->isActive('admin.inventory.index') ? $active : $link }}">
        <span class="{{ $this->isActive('admin.inventory.index') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-boxes-stacked"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.inventory') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.inventory_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('admin.inventory.movements') }}" class="mt-1 {{ $this->isActive('admin.inventory.movements') ? $active : $link }}">
        <span class="{{ $this->isActive('admin.inventory.movements') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-arrows-left-right"></i></span>
        <div class="flex-1">
            <p class="font-semibold leading-5 text-sm">{{ __('dashboard.movements') }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('dashboard.movements_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('admin.activities.index') }}" class="mt-2 {{ $this->isActive('admin.activities.*') ? $active : $link }}">
        <span class="{{ $this->isActive('admin.activities.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-clock-rotate-left"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.activity_log') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.activity_log_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('admin.settings') }}" class="mt-2 {{ $this->isActive('admin.settings') ? $active : $link }}">
        <span class="{{ $this->isActive('admin.settings') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-gear"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.settings') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.settings_desc') }}</p>
        </div>
    </a>
@endif
