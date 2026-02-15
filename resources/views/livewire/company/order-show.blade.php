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
                <p class="font-black text-xl">{{ __('orders.order') }} #{{ $order->id }}</p>
                <p class="text-sm text-slate-500 mt-1">{{ __('orders.status_label') }}: {{ \Illuminate\Support\Str::startsWith(__('common.status_' . $order->status), 'common.') ? $order->status : __('common.status_' . $order->status) }}</p>
                @if ($order->status === 'pending_company' && $order->requested_by_name)
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ __('orders.requested_by') }}: {{ $order->requested_by_name }}</p>
                @endif
                @if ($order->technician)
                    <p class="text-sm text-slate-500 mt-1">
                        {{ __('orders.technician_label') }}: {{ $order->technician->name }} @if ($order->technician->phone) — {{ $order->technician->phone }} @endif
                    </p>
                @else
                    <p class="text-sm text-slate-500 mt-1">{{ __('orders.technician_label') }}: {{ __('orders.unassigned') }}</p>
                @endif
            </div>
            <div>
                <a href="{{ route('company.orders.index') }}"
                   class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                    {{ __('orders.back') }}
                </a>
                @if ($order->status === 'pending_company')
                    <button type="button" wire:click="approveRequest"
                            class="my-4 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                        {{ __('orders.approve_request') }}
                    </button>
                @endif
                @if($order->status !== 'completed' && $order->status !== 'pending_company')
                    <button type="button" wire:click="cancelOrder"
                            wire:confirm="{{ __('orders.cancel_confirm') }}"
                            class="my-4 px-4 py-2 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-400 font-semibold">
                        {{ __('orders.cancel_order') }}
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
        <h3 class="font-black text-lg mb-3">{{ __('orders.order_details') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-slate-500">{{ __('orders.service') }}</span>
                <span class="font-bold">{{ $serviceName }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500">{{ __('orders.amount_required') }}</span>
                <span class="font-bold">{{ (float)$amount > 0 ? number_format((float) $amount, 2) . ' ' . __('company.sar') : '-' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500">{{ __('orders.payment_status') }}</span>
                <span class="font-bold">{{ $payment?->status ?? __('orders.no_payment') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500">{{ __('orders.payment_method') }}</span>
                <span class="font-bold">{{ $payment?->method ? strtoupper($payment->method) : '-' }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h3 class="font-black text-lg mb-3">{{ __('orders.before_photos') }}</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse($before as $img)
                    <a href="{{ asset('storage/' . $img->file_path) }}" target="_blank"
                       class="block rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                        <img src="{{ asset('storage/' . $img->file_path) }}" class="w-full h-28 object-cover" alt="">
                    </a>
                @empty
                    <p class="text-sm text-slate-500 col-span-full">{{ __('orders.no_before_photos') }}</p>
                @endforelse
            </div>
        </div>
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h3 class="font-black text-lg mb-3">{{ __('orders.after_photos') }}</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse($after as $img)
                    <a href="{{ asset('storage/' . $img->file_path) }}" target="_blank"
                       class="block rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                        <img src="{{ asset('storage/' . $img->file_path) }}" class="w-full h-28 object-cover" alt="">
                    </a>
                @empty
                    <p class="text-sm text-slate-500 col-span-full">{{ __('orders.no_after_photos') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h3 class="font-black text-lg mb-3">{{ __('orders.invoice') }}</h3>
            @if ($order->invoice)
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">{{ __('orders.invoice_number') }}</span>
                        <span class="font-bold">#{{ $order->invoice->id }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">{{ __('orders.total') }}</span>
                        <span class="font-bold">{{ $order->invoice->total ? number_format((float)$order->invoice->total, 2) . ' ' . __('company.sar') : '-' }}</span>
                    </div>
                    <a href="{{ route('company.invoices.show', $order->invoice->id) }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold mt-2">
                        {{ __('orders.view_invoice') }}
                    </a>
                </div>
            @else
                <p class="text-sm text-slate-500">{{ __('orders.no_invoice') }}</p>
            @endif
        </div>
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h3 class="font-black text-lg mb-3">{{ __('orders.payments') }}</h3>
            @if ($order->payments && $order->payments->count())
                <div class="space-y-3">
                    @foreach ($order->payments as $pay)
                        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <p class="font-bold">{{ $pay->method ?? 'payment' }}</p>
                                <span class="text-xs font-bold px-3 py-1 rounded-xl {{ $pay->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">{{ $pay->status }}</span>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">{{ __('orders.amount_label') }}: {{ is_null($pay->amount) ? '-' : number_format((float) $pay->amount, 2) . ' ' . __('company.sar') }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $pay->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">{{ __('orders.no_payments') }}</p>
            @endif
        </div>
    </div>
</div>
