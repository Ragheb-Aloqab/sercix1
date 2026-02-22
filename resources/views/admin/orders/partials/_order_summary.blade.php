<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black mb-4">معلومات الطلب</h2>

    @php
        // Order total from services (payment info removed)
        $items = $order->services ?? collect();
        $computedTotal = $items->sum(function ($s) {
            $qty  = (float) ($s->pivot->qty ?? 0);
            $unit = (float) ($s->pivot->unit_price ?? 0) ?: (float) ($s->base_price ?? 0);
            return (float) ($s->pivot->total_price ?: ($qty * $unit));
        });
        $total = (float) ($order->total_amount ?? $computedTotal);
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

        {{-- السائق / جوال التواصل (للتواصل عبر واتساب) --}}
        @if ($order->driver_phone || $order->requested_by_name)
        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
            <p class="text-slate-500 dark:text-slate-400">السائق / جوال التواصل</p>
            <p class="font-bold mt-1">{{ $order->requested_by_name ?? '—' }}</p>
            @if ($order->driver_phone)
                <p class="text-xs mt-1">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->driver_phone) }}" target="_blank" rel="noopener"
                       class="text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
                        <i class="fa-brands fa-whatsapp me-1"></i>{{ $order->driver_phone }}
                    </a>
                </p>
            @endif
        </div>
        @endif

        {{-- إجمالي الطلب (من الخدمات) --}}
        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
            <p class="text-slate-500 dark:text-slate-400">إجمالي الطلب</p>
            <p class="font-bold mt-1">{{ number_format($total, 2) }} SAR</p>
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
