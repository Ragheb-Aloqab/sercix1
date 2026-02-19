<div class="space-y-6">
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.today_orders') }}</p>
        <p class="text-3xl font-black mt-1">{{ $todayOrders ?? 0 }}</p>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.in_progress') }}</p>
        <p class="text-3xl font-black mt-1">{{ $inProgress ?? 0 }}</p>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.completed') }}</p>
        <p class="text-3xl font-black mt-1">{{ $completed ?? 0 }}</p>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.total_paid') }}</p>
        <p class="text-3xl font-black mt-1">{{ number_format($paidTotal ?? 0, 2) }}</p>
    </div>
</div>

<div class="mt-4 sm:mt-6 grid grid-cols-1 xl:grid-cols-3 gap-3 sm:gap-4">
    <div
        class="xl:col-span-2 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0 overflow-hidden">
        <h2 class="text-base sm:text-lg font-black">{{ __('dashboard.latest_orders') }}</h2>
        <div class="mt-3 sm:mt-4 space-y-2 sm:space-y-3">
            @forelse($latestOrders as $o)
                <div class="p-3 sm:p-4 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-bold truncate">{{ __('dashboard.order') }} #{{ $o->id }} — {{ $o->status }}</p>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 truncate">{{ $o->city ?? '-' }} —
                            {{ \Illuminate\Support\Str::limit($o->address ?? '', 40) }}</p>
                    </div>
                    @if(Route::has('company.orders.show'))
                        <a href="{{ route('company.orders.show', $o) }}"
                           class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold shrink-0">
                            {{ __('dashboard.view') }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div
        class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
        <h2 class="text-base sm:text-lg font-black">{{ __('dashboard.enabled_services') }}</h2>
        <div class="mt-3 space-y-2">
            @forelse($enabledServices as $s)
                <div class="flex items-center justify-between text-sm">
                    <span class="font-semibold">{{ $s->name }}</span>
                    <span class="text-slate-500">
                        {{ $s->pivot->base_price ?? $s->base_price }} SAR
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.no_services_enabled') }}</p>
            @endforelse
        </div>
    </div>
</div>
</div>
