<div class="space-y-6">
    @php
        use App\Models\Order;
        use App\Models\Payment;
        use App\Models\User;

        $today = now()->toDateString();

        $todayOrders = Order::query()->whereDate('created_at', $today)->count();
        $inProgress = Order::query()->whereIn('status', ['on_the_way', 'in_progress'])->count();
        $pending = Order::query()->where('status', 'pending')->count();
        $unassigned = Order::query()
            ->whereNull('technician_id')
            ->whereIn('status', ['pending', 'accepted'])
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
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">طلبات اليوم</p>
                    <p class="text-3xl font-black mt-1">{{ $todayOrders }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">غير مسندة: {{ $unassigned }}</p>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">قيد التنفيذ</p>
                    <p class="text-3xl font-black mt-1">{{ $inProgress }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-600 text-white flex items-center justify-center">
                    <i class="fa-solid fa-person-walking"></i>
                </div>
            </div>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">فنيون نشطون: {{ $activeTechs }}</p>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">إيراد اليوم</p>
                    <p class="text-3xl font-black mt-1">{{ number_format($todayRevenue, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 flex items-center justify-center">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">دفعات معلّقة: {{ $pendingPayments }}</p>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">بانتظار</p>
                    <p class="text-3xl font-black mt-1">{{ $pending }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">طلبات تحتاج متابعة</p>
        </div>
    </div>

    {{-- Latest + Alerts --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h2 class="text-lg font-black">آخر الطلبات</h2>

            <div class="mt-4 space-y-3">
                @foreach ($latestOrders as $o)
                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <p class="font-bold">طلب #{{ $o->id }} — {{ $o->status }}</p>
                            <p class="text-sm text-slate-500">
                                شركة: {{ $o->company?->company_name }} — فني: {{ $o->technician?->name ?? 'غير مسند' }}
                            </p>
                        </div>

                        <a href="{{ route('admin.orders.show', $o) }}"
                           class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold">
                            عرض
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h2 class="text-lg font-black">تنبيهات</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <li>• طلبات غير مسندة: {{ $unassigned }}</li>
                <li>• دفعات معلّقة: {{ $pendingPayments }}</li>
                <li>• فنيون نشطون: {{ $activeTechs }}</li>
            </ul>
        </div>
    </div>
</div>
