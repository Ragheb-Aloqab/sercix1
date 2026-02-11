<div class="space-y-6">
    @if (session('success'))
        <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-800 dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-black text-xl">طلب #{{ $order->id }}</p>
                <p class="text-sm text-slate-500 mt-1">الحالة: {{ $order->status }}</p>
                @if ($order->status === 'requested' && $order->requested_by_name)
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">طلب من: {{ $order->requested_by_name }}</p>
                @endif
                @if ($order->technician)
                    <p class="text-sm text-slate-500 mt-1">
                        الفني: {{ $order->technician->name }} @if ($order->technician->phone) — {{ $order->technician->phone }} @endif
                    </p>
                @else
                    <p class="text-sm text-slate-500 mt-1">الفني: غير مُسنّد</p>
                @endif
            </div>
            <div>
                <a href="{{ route('company.orders.index') }}"
                   class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                    رجوع
                </a>
                @if ($order->status === 'requested')
                    <button type="button" wire:click="approveRequest"
                            class="my-4 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                        موافقة على الطلب
                    </button>
                @endif
                @if($order->status !== 'completed' && $order->status !== 'requested')
                    <button type="button" wire:click="cancelOrder"
                            wire:confirm="طلب إلغاء الطلب وإرساله للمدير. متأكد؟"
                            class="my-4 px-4 py-2 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-400 font-semibold">
                        إلغاء الطلب
                    </button>
                @endif
            </div>
        </div>
    </div>

    @php
        $payment = $order->payments?->first();
        $amount = $payment?->amount ?? $order->total_amount;
        $serviceName = $order->services->first()?->name ?? '-';
        $before = $order->attachments?->where('type', 'before_photo') ?? collect();
        $after = $order->attachments?->where('type', 'after_photo') ?? collect();
    @endphp

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <h3 class="font-black text-lg mb-3">تفاصيل الطلب</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-slate-500">الخدمة</span>
                <span class="font-bold">{{ $serviceName }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500">المبلغ المطلوب</span>
                <span class="font-bold">{{ (float)$amount > 0 ? number_format((float) $amount, 2) . ' SAR' : '-' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500">حالة الدفع</span>
                <span class="font-bold">{{ $payment?->status ?? 'no_payment' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500">طريقة الدفع</span>
                <span class="font-bold">{{ $payment?->method ? strtoupper($payment->method) : '-' }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h3 class="font-black text-lg mb-3">صور قبل</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse($before as $img)
                    <a href="{{ asset('storage/' . $img->file_path) }}" target="_blank"
                       class="block rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                        <img src="{{ asset('storage/' . $img->file_path) }}" class="w-full h-28 object-cover" alt="">
                    </a>
                @empty
                    <p class="text-sm text-slate-500 col-span-full">لا توجد صور قبل حتى الآن.</p>
                @endforelse
            </div>
        </div>
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h3 class="font-black text-lg mb-3">صور بعد</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse($after as $img)
                    <a href="{{ asset('storage/' . $img->file_path) }}" target="_blank"
                       class="block rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                        <img src="{{ asset('storage/' . $img->file_path) }}" class="w-full h-28 object-cover" alt="">
                    </a>
                @empty
                    <p class="text-sm text-slate-500 col-span-full">لا توجد صور بعد حتى الآن.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h3 class="font-black text-lg mb-3">الفاتورة</h3>
            @if ($order->invoice)
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">رقم الفاتورة</span>
                        <span class="font-bold">#{{ $order->invoice->id }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">الإجمالي</span>
                        <span class="font-bold">{{ $order->invoice->total ? number_format((float)$order->invoice->total, 2) . ' SAR' : '-' }}</span>
                    </div>
                    <a href="{{ route('company.invoices.show', $order->invoice->id) }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold mt-2">
                        عرض الفاتورة
                    </a>
                </div>
            @else
                <p class="text-sm text-slate-500">لا توجد فاتورة لهذا الطلب حالياً.</p>
            @endif
        </div>
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h3 class="font-black text-lg mb-3">المدفوعات</h3>
            @if ($order->payments && $order->payments->count())
                <div class="space-y-3">
                    @foreach ($order->payments as $pay)
                        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <p class="font-bold">{{ $pay->method ?? 'payment' }}</p>
                                <span class="text-xs font-bold px-3 py-1 rounded-xl {{ $pay->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">{{ $pay->status }}</span>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">المبلغ: {{ is_null($pay->amount) ? '-' : number_format((float) $pay->amount, 2) . ' SAR' }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $pay->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">لا توجد مدفوعات بعد.</p>
            @endif
        </div>
    </div>
</div>
