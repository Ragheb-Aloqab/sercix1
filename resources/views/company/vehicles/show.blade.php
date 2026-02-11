@extends('admin.layouts.app')

@section('title', 'تفاصيل المركبة | ' . ($vehicle->plate_number ?? 'SERV.X'))
@section('page_title', 'تفاصيل المركبة')
@section('subtitle', $vehicle->plate_number . ' — ' . trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')))

@section('content')
    <div class="space-y-6">

        {{-- Back + Edit --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('company.vehicles.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold hover:bg-slate-50 dark:hover:bg-slate-800">
                <i class="fa-solid fa-arrow-right"></i> رجوع للمركبات
            </a>
            <a href="{{ route('company.vehicles.edit', $vehicle) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold">
                <i class="fa-solid fa-pen"></i> تعديل المركبة
            </a>
        </div>

        {{-- Vehicle details --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6">
            <h2 class="text-lg font-black mb-4">معلومات المركبة</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">لوحة المركبة</p>
                    <p class="font-bold mt-1">{{ $vehicle->plate_number ?? '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">الماركة / الموديل</p>
                    <p class="font-bold mt-1">{{ trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) ?: '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">سنة الصنع</p>
                    <p class="font-bold mt-1">{{ $vehicle->year ?? '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">VIN</p>
                    <p class="font-bold mt-1 font-mono">{{ $vehicle->vin ?? '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">الفرع</p>
                    <p class="font-bold mt-1">{{ $vehicle->branch?->name ?? '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">الحالة</p>
                    <p class="font-bold mt-1">
                        @if ($vehicle->is_active)
                            <span class="px-2 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">نشط</span>
                        @else
                            <span class="px-2 py-1 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold">غير نشط</span>
                        @endif
                    </p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">اسم السائق</p>
                    <p class="font-bold mt-1">{{ $vehicle->driver_name ?? '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">جوال السائق</p>
                    <p class="font-bold mt-1 font-mono">{{ $vehicle->driver_phone ?? '—' }}</p>
                </div>
                @if ($vehicle->notes)
                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 sm:col-span-2">
                        <p class="text-slate-500 dark:text-slate-400">ملاحظات</p>
                        <p class="font-bold mt-1">{{ $vehicle->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        @php
            $orders = $vehicle->orders ?? collect();
            $totalOrdersAmount = 0;
            $totalPaid = 0;
            foreach ($orders as $o) {
                $totalOrdersAmount += (float) ($o->total_amount ?? 0);
                $totalPaid += (float) ($o->payments->sum('amount'));
            }
            $statusLabels = [
                'pending' => 'قيد الانتظار',
                'requested' => 'مطلوب',
                'assigned' => 'معيّن لفني',
                'on_the_way' => 'في الطريق',
                'in_progress' => 'قيد التنفيذ',
                'completed' => 'مكتمل',
                'cancelled' => 'ملغى',
                'paid' => 'مدفوع',
            ];
        @endphp

        {{-- Summary --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-slate-500 dark:text-slate-400 text-sm">عدد الطلبات</p>
                <p class="text-2xl font-black mt-1">{{ $orders->count() }}</p>
            </div>
            <div class="rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-slate-500 dark:text-slate-400 text-sm">إجمالي قيمة الطلبات</p>
                <p class="text-2xl font-black mt-1">{{ number_format($totalOrdersAmount, 2) }} ر.س</p>
            </div>
            <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4">
                <p class="text-emerald-700 dark:text-emerald-400 text-sm">إجمالي المدفوع</p>
                <p class="text-2xl font-black mt-1 text-emerald-700 dark:text-emerald-300">{{ number_format($totalPaid, 2) }} ر.س</p>
            </div>
        </div>

        {{-- Orders history (كل الطلبات + الخدمات + المدفوعات) --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
                <h2 class="text-lg font-black">سجل الطلبات والخدمات والمدفوعات</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">كل طلبات هذه المركبة مع الخدمات والمدفوعات</p>
            </div>
            <div class="p-5">
                @if ($orders->count())
                    <div class="space-y-6">
                        @foreach ($orders as $order)
                            @php
                                $orderTotal = (float) ($order->total_amount ?? 0);
                                $orderPaid = (float) ($order->payments->sum('amount'));
                                $orderStatusClass = match($order->status ?? '') {
                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'requested' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <a href="{{ route('company.orders.show', $order) }}" class="font-black text-lg hover:underline">
                                            طلب #{{ $order->id }}
                                        </a>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $orderStatusClass }}">
                                            {{ $statusLabels[$order->status ?? ''] ?? $order->status }}
                                        </span>
                                        <span class="text-slate-500 dark:text-slate-400 text-sm">
                                            {{ $order->created_at?->translatedFormat('d M Y، H:i') ?? $order->created_at }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold">{{ number_format($orderTotal, 2) }} ر.س</span>
                                        <a href="{{ route('company.orders.show', $order) }}"
                                            class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold hover:bg-slate-100 dark:hover:bg-slate-800">
                                            عرض الطلب
                                        </a>
                                    </div>
                                </div>
                                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-slate-500 dark:text-slate-400 font-bold mb-2">الخدمات</p>
                                        @if ($order->services && $order->services->count())
                                            <ul class="space-y-1">
                                                @foreach ($order->services as $s)
                                                    <li class="flex justify-between gap-2">
                                                        <span>{{ $s->name }}</span>
                                                        <span>{{ $s->pivot->qty ?? 1 }} × {{ number_format((float)($s->pivot->unit_price ?? 0), 2) }} = {{ number_format((float)($s->pivot->total_price ?? 0), 2) }} ر.س</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-slate-500">—</p>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-slate-500 dark:text-slate-400 font-bold mb-2">المدفوعات</p>
                                        @if ($order->payments && $order->payments->count())
                                            <ul class="space-y-1">
                                                @foreach ($order->payments as $pay)
                                                    <li class="flex justify-between gap-2">
                                                        <span>
                                                            {{ number_format((float)$pay->amount, 2) }} ر.س
                                                            @if ($pay->method === 'cash') (كاش) @elseif ($pay->method === 'tap') (Tap) @else (بنك) @endif
                                                            — {{ $pay->status === 'paid' ? 'مدفوع' : $pay->status }}
                                                        </span>
                                                        @if ($pay->paid_at)
                                                            <span class="text-slate-500">{{ $pay->paid_at->format('Y-m-d') }}</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-slate-500">لا توجد مدفوعات</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-500 dark:text-slate-400">لا توجد طلبات لهذه المركبة حتى الآن.</p>
                @endif
            </div>
        </div>

    </div>
@endsection
