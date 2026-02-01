<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black mb-3">الدفع</h2>

    @php
        $payment = $order->payment;
        $status  = $payment?->status; // pending|paid|failed
        $method  = $payment?->method; // cash|tap
        $amount  = (float) ($payment?->amount ?? 0);

        $statusLabel = match ($status) {
            'paid'    => 'مدفوع',
            'pending' => 'قيد الانتظار',
            'failed'  => 'فشل الدفع',
            default   => '—',
        };

        $methodLabel = match ($method) {
            'cash' => 'كاش',
            'tap'  => 'Tap',
            default => '—',
        };

        $badgeClass = match ($status) {
            'paid'    => 'bg-emerald-100 text-emerald-700',
            'pending' => 'bg-amber-100 text-amber-700',
            'failed'  => 'bg-red-100 text-red-700',
            default   => 'bg-slate-100 text-slate-700',
        };
    @endphp

    <div class="text-sm mb-4">
        <p class="text-slate-500 dark:text-slate-400">آخر حالة دفع</p>

        <div class="mt-2 flex flex-wrap items-center gap-2">
            {{-- Badge status --}}
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">
                {{ $statusLabel }}
            </span>

            {{-- amount --}}
            @if($payment)
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                    {{ number_format($amount, 2) }} SAR
                </span>
            @endif

            {{-- method --}}
            @if($payment?->method)
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    • {{ $methodLabel }}
                </span>
            @endif
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.orders.payments.store', $order) }}" class="space-y-3">
        @csrf

        <select name="method"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
            <option value="cash" @selected(old('method', $payment?->method)==='cash')>cash</option>
            <option value="tap" @selected(old('method', $payment?->method)==='tap')>tap</option>
        </select>

        <select name="status"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
            <option value="pending" @selected(old('status', $payment?->status)==='pending')>pending</option>
            <option value="paid" @selected(old('status', $payment?->status)==='paid')>paid</option>
            <option value="failed" @selected(old('status', $payment?->status)==='failed')>failed</option>
        </select>

        <input name="amount" value="{{ old('amount', $payment?->amount ?? ($order->total_amount ?? 0)) }}"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="Amount SAR" />

        <button type="submit"
            class="w-full px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
            حفظ الدفع
        </button>
    </form>

    <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">
        Tap Integration لاحقاً: هنا فقط نسجّل الدفع أو حالة العملية، وبعدها نربط Webhook.
    </p>
</div>
