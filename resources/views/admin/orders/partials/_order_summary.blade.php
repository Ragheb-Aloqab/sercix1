<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black mb-4">معلومات الطلب</h2>

    @php
        $payment = $order->payment;

        $payStatus = $payment?->status; // pending|paid|failed|partial...
        $payMethod = $payment?->method; // cash|tap...

        $payStatusLabel = match ($payStatus) {
            'paid'    => 'مدفوع',
            'pending' => 'قيد الانتظار',
            'failed'  => 'فشل الدفع',
            'partial' => 'مدفوع جزئي',
            default   => '—',
        };

        $payMethodLabel = match ($payMethod) {
            'cash' => 'كاش',
            'tap'  => 'Tap',
            default => '—',
        };

        $payBadge = match ($payStatus) {
            'paid'    => 'bg-emerald-100 text-emerald-700',
            'pending' => 'bg-amber-100 text-amber-700',
            'failed'  => 'bg-red-100 text-red-700',
            'partial' => 'bg-sky-100 text-sky-700',
            default   => 'bg-slate-100 text-slate-700',
        };

        // إجمالي الطلب: من pivot (total_price أو qty*unit_price مع fallback لـ base_price)
        $items = $order->services ?? collect();
        $computedTotal = $items->sum(function ($s) {
            $qty  = (float) ($s->pivot->qty ?? 0);
            $unit = (float) ($s->pivot->unit_price ?? 0) ?: (float) ($s->base_price ?? 0);
            return (float) ($s->pivot->total_price ?: ($qty * $unit));
        });
        $total = (float) ($order->total_amount ?? $computedTotal);

        $paidAmount = (float) ($payment?->amount ?? 0);
        $due = max(0, $total - $paidAmount);
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

        {{-- الشركة --}}
        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
            <p class="text-slate-500 dark:text-slate-400">الشركة</p>
            <p class="font-bold mt-1">{{ $order->company?->company_name ?? '—' }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $order->company?->phone ?? '' }}</p>
        </div>

        {{-- الفرع (إذا العلاقة موجودة) --}}
        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
            <p class="text-slate-500 dark:text-slate-400">الفرع</p>
            <p class="font-bold mt-1">{{ ($order->company->branches[0]->name)?? '—' }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Branch ID: {{ $order->company->branches[0]->id ?? '—' }}
            </p>
        </div>

        {{-- المركبة --}}
        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
            <p class="text-slate-500 dark:text-slate-400">المركبة</p>
            <p class="font-bold mt-1">
                {{ trim(($order->vehicle?->make ?? '').' '.($order->vehicle?->model ?? '')) ?: '—' }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                لوحة: {{ $order->vehicle?->plate_number ?? '—' }}
            </p>
        </div>

        {{-- الدفع --}}
        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
            <p class="text-slate-500 dark:text-slate-400">الدفع</p>

            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $payBadge }}">
                    {{ $payStatusLabel }}
                </span>

                <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-xs font-semibold">
                    {{ $payMethodLabel }}
                </span>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                الإجمالي: <span class="font-bold text-slate-900 dark:text-white">{{ number_format($total, 2) }}</span> SAR
                • المدفوع: <span class="font-bold text-slate-900 dark:text-white">{{ number_format($paidAmount, 2) }}</span> SAR
                • المتبقي: <span class="font-bold {{ $due > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format($due, 2) }}</span> SAR
            </p>
        </div>

        {{-- العنوان / الموقع --}}
        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 md:col-span-2">
            <p class="text-slate-500 dark:text-slate-400">العنوان / الموقع</p>
            <p class="font-bold mt-1">{{ $order->address ?? '—' }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Lat: {{ $order->lat ?? '—' }} | Lng: {{ $order->lng ?? '—' }}
            </p>
        </div>

        {{-- ملاحظات --}}
        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 md:col-span-2">
            <p class="text-slate-500 dark:text-slate-400">ملاحظات</p>
            <p class="font-semibold mt-1">{{ $order->notes ?? '—' }}</p>
        </div>
    </div>
</div>
