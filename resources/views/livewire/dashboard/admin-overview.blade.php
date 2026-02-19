<div class="space-y-6">
    @php
        use App\Models\Order;
        use App\Models\Payment;
        use App\Models\User;

        $today = now()->toDateString();

        $todayOrders = Order::query()->whereDate('created_at', $today)->count();
        $inProgress = Order::query()->whereIn('status', ['in_progress'])->count();
        $pending = Order::query()->whereIn('status', ['pending_approval', 'approved', 'pending_confirmation'])->count();
        $unassigned = Order::query()
            ->whereNull('technician_id')
            ->where('status', 'approved')
            ->count();

        $todayRevenue = Payment::query()->where('status', 'paid')->whereDate('created_at', $today)->sum('amount');
        $pendingPayments = Payment::query()->where('status', 'pending')->count();
        $activeTechs = User::query()->where('role', 'technician')->where('status', 'active')->count();

        $latestOrders = Order::query()
            ->with(['company:id,company_name,phone', 'technician:id,name,phone'])
            ->latest()
            ->take(8)
            ->get();
    @endphp
    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.today_orders') }}</p>
                    <p class="text-3xl font-black mt-1">{{ $todayOrders }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ __('dashboard.unassigned') }}: {{ $unassigned }}</p>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.in_progress') }}</p>
                    <p class="text-3xl font-black mt-1">{{ $inProgress }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-600 text-white flex items-center justify-center">
                    <i class="fa-solid fa-person-walking"></i>
                </div>
            </div>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ __('dashboard.active_techs') }}: {{ $activeTechs }}</p>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.today_revenue') }}</p>
                    <p class="text-3xl font-black mt-1">{{ number_format($todayRevenue, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 flex items-center justify-center">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ __('dashboard.pending_payments') }}: {{ $pendingPayments }}</p>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('dashboard.pending') }}</p>
                    <p class="text-3xl font-black mt-1">{{ $pending }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ __('dashboard.orders_need_followup') }}</p>
        </div>
    </div>

    {{-- Latest + Alerts --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-3 sm:gap-4">
        <div class="xl:col-span-2 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0 overflow-hidden">
            <h2 class="text-lg font-black">{{ __('dashboard.latest_orders') }}</h2>

            <div class="mt-4 space-y-3">
                @foreach ($latestOrders as $o)
                    <div class="p-3 sm:p-4 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-bold truncate">{{ __('dashboard.order') }} #{{ $o->id }} — {{ $o->status }}</p>
                            <p class="text-xs sm:text-sm text-slate-500 truncate">
                                {{ __('dashboard.company_label') }}: {{ $o->company?->company_name }} — {{ __('dashboard.technician_label') }}: {{ $o->technician?->name ?? __('dashboard.unassigned_label') }}
                            </p>
                        </div>

                        <a href="{{ route('admin.orders.show', $o) }}"
                           class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold">
                            {{ __('dashboard.view') }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-5 min-w-0">
            <h2 class="text-lg font-black">{{ __('dashboard.alerts') }}</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <li>• {{ __('dashboard.unassigned') }}: {{ $unassigned }}</li>
                <li>• {{ __('dashboard.pending_payments') }}: {{ $pendingPayments }}</li>
                <li>• {{ __('dashboard.active_techs') }}: {{ $activeTechs }}</li>
            </ul>
        </div>
    </div>
</div>
