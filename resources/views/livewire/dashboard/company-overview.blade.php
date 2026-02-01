<div>
  @php
    use App\Models\Order;
    use App\Models\Payment;

    $today = now()->toDateString();

    $todayOrders = Order::query()->where('company_id', $company->id)->whereDate('created_at', $today)->count();
    $inProgress = Order::query()
        ->where('company_id', $company->id)
        ->whereIn('status', ['on_the_way', 'in_progress'])
        ->count();
    $completed = Order::query()->where('company_id', $company->id)->where('status', 'completed')->count();

    $paidTotal = Payment::query()->where('company_id', $company->id)->where('status', 'paid')->sum('amount');

    $latestOrders = Order::query()->where('company_id', $company->id)->latest()->take(6)->get();

    // خدمات الشركة من pivot company_services
    $enabledServices = $company->services()->wherePivot('is_enabled', true)->take(8)->get();
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <p class="text-sm text-slate-500">طلبات اليوم</p>
        <p class="text-3xl font-black mt-1">{{ $todayOrders }}</p>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <p class="text-sm text-slate-500">قيد التنفيذ</p>
        <p class="text-3xl font-black mt-1">{{ $inProgress }}</p>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <p class="text-sm text-slate-500">مكتملة</p>
        <p class="text-3xl font-black mt-1">{{ $completed }}</p>
    </div>

    <div
        class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <p class="text-sm text-slate-500">إجمالي المدفوعات</p>
        <p class="text-3xl font-black mt-1">{{ number_format($paidTotal, 2) }}</p>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div
        class="xl:col-span-2 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <h2 class="text-lg font-black">آخر الطلبات</h2>
        <div class="mt-4 space-y-3">
            @foreach ($latestOrders as $o)
                <div
                    class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <p class="font-bold">طلب #{{ $o->id }} — {{ $o->status }}</p>
                        <p class="text-sm text-slate-500">{{ $o->city }} —
                            {{ \Illuminate\Support\Str::limit($o->address, 40) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div
        class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <h2 class="text-lg font-black">الخدمات المفعلة لك</h2>
        <div class="mt-3 space-y-2">
            @forelse($enabledServices as $s)
                <div class="flex items-center justify-between text-sm">
                    <span class="font-semibold">{{ $s->name }}</span>
                    <span class="text-slate-500">
                        {{ $s->pivot->base_price ?? $s->base_price }} SAR
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-500">لا توجد خدمات مفعلة حالياً.</p>
            @endforelse
        </div>
    </div>
</div>
</div>
