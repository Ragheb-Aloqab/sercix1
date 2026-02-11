{{-- resources/views/admin/orders/invoice.blade.php --}}

@extends('admin.layouts.print')

@section('content')
    <div class="max-w-6xl mx-auto p-6" dir="rtl">

        {{-- Header --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black">فاتورة الطلب #{{ $order->id }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $order->invoice?->number ? 'رقم الفاتورة: ' . $order->invoice->number : 'لا توجد فاتورة بعد' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if (!$order->invoice)
                    <form method="POST" action="{{ route('admin.orders.invoice.store', $order) }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700">
                            إنشاء فاتورة
                        </button>
                    </form>
                @endif

                <button onclick="window.print()"
                    class="px-4 py-2 rounded-xl bg-gray-900 text-white font-semibold hover:bg-black">
                    طباعة
                </button>

                <a href="{{ route('admin.orders.show', $order) }}"
                    class="px-4 py-2 rounded-xl bg-gray-100 text-gray-800 font-semibold hover:bg-gray-200">
                    رجوع للطلب
                </a>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Invoice Card --}}
        <div class="mt-6 bg-white border rounded-2xl p-6 print:border-0 print:shadow-none">

            {{-- Top info --}}
            <div class="grid md:grid-cols-2 gap-6">
                {{-- Company / Customer --}}
                <div class="border rounded-2xl p-4">
                    <h2 class="font-black text-lg mb-3">بيانات الشركة</h2>

                    <div class="text-sm space-y-2">
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">الاسم</span>
                            <span class="font-semibold">{{ $order->company?->name ?? '-' }}</span>
                        </div>

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">المدينة</span>
                            <span class="font-semibold">{{ $order->city ?? '-' }}</span>
                        </div>

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">العنوان</span>
                            <span class="font-semibold text-left" dir="ltr">{{ $order->address ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Invoice meta --}}
                <div class="border rounded-2xl p-4">
                    <h2 class="font-black text-lg mb-3">بيانات الفاتورة</h2>

                    <div class="text-sm space-y-2">
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">رقم الفاتورة</span>
                            <span class="font-semibold">{{ $order->invoice?->number ?? '-' }}</span>
                        </div>

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">تاريخ الإصدار</span>
                            <span class="font-semibold">
                                {{ $order->invoice?->issued_at ? \Carbon\Carbon::parse($order->invoice->issued_at)->format('Y-m-d H:i') : '-' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">حالة الطلب</span>
                            <span class="font-semibold">
                                {{ $order->status ?? '-' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">موعد الخدمة</span>
                            <span class="font-semibold">
                                {{ $order->scheduled_at ? \Carbon\Carbon::parse($order->scheduled_at)->format('Y-m-d H:i') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vehicle --}}
            <div class="mt-6 border rounded-2xl p-4">
                <h2 class="font-black text-lg mb-3">بيانات المركبة</h2>

                <div class="grid md:grid-cols-4 gap-3 text-sm">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <div class="text-gray-500">النوع</div>
                        <div class="font-semibold mt-1">{{ $order->vehicle?->make ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <div class="text-gray-500">الموديل</div>
                        <div class="font-semibold mt-1">{{ $order->vehicle?->model ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <div class="text-gray-500">السنة</div>
                        <div class="font-semibold mt-1">{{ $order->vehicle?->year ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <div class="text-gray-500">اللوحة</div>
                        <div class="font-semibold mt-1">{{ $order->vehicle?->plate_number ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Services table --}}
            @php
                $items = $order->services ?? collect();

                $subtotal = $items->sum(function ($s) {
                    return (float) ($s->pivot->total_price ??
                        (float) ($s->pivot->qty ?? 0) * (float) ($s->pivot->unit_price ?? 0));
                });

                // إذا عندك خصم/ضريبة لاحقاً عدّل هنا:
                $discount = 0;
                $tax = 0;

                $grandTotal = max(0, $subtotal - $discount + $tax);

                // المدفوع (لو payment علاقة واحدة فيها amount)
                $paid = (float) ($order->payment?->amount ?? 0);

                // المتبقي
                $due = max(0, $grandTotal - $paid);
            @endphp

            <div class="mt-6">
                <h2 class="font-black text-lg mb-3">{{ __('common.services') }}</h2>

                <div class="overflow-x-auto border rounded-2xl">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-right">
                                <th class="p-3 font-bold">الخدمة</th>
                                <th class="p-3 font-bold">الكمية</th>
                                <th class="p-3 font-bold">سعر الوحدة</th>
                                <th class="p-3 font-bold">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($items as $service)
                                @php
                                    $qty = (float) ($service->pivot->qty ?? 0);
                                    $unit = (float) ($service->pivot->unit_price ?? 0);
                                    $total = (float) ($service->pivot->total_price ?? $qty * $unit);
                                @endphp

                                <tr>
                                    <td class="p-3 font-semibold">{{ $service->name ?? '#' . $service->id }}</td>
                                    <td class="p-3">{{ $qty }}</td>
                                    <td class="p-3">{{ number_format($unit, 2) }}</td>
                                    <td class="p-3 font-semibold">{{ number_format($total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-gray-500">
                                        لا توجد خدمات ضمن هذا الطلب.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Notes --}}
            @if ($order->notes)
                <div class="mt-6 border rounded-2xl p-4">
                    <h2 class="font-black text-lg mb-2">ملاحظات</h2>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $order->notes }}</p>
                </div>
            @endif

            {{-- Totals + Payment --}}
            <div class="mt-6 grid md:grid-cols-2 gap-6">
                <div class="border rounded-2xl p-4">
                    <h2 class="font-black text-lg mb-3">الدفع</h2>

                    <div class="text-sm space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-500">حالة الدفع</span>
                            <span class="font-semibold">{{ $order->payment?->status ?? '-' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500">طريقة الدفع</span>
                            <span class="font-semibold">{{ $order->payment?->method ?? '-' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500">المدفوع</span>
                            <span class="font-semibold">{{ number_format($paid, 2) }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500">المتبقي</span>
                            <span class="font-semibold {{ $due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ number_format($due, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="border rounded-2xl p-4">
                    <h2 class="font-black text-lg mb-3">ملخص المبالغ</h2>

                    <div class="text-sm space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-500">المجموع الفرعي</span>
                            <span class="font-semibold">{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500">الخصم</span>
                            <span class="font-semibold">{{ number_format($discount, 2) }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500">الضريبة</span>
                            <span class="font-semibold">{{ number_format($tax, 2) }}</span>
                        </div>

                        <div class="h-px bg-gray-200 my-2"></div>

                        <div class="flex justify-between text-base">
                            <span class="font-black">الإجمالي النهائي</span>
                            <span class="font-black">{{ number_format($grandTotal, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-8 text-center text-xs text-gray-500">
                <p>تم إنشاء هذه الفاتورة بواسطة النظام — {{ now()->format('Y-m-d H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- Print styles --}}
    <style>
        @media print {
            body {
                background: white !important;
            }

            .print\:border-0 {
                border: 0 !important;
            }

            .print\:shadow-none {
                box-shadow: none !important;
            }
        }
    </style>
@endsection
