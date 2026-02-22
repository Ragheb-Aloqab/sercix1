<div class="space-y-6">
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-2xl bg-red-500/20 text-red-300 border border-red-400/50">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-black text-xl text-white">{{ __('orders.order') }} #{{ $order->id }}</p>
                <p class="text-sm text-slate-400 mt-1">{{ __('orders.status_label') }}: {{ \Illuminate\Support\Str::startsWith(__('common.status_' . $order->status), 'common.') ? $order->status : __('common.status_' . $order->status) }}</p>
                @if (in_array($order->status, ['pending_approval', 'approved', 'in_progress', 'pending_confirmation']) && $order->requested_by_name)
                    <p class="text-sm text-slate-400 mt-1">{{ __('orders.requested_by') }}: {{ $order->requested_by_name }}</p>
                @endif
                @if ($order->status === 'rejected' && $order->rejection_reason)
                    <p class="text-sm text-red-400 mt-1">{{ __('orders.rejection_reason') }}: {{ $order->rejection_reason }}</p>
                @endif
                @if ($order->requested_by_name)
                    <p class="text-sm text-slate-400 mt-1">{{ __('orders.driver_name') }}: {{ $order->requested_by_name }}</p>
                @endif
                @if ($order->vehicle)
                    <p class="text-sm text-slate-400 mt-1">{{ __('orders.vehicle_plate') }}: {{ $order->vehicle->plate_number ?? '—' }}</p>
                    <p class="text-sm text-slate-400 mt-1">{{ __('orders.vehicle_name') }}: {{ trim(($order->vehicle->make ?? '').' '.($order->vehicle->model ?? '')) ?: '—' }}</p>
                @endif
                <p class="text-sm text-slate-400 mt-1">{{ __('orders.request_date') }}: {{ $order->created_at?->format('Y-m-d H:i') }}</p>
            </div>
            <div>
                <a href="{{ route('company.orders.index') }}"
                   class="px-4 py-2 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white font-semibold hover:bg-slate-700/50 transition-colors">
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
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
        <h3 class="font-bold text-base text-slate-300 mb-3 text-end">{{ __('orders.quotation_invoice') }}</h3>
        @if ($quotationInvoice)
            <div class="space-y-2 text-sm p-4 rounded-2xl {{ $hasQuotation ? 'bg-emerald-500/20 border border-emerald-400/50' : 'bg-slate-700/50 border border-slate-500/30' }}">
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
            <div class="p-4 rounded-2xl bg-amber-500/20 border border-amber-400/50">
                <p class="text-amber-300 font-semibold">{{ __('orders.quotation_missing') }}</p>
                <p class="text-sm text-amber-400/80 mt-1">{{ __('orders.quotation_missing_help') }}</p>
            </div>
        @endif
    </div>

    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
        <h3 class="font-bold text-base text-slate-300 mb-3 text-end">{{ __('orders.order_details') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="flex items-center justify-between py-2 border-b border-slate-600/50">
                <span class="font-bold text-white">{{ $serviceName }}</span>
                <span class="text-slate-400">{{ __('orders.service') }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-slate-600/50">
                <span class="font-bold text-white">{{ (float)$amount > 0 ? number_format((float) $amount, 2) . ' ' . __('company.sar') : '-' }}</span>
                <span class="text-slate-400">{{ __('orders.amount_required') }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <h3 class="font-bold text-base text-slate-300 mb-3 text-end">{{ __('orders.invoice') }}</h3>
                @if ($driverInvoice)
                <div class="space-y-2 text-sm mb-3 p-4 rounded-2xl {{ $order->status === 'pending_confirmation' ? 'bg-emerald-500/20 border border-emerald-400/50' : 'bg-slate-700/50 border border-slate-500/30' }}">
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
                    <div class="flex items-center justify-between py-2 border-b border-slate-600/50">
                        <span class="font-bold text-white">#{{ $order->invoice->id }}</span>
                        <span class="text-slate-400">{{ __('orders.invoice_number') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-600/50">
                        <span class="font-bold text-white">{{ $order->invoice->total ? number_format((float)$order->invoice->total, 2) . ' ' . __('company.sar') : '-' }}</span>
                        <span class="text-slate-400">{{ __('orders.total') }}</span>
                    </div>
                    <a href="{{ route('company.invoices.show', $order->invoice->id) }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold mt-2 transition-colors">
                        {{ __('orders.view_invoice') }}
                    </a>
                </div>
            @else
                <p class="text-sm text-slate-500">{{ __('orders.no_invoice') }}</p>
            @endif
        </div>
    </div>

    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeRejectModal">
            <div class="bg-slate-800 rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl border border-slate-500/30">
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
