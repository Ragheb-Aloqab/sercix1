@extends('admin.layouts.app')

@section('page_title', 'تفاصيل الفاتورة')
@section('subtitle', 'Invoice overview')

@section('content')
<div class="space-y-6">

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-black text-xl">
                    فاتورة #{{ $invoice->invoice_number ?? $invoice->id }}
                </p>
                @php
                    $barcodeData = $invoice->invoice_number ?? 'INV-' . $invoice->id;
                    $barcodeGen = new \Picqer\Barcode\BarcodeGeneratorSVG();
                    $barcodeImg = $barcodeGen->getBarcode($barcodeData, $barcodeGen::TYPE_CODE_128, 2, 40);
                @endphp
                <div class="mt-2 flex items-center gap-3">
                    <div class="inline-block p-2 bg-white border border-slate-200 rounded-lg">
                        {!! $barcodeImg !!}
                    </div>
                    <span class="text-xs font-mono text-slate-600">{{ $barcodeData }}</span>
                </div>
                <p class="text-sm text-slate-500 mt-1">
                    التاريخ: {{ optional($invoice->created_at)->format('Y-m-d H:i') }}
                </p>
                <p class="text-sm text-slate-500 mt-1">
                    {{ __('invoice.service') }}: {{ $invoice->service_type_label }}
                </p>
                <p class="text-sm text-slate-500 mt-1">
                    {{ __('invoice.driver_name') }}: {{ $invoice->driver_name ?? '-' }}
                </p>
                <p class="text-sm text-slate-500 mt-1">
                    {{ __('invoice.driver_phone') }}: {{ $invoice->driver_phone ?? '-' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('company.invoices.pdf', $invoice) }}"
                   download="invoice-{{ $invoice->invoice_number ?? $invoice->id }}.pdf"
                   class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                    <i class="fa-solid fa-file-pdf me-1"></i> تحميل PDF
                </a>

                <a href="{{ route('company.invoices.index') }}"
                   class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                    رجوع
                </a>
            </div>
        </div>
    </div>

    <div class="rounded-2xl p-4 bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800">
        <p class="text-sm text-slate-500">{{ __('invoice.total') }}</p>
        <p class="text-2xl font-black">
            {{ number_format($invoice->getTotalAttribute(), 2) }} SAR
        </p>
    </div>

    {{-- Vehicle & details (order or fuel) --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <h2 class="font-black text-lg mb-4">{{ $invoice->isFuel() ? __('fuel.refills_log') : __('invoice.order_details') }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            @if($invoice->vehicle)
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">{{ __('invoice.vehicle') }}</span>
                    <span class="font-bold">{{ trim(($invoice->vehicle->make ?? '') . ' ' . ($invoice->vehicle->model ?? '')) ?: $invoice->vehicle->plate_number }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">{{ __('invoice.plate') }}</span>
                    <span class="font-bold">{{ $invoice->vehicle->plate_number ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">{{ __('invoice.vehicle_type') }}</span>
                    <span class="font-bold">{{ $invoice->vehicle->type ?? '-' }}</span>
                </div>
            @endif
            @if($invoice->fuelRefill)
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">{{ __('fuel.quantity') }}</span>
                    <span class="font-bold">{{ number_format($invoice->fuelRefill->liters, 1) }} L</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">{{ __('fuel.refilled_at') }}</span>
                    <span class="font-bold">{{ $invoice->fuelRefill->refilled_at?->format('Y-m-d H:i') ?? '-' }}</span>
                </div>
                @if($invoice->fuelRefill->receipt_path)
                    <div class="md:col-span-2">
                        <span class="text-slate-500 block mb-2">{{ __('invoice.uploaded_invoice') }}</span>
                        <a href="{{ asset('storage/' . $invoice->fuelRefill->receipt_path) }}" target="_blank" class="inline-block">
                            <img src="{{ asset('storage/' . $invoice->fuelRefill->receipt_path) }}" alt="Receipt" class="max-w-xs rounded-xl border border-slate-200" />
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if($invoice->order)
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h2 class="font-black text-lg mb-4">تفاصيل الطلب</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500"> رقم الطلب</span>
                    <span class="font-bold">#{{ $invoice->order->id }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-500">حالة الطلب</span>
                    <span class="font-bold">{{ $invoice->order->status }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-500"> تاريخ الانشاء</span>
                    <span class="font-bold">{{ optional($invoice->order->created_at)->format('Y-m-d H:i') }}</span>
                </div>
            </div>

            @if($invoice->order->services && $invoice->order->services->count())
                <div class="mt-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-black">{{ __('common.services') }}</h3>
                        <span class="text-sm text-slate-500">{{ $invoice->order->services->count() }} items</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-950/40">
                                <tr class="text-slate-600 dark:text-slate-300">
                                    <th class="p-3 text-start font-bold">الخدمة</th>
                                    <th class="p-3 text-start font-bold">السعر</th>
                                    <th class="p-3 text-start font-bold">المدة التقريبية</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800">
                                @foreach($invoice->order->services as $svc)
                                    <tr>
                                        <td class="p-3 font-semibold">{{ $svc->name }}</td>
                                        <td class="p-3">
                                            {{ number_format((float)($svc->pivot->unit_price ?? $svc->pivot->total_price ?? $svc->base_price ?? 0), 2) }} SAR
                                        </td>
                                        <td class="p-3">
                                            {{ $svc->pivot->estimated_minutes ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @php $driverInvoiceAtt = $invoice->order->attachments->where('type', 'driver_invoice')->first(); @endphp
            @if($driverInvoiceAtt)
                <div class="mt-5">
                    <span class="text-slate-500 block mb-2">{{ __('invoice.uploaded_invoice') }}</span>
                    <a href="{{ asset('storage/' . $driverInvoiceAtt->file_path) }}" target="_blank" class="inline-block">
                        @if(str_ends_with(strtolower($driverInvoiceAtt->file_path ?? ''), '.pdf'))
                            <span class="px-4 py-2 rounded-xl bg-sky-100 text-sky-700 font-semibold">
                                <i class="fa-solid fa-file-pdf me-2"></i>{{ $driverInvoiceAtt->original_name ?? __('invoice.view_details') }}
                            </span>
                        @else
                            <img src="{{ asset('storage/' . $driverInvoiceAtt->file_path) }}" alt="Invoice" class="max-w-xs rounded-xl border border-slate-200" />
                        @endif
                    </a>
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
