@extends('admin.layouts.app')

@section('page_title', __('invoice.invoice_details') ?? 'تفاصيل الفاتورة')
@section('subtitle', __('invoice.overview') ?? 'Invoice overview')

@section('content')
@include('company.partials.glass-start', ['title' => __('invoice.invoice') ?? 'فاتورة'] . ' #' . ($invoice->invoice_number ?? $invoice->id))

    <div class="space-y-6">
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-black text-xl text-white">
                        {{ __('invoice.invoice') ?? 'فاتورة' }} #{{ $invoice->invoice_number ?? $invoice->id }}
                    </p>
                    <div class="mt-2 flex items-center gap-3">
                        <div class="inline-block p-2 bg-slate-700/50 border border-slate-500/30 rounded-xl">
                            {!! $barcodeImg !!}
                        </div>
                        <span class="text-xs font-mono text-slate-400">{{ $barcodeData }}</span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">{{ __('invoice.date') ?? 'التاريخ' }}: {{ optional($invoice->created_at)->format('Y-m-d H:i') }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ __('invoice.service') ?? 'الخدمة' }}: {{ $invoice->service_type_label }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ __('invoice.driver_name') }}: {{ $invoice->driver_name ?? '-' }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ __('invoice.driver_phone') ?? 'جوال السائق' }}: {{ $invoice->driver_phone ?? '-' }}</p>
                </div>

                <div class="flex flex-wrap gap-2 shrink-0">
                    <a href="{{ route('company.invoices.pdf', $invoice) }}"
                       download="invoice-{{ $invoice->invoice_number ?? $invoice->id }}.pdf"
                       class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold transition-colors">
                        <i class="fa-solid fa-file-pdf me-1"></i> {{ __('common.download') ?? 'تحميل PDF' }}
                    </a>
                    <a href="{{ route('company.invoices.index') }}"
                       class="px-4 py-2 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white font-semibold hover:bg-slate-700/50 transition-colors">
                        {{ __('common.back') ?? 'رجوع' }}
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('invoice.total') }}</p>
            <p class="text-2xl font-black text-white text-end">
                {{ number_format($invoice->getTotalAttribute(), 2) }} {{ __('company.sar') }}
            </p>
        </div>

        {{-- Vehicle & details --}}
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <h2 class="font-bold text-base text-slate-300 mb-4 text-end">{{ $invoice->isFuel() ? __('fuel.refills_log') : __('invoice.order_details') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                @if($invoice->vehicle)
                    <div class="flex justify-between items-center py-2 border-b border-slate-600/50">
                        <span class="font-bold text-white">{{ trim(($invoice->vehicle->make ?? '') . ' ' . ($invoice->vehicle->model ?? '')) ?: $invoice->vehicle->plate_number }}</span>
                        <span class="text-slate-400">{{ __('invoice.vehicle') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-600/50">
                        <span class="font-bold text-white">{{ $invoice->vehicle->plate_number ?? '-' }}</span>
                        <span class="text-slate-400">{{ __('invoice.plate') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-600/50">
                        <span class="font-bold text-white">{{ $invoice->vehicle->type ?? '-' }}</span>
                        <span class="text-slate-400">{{ __('invoice.vehicle_type') }}</span>
                    </div>
                @endif
                @if($invoice->fuelRefill)
                    <div class="flex justify-between items-center py-2 border-b border-slate-600/50">
                        <span class="font-bold text-white">{{ number_format($invoice->fuelRefill->liters, 1) }} L</span>
                        <span class="text-slate-400">{{ __('fuel.quantity') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-600/50">
                        <span class="font-bold text-white">{{ $invoice->fuelRefill->refilled_at?->format('Y-m-d H:i') ?? '-' }}</span>
                        <span class="text-slate-400">{{ __('fuel.refilled_at') }}</span>
                    </div>
                    @if($invoice->fuelRefill->receipt_path)
                        <div class="md:col-span-2">
                            <span class="text-slate-400 block mb-2 text-end">{{ __('invoice.uploaded_invoice') }}</span>
                            <a href="{{ asset('storage/' . $invoice->fuelRefill->receipt_path) }}" target="_blank" class="inline-block">
                                <img src="{{ asset('storage/' . $invoice->fuelRefill->receipt_path) }}" alt="Receipt" class="max-w-xs rounded-xl border border-slate-500/30" />
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        @if($invoice->order)
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
                <h2 class="font-bold text-base text-slate-300 mb-4 text-end">{{ __('invoice.order_details') ?? 'تفاصيل الطلب' }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-6">
                    <div class="flex justify-between items-center py-2 border-b border-slate-600/50">
                        <span class="font-bold text-white">#{{ $invoice->order->id }}</span>
                        <span class="text-slate-400">{{ __('invoice.order_number') ?? 'رقم الطلب' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-600/50">
                        <span class="font-bold text-white">{{ $invoice->order->status }}</span>
                        <span class="text-slate-400">{{ __('invoice.order_status') ?? 'حالة الطلب' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-600/50">
                        <span class="font-bold text-white">{{ optional($invoice->order->created_at)->format('Y-m-d H:i') }}</span>
                        <span class="text-slate-400">{{ __('invoice.created_at') ?? 'تاريخ الانشاء' }}</span>
                    </div>
                </div>

                @if($invoice->order->services && $invoice->order->services->count())
                    <div class="mt-6">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-sm text-slate-500">{{ $invoice->order->services->count() }} {{ __('common.items') ?? 'items' }}</span>
                            <h3 class="font-bold text-slate-300">{{ __('common.services') }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-slate-600/50">
                                    <tr class="text-slate-400">
                                        <th class="p-3 text-end font-bold">{{ __('invoice.service') ?? 'الخدمة' }}</th>
                                        <th class="p-3 text-end font-bold">{{ __('invoice.price') ?? 'السعر' }}</th>
                                        <th class="p-3 text-end font-bold">{{ __('invoice.duration') ?? 'المدة' }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-600/50">
                                    @foreach($invoice->order->services as $svc)
                                        <tr>
                                            <td class="p-3 font-semibold text-white text-end">{{ $svc->name }}</td>
                                            <td class="p-3 text-white text-end">
                                                {{ number_format((float)($svc->pivot->unit_price ?? $svc->pivot->total_price ?? $svc->base_price ?? 0), 2) }} {{ __('company.sar') }}
                                            </td>
                                            <td class="p-3 text-slate-400 text-end">
                                                {{ $svc->pivot->estimated_minutes ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($driverInvoiceAtt ?? null)
                    <div class="mt-6">
                        <span class="text-slate-400 block mb-2 text-end">{{ __('invoice.uploaded_invoice') }}</span>
                        <a href="{{ asset('storage/' . $driverInvoiceAtt->file_path) }}" target="_blank" class="inline-block">
                            @if(str_ends_with(strtolower($driverInvoiceAtt->file_path ?? ''), '.pdf'))
                                <span class="px-4 py-2 rounded-xl bg-sky-500/30 text-sky-300 font-semibold border border-sky-400/50">
                                    <i class="fa-solid fa-file-pdf me-2"></i>{{ $driverInvoiceAtt->original_name ?? __('invoice.view_details') }}
                                </span>
                            @else
                                <img src="{{ asset('storage/' . $driverInvoiceAtt->file_path) }}" alt="Invoice" class="max-w-xs rounded-xl border border-slate-500/30" />
                            @endif
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>

@include('company.partials.glass-end')
@endsection
