<div class="space-y-6">
    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="dash-card dash-card-kpi p-4 sm:p-5 min-w-0">
            <p class="dash-card-title">{{ __('dashboard.today_orders') }}</p>
            <p class="dash-card-value mt-1">{{ $todayOrders ?? 0 }}</p>
        </div>
        <div class="dash-card dash-card-kpi p-4 sm:p-5 min-w-0">
            <p class="dash-card-title">{{ __('dashboard.in_progress') }}</p>
            <p class="dash-card-value mt-1">{{ $inProgress ?? 0 }}</p>
        </div>
        <div class="dash-card dash-card-kpi p-4 sm:p-5 min-w-0">
            <p class="dash-card-title">{{ __('dashboard.completed') }}</p>
            <p class="dash-card-value mt-1">{{ $completed ?? 0 }}</p>
        </div>
    </div>

    {{-- Latest orders + Enabled services --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
        <div class="xl:col-span-2 dash-card p-4 sm:p-5 min-w-0 overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <h2 class="dash-section-title">{{ __('dashboard.latest_orders') }}</h2>
                @if(Route::has('company.orders.index'))
                    <a href="{{ route('company.orders.index') }}" class="dash-link">{{ __('common.view_all') }}</a>
                @endif
            </div>
            <div class="space-y-2">
                @forelse($latestOrders as $o)
                    <a href="{{ Route::has('company.orders.show') ? route('company.orders.show', $o) : '#' }}"
                       class="dash-order-row flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold truncate text-white">{{ __('dashboard.order') }} #{{ $o->id }} — {{ $o->status }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $o->city ?? '-' }} · {{ \Illuminate\Support\Str::limit($o->address ?? '', 40) }}</p>
                        </div>
                        <span class="text-sky-400 text-sm shrink-0">
                            <i class="fa-solid fa-arrow-left ms-1"></i>
                        </span>
                    </a>
                @empty
                    <p class="text-slate-500 text-sm py-6 text-center">{{ __('dashboard.no_results') }}</p>
                @endforelse
            </div>
        </div>

        <div class="dash-card p-4 sm:p-5 min-w-0">
            <h2 class="dash-section-title">{{ __('dashboard.enabled_services') }}</h2>
            <div class="flex flex-wrap gap-2">
                @forelse($enabledServices as $s)
                    <span class="dash-service-tag">
                        <span class="text-white font-medium">{{ $s->name }}</span>
                        <span class="text-slate-400">{{ $s->pivot->base_price ?? $s->base_price }} {{ __('company.sar') }}</span>
                    </span>
                @empty
                    <p class="text-sm text-slate-500">{{ __('dashboard.no_services_enabled') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
