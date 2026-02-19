<div class="space-y-6">
    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.today_orders') }}</p>
            <p class="text-3xl font-black mt-1">{{ $kpis['today'] ?? 0 }}</p>
        </div>
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.in_progress') }}</p>
            <p class="text-3xl font-black mt-1">{{ $kpis['progress'] ?? 0 }}</p>
        </div>
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.completed') }}</p>
            <p class="text-3xl font-black mt-1">{{ $kpis['completed'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Latest tasks --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0 overflow-hidden">
        <h2 class="text-lg font-black">{{ __('dashboard.tasks') }}</h2>
        <div class="mt-4 space-y-3">
            @forelse($latestTasks as $order)
                <div class="p-3 sm:p-4 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-bold truncate">{{ __('dashboard.order') }} #{{ $order->id }} — {{ $order->status }}</p>
                        <p class="text-xs sm:text-sm text-slate-500 truncate">
                            {{ $order->company?->company_name ?? '-' }}
                            @if($order->vehicle)
                                — {{ $order->vehicle->plate_number ?? $order->vehicle->make }}
                            @endif
                        </p>
                    </div>
                    @if(Route::has('tech.tasks.show'))
                        <a href="{{ route('tech.tasks.show', $order) }}"
                           class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold shrink-0">
                            {{ __('dashboard.view') }}
                        </a>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400 py-4">{{ __('dashboard.no_results') }}</p>
            @endforelse
        </div>
    </div>
</div>
