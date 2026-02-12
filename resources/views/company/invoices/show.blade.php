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
                    الحالة: {{ ucfirst($invoice->status) }}
                </p>
                <p class="text-sm text-slate-500 mt-1">
                    التاريخ: {{ optional($invoice->created_at)->format('Y-m-d H:i') }}
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl p-4 bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800">
            <p class="text-sm text-slate-500">الاجمالي</p>
            <p class="text-2xl font-black">
                {{ number_format($invoice->getTotalAttribute(), 2) }} SAR
            </p>
        </div>

        <div class="rounded-2xl p-4 bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800">
            <p class="text-sm text-slate-500">المدفوع</p>
            <p class="text-2xl font-black text-emerald-600">
                {{ number_format($paidAmount ?? 0, 2) }} SAR
            </p>
        </div>

        <div class="rounded-2xl p-4 bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800">
            <p class="text-sm text-slate-500">المتبقي</p>
            <p class="text-2xl font-black text-rose-600">
                {{ number_format($remainingAmount ?? 0, 2) }} SAR
            </p>
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

                @if($invoice->order->vehicle)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">المركبة</span>
                        <span class="font-bold">
                            {{ $invoice->order->vehicle->name ?? $invoice->order->vehicle->plate_number ?? ('Vehicle #' . $invoice->order->vehicle->id) }}
                        </span>
                    </div>
                @endif

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
        </div>
    @endif

    @if($invoice->order && $invoice->order->payments && $invoice->order->payments->count())
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-black text-lg">المدفوعات</h2>
                <a href="{{ route('company.payments.index', ['order_id' => $invoice->order->id]) }}"
                   class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                    عرض الكل
                </a>
            </div>

            <div class="space-y-3">
                @foreach($invoice->order->payments->sortByDesc('id') as $pay)
                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <div class="font-bold">
                                {{ $pay->method ? strtoupper($pay->method) : 'PAYMENT' }}
                            </div>
                            <span class="text-xs font-bold px-3 py-1 rounded-xl
                                {{ $pay->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">
                                {{ $pay->status }}
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-slate-500">
                            المبلغ: <span class="font-semibold text-slate-900 dark:text-white">{{ number_format((float)($pay->amount ?? 0), 2) }} SAR</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">
                            {{ optional($pay->created_at)->format('Y-m-d H:i') }}
                            @if(!empty($pay->paid_at))
                                <span class="mx-2">|</span>
                                 تاريخ الدفع: {{ \Illuminate\Support\Carbon::parse($pay->paid_at)->format('Y-m-d H:i') }}
                            @endif
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('company.payments.show', $pay) }}"
                               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                                عرض
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @php
        $remaining = (float)($remainingAmount ?? 0);
    @endphp

    @if($invoice->order && $remaining > 0)
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <p class="font-black text-lg">دفع المبلغ المتبقي </p>
                    <p class="text-sm text-slate-500 mt-1">
                        المتبقي: {{ number_format($remaining, 2) }} SAR
                    </p>
                </div>

                <a href="{{ route('company.payments.index', ['order_id' => $invoice->order->id]) }}"
                   class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-center">
                    <i class="fa-solid fa-credit-card me-2"></i> الذهاب للدفع
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
