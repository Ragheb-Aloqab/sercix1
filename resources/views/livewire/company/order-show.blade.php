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
                @if (in_array($order->status, ['pending_approval', 'approved', 'in_progress', 'pending_confirmation']) && $order->requested_by_name)
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ __('orders.requested_by') }}: {{ $order->requested_by_name }}</p>
                @endif
                @if ($order->status === 'rejected' && $order->rejection_reason)
                    <p class="text-sm text-rose-600 mt-1">{{ __('orders.rejection_reason') }}: {{ $order->rejection_reason }}</p>
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
                @if ($order->status === 'pending_approval')
                    <button type="button" wire:click="approveRequest"
                            @disabled(!$hasQuotation)
                            class="my-4 px-4 py-2 rounded-xl font-semibold {{ $hasQuotation ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-300 dark:bg-slate-700 text-slate-500 cursor-not-allowed' }}"
                            @if(!$hasQuotation) title="{{ __('orders.quotation_required_for_approval') }}" @endif>
                        {{ __('orders.approve_request') }}
                    </button>
                    <button type="button" wire:click="openRejectModal"
                            class="my-4 px-4 py-2 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 font-semibold">
                        {{ __('orders.reject_request') }}
                    </button>
                @endif
                @if ($order->status === 'pending_confirmation')
                    <button type="button" wire:click="confirmCompletion" wire:loading.attr="disabled"
                            class="my-4 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmCompletion">{{ __('orders.confirm_completion') }}</span>
                        <span wire:loading wire:target="confirmCompletion">جاري الحفظ...</span>
                    </button>
                @endif
                @if($order->status !== 'completed' && $order->status !== 'pending_approval' && $order->status !== 'rejected')
                    <button type="button" wire:click="cancelOrder"
                            wire:confirm="{{ __('orders.cancel_confirm') }}"
                            class="my-4 px-4 py-2 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-400 font-semibold">
                        {{ __('orders.cancel_order') }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Quotation invoice (required for approval) --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <h3 class="font-black text-lg mb-3">{{ __('orders.quotation_invoice') }}</h3>
        @if ($quotationInvoice)
            <div class="space-y-2 text-sm p-4 rounded-2xl {{ $hasQuotation ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-800/50' }}">
                <p class="font-bold">{{ __('orders.quotation_invoice') }}</p>
                @if ($isQuotationImage)
                    <a href="{{ asset('storage/' . $quotationInvoice->file_path) }}" target="_blank" class="block mt-2 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 max-w-xs">
                        <img src="{{ asset('storage/' . $quotationInvoice->file_path) }}" alt="{{ $quotationInvoice->original_name ?? 'Quotation' }}" class="w-full h-40 object-contain bg-white">
                    </a>
                @else
                    <div class="mt-2 p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 inline-flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf text-3xl text-rose-600"></i>
                        <span class="font-semibold">{{ $quotationInvoice->original_name ?? __('orders.view_quotation') }}</span>
                    </div>
                @endif
                <a href="{{ asset('storage/' . $quotationInvoice->file_path) }}" target="_blank" class="inline-flex items-center gap-2 text-sky-600 dark:text-sky-400 hover:underline mt-2">
                    <i class="fa-solid {{ $isQuotationImage ? 'fa-image' : 'fa-file-pdf' }}"></i> {{ __('orders.view_quotation') }}
                </a>
            </div>
        @else
            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                <p class="text-amber-800 dark:text-amber-200 font-semibold">{{ __('orders.quotation_missing') }}</p>
                <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">{{ __('orders.quotation_missing_help') }}</p>
            </div>
        @endif
    </div>

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
                @if ($driverInvoice)
                <div class="space-y-2 text-sm mb-3 p-4 rounded-2xl {{ $order->status === 'pending_confirmation' ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-800/50' }}">
                    <p class="font-bold">{{ __('orders.driver_invoice') }}</p>
                    @if ($isDriverInvoiceImage)
                        <a href="{{ asset('storage/' . $driverInvoice->file_path) }}" target="_blank" class="block mt-2 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 max-w-xs">
                            <img src="{{ asset('storage/' . $driverInvoice->file_path) }}" alt="{{ $driverInvoice->original_name ?? 'Invoice' }}" class="w-full h-40 object-contain bg-white">
                        </a>
                    @endif
                    <a href="{{ asset('storage/' . $driverInvoice->file_path) }}" target="_blank" class="inline-flex items-center gap-2 text-sky-600 hover:underline mt-2">
                        <i class="fa-solid {{ $isDriverInvoiceImage ? 'fa-image' : 'fa-file-pdf' }}"></i> {{ $driverInvoice->original_name ?? __('orders.view_invoice') }}
                    </a>
                </div>
            @endif
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

    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeRejectModal">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl">
                <h3 class="font-black text-lg mb-3">{{ __('orders.reject_request') }}</h3>
                <p class="text-sm text-slate-500 mb-3">{{ __('orders.rejection_reason_optional') }}</p>
                <textarea wire:model="rejection_reason" rows="3" placeholder="{{ __('orders.rejection_reason_placeholder') }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 dark:bg-slate-800"></textarea>
                @error('rejection_reason')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                <div class="flex gap-2 mt-4">
                    <button type="button" wire:click="rejectRequest" class="flex-1 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold">
                        {{ __('orders.reject_confirm') }}
                    </button>
                    <button type="button" wire:click="closeRejectModal" class="px-4 py-2 rounded-xl border border-slate-200 font-semibold">
                        {{ __('common.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
