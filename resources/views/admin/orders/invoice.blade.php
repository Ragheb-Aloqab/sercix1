@extends('admin.layouts.app')

@section('page_title', 'الفاتورة')
@section('subtitle', 'Invoice')

@section('content')
    <div class="space-y-6">

        @if ($invoice)

            <div
                class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <p class="font-black text-xl">فاتورة #{{ $invoice->invoice_number ?? $invoice->id }}</p>
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
                        <p class="text-sm text-slate-500 mt-1">الحالة: {{ ucfirst($invoice->status ?? 'unpaid') }}</p>
                        <p class="text-sm text-slate-500 mt-1">التاريخ:
                            {{ optional($invoice->created_at)->format('Y-m-d H:i') }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.orders.invoice.pdf', $order) }}"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                            <i class="fa-solid fa-file-pdf me-1"></i> تحميل PDF
                        </a>
                        <button onclick="window.print()"
                            class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white font-semibold">
                            <i class="fa-solid fa-print me-1"></i> طباعة
                        </button>
                        <a href="{{ route('admin.orders.show', $order) }}"
                            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                            رجوع للطلب
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-2xl p-4 bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800">
                    <p class="text-sm text-slate-500">الإجمالي</p>
                    <p class="text-2xl font-black">{{ number_format($invoice->total ?? 0, 2) }} ر.س</p>
                </div>
                <div class="rounded-2xl p-4 bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800">
                    <p class="text-sm text-slate-500">المدفوع</p>
                    <p class="text-2xl font-black text-emerald-600">{{ number_format($paidAmount ?? 0, 2) }} ر.س</p>
                </div>
                <div class="rounded-2xl p-4 bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800">
                    <p class="text-sm text-slate-500">المتبقي</p>
                    <p class="text-2xl font-black text-rose-600">{{ number_format($remainingAmount ?? 0, 2) }} ر.س</p>
                </div>
            </div>

            @if ($invoice->order)
                <div
                    class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                    <h2 class="font-black text-lg mb-4">تفاصيل الطلب</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">رقم الطلب</span><span
                                class="font-bold">#{{ $invoice->order->id }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">حالة الطلب</span><span
                                class="font-bold">{{ $invoice->order->status }}</span></div>
                        @if ($invoice->order->vehicle)
                            <div class="flex justify-between"><span class="text-slate-500">المركبة</span><span
                                    class="font-bold">{{ $invoice->order->vehicle->make ?? '' }}
                                    {{ $invoice->order->vehicle->model ?? '' }} —
                                    {{ $invoice->order->vehicle->plate_number ?? '-' }}</span></div>
                        @endif
                    </div>

                    @if ($invoice->order->services && $invoice->order->services->count())
                        <div class="mt-5">
                            <h3 class="font-black mb-3">{{ __('common.services') }}</h3>
                            <div class="overflow-x-auto border rounded-2xl">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                                        <tr class="text-right">
                                            <th class="p-3 font-bold">الخدمة</th>
                                            <th class="p-3 font-bold">الكمية</th>
                                            <th class="p-3 font-bold">سعر الوحدة</th>
                                            <th class="p-3 font-bold">الإجمالي</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @foreach ($invoice->order->services as $svc)
                                            @php
                                                $qty = (float) ($svc->pivot->qty ?? 1);
                                                $unit =
                                                    (float) ($svc->pivot->unit_price ??
                                                        ($svc->pivot->total_price ?? 0));
                                                $rowTotal = (float) ($svc->pivot->total_price ?? $qty * $unit);
                                            @endphp
                                            <tr>
                                                <td class="p-3 font-semibold">{{ $svc->name }}</td>
                                                <td class="p-3">{{ $qty }}</td>
                                                <td class="p-3">{{ number_format($unit, 2) }} ر.س</td>
                                                <td class="p-3 font-semibold">{{ number_format($rowTotal, 2) }} ر.س</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @else
            <div
                class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black">فاتورة الطلب #{{ $order->id }}</h1>
                        <p class="text-sm text-slate-500 mt-1">لا توجد فاتورة لهذا الطلب بعد</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.orders.invoice.store', $order) }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700">
                                إنشاء فاتورة
                            </button>
                        </form>
                        <a href="{{ route('admin.orders.show', $order) }}"
                            class="px-4 py-2 rounded-xl bg-slate-100 text-slate-800 font-semibold hover:bg-slate-200">
                            رجوع للطلب
                        </a>
                    </div>
                </div>
                @if (session('success'))
                    <div class="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800">
                        {{ session('success') }}</div>
                @endif
            </div>
        @endif

    </div>
@endsection
