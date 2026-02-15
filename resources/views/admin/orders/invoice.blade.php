@extends('admin.layouts.app')

@section('page_title', __('invoice.invoice'))
@section('subtitle', __('invoice.invoice'))

@section('content')
    <div class="space-y-6">

        @if ($invoice)

            <div
                class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <p class="font-black text-xl">{{ __('invoice.invoice') }} #{{ $invoice->invoice_number ?? $invoice->id }}</p>
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
                        <p class="text-sm text-slate-500 mt-1">{{ __('invoice.status') }}: {{ ucfirst($invoice->status ?? 'unpaid') }}</p>
                        <p class="text-sm text-slate-500 mt-1">{{ __('invoice.date') }}:
                            {{ optional($invoice->created_at)->format('Y-m-d H:i') }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.orders.invoice.pdf', $order) }}"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                            <i class="fa-solid fa-file-pdf me-1"></i> {{ __('invoice.download_pdf') }}
                        </a>
                        <button onclick="window.print()"
                            class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white font-semibold">
                            <i class="fa-solid fa-print me-1"></i> {{ __('invoice.print') }}
                        </button>
                        <a href="{{ route('admin.orders.show', $order) }}"
                            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                            {{ __('invoice.back_to_order') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-2xl p-4 bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800">
                    <p class="text-sm text-slate-500">{{ __('invoice.total') }}</p>
                    <p class="text-2xl font-black">{{ number_format($invoice->total ?? 0, 2) }} {{ __('company.sar') }}</p>
                </div>
                <div class="rounded-2xl p-4 bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800">
                    <p class="text-sm text-slate-500">{{ __('invoice.paid') }}</p>
                    <p class="text-2xl font-black text-emerald-600">{{ number_format($paidAmount ?? 0, 2) }} {{ __('company.sar') }}</p>
                </div>
                <div class="rounded-2xl p-4 bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800">
                    <p class="text-sm text-slate-500">{{ __('invoice.remaining') }}</p>
                    <p class="text-2xl font-black text-rose-600">{{ number_format($remainingAmount ?? 0, 2) }} {{ __('company.sar') }}</p>
                </div>
            </div>

            @if ($invoice->order)
                <div
                    class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                    <h2 class="font-black text-lg mb-4">{{ __('invoice.order_details') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">{{ __('invoice.order_number') }}</span><span
                                class="font-bold">#{{ $invoice->order->id }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">{{ __('invoice.order_status') }}</span><span
                                class="font-bold">{{ \Illuminate\Support\Str::startsWith(__('common.status_' . $invoice->order->status), 'common.') ? $invoice->order->status : __('common.status_' . $invoice->order->status) }}</span></div>
                        @if ($invoice->order->vehicle)
                            <div class="flex justify-between"><span class="text-slate-500">{{ __('invoice.vehicle') }}</span><span
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
                                            <th class="p-3 font-bold">{{ __('invoice.service') }}</th>
                                            <th class="p-3 font-bold">{{ __('invoice.quantity') }}</th>
                                            <th class="p-3 font-bold">{{ __('invoice.unit_price') }}</th>
                                            <th class="p-3 font-bold">{{ __('invoice.total') }}</th>
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
                                                <td class="p-3">{{ number_format($unit, 2) }} {{ __('company.sar') }}</td>
                                                <td class="p-3 font-semibold">{{ number_format($rowTotal, 2) }} {{ __('company.sar') }}</td>
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
                        <h1 class="text-2xl font-black">{{ __('invoice.order_invoice') }} #{{ $order->id }}</h1>
                        <p class="text-sm text-slate-500 mt-1">{{ __('invoice.no_invoice_yet') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.orders.invoice.store', $order) }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700">
                                {{ __('invoice.create_invoice') }}
                            </button>
                        </form>
                        <a href="{{ route('admin.orders.show', $order) }}"
                            class="px-4 py-2 rounded-xl bg-slate-100 text-slate-800 font-semibold hover:bg-slate-200">
                            {{ __('invoice.back_to_order') }}
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
