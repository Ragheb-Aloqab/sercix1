@extends('admin.layouts.app')

@section('title', 'طلباتي | SERV.X')
@section('page_title', 'طلباتي')

@section('content')
    <div class="space-y-6">

        @if (session('success'))
            <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
            <div class="p-5 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black">قائمة الطلبات</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">تصفّح طلبات الشركة مع الفلاتر</p>
                </div>

                <a href="{{ route('company.orders.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                    <i class="fa-solid fa-plus"></i>
                    طلب خدمة
                </a>
            </div>

            <div class="p-5">
                <form method="GET" action="{{ route('company.orders.index') }}"
                    class="grid grid-cols-1 md:grid-cols-4 gap-3">

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">الحالة</label>
                        <select name="status"
                            class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                            <option value="">كل الحالات</option>
                            @foreach ($statuses as $s)
                                <option value="{{ $s }}" @selected($status === $s)>{{ $s }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 flex items-end gap-2">
                        <button class="w-full px-4 py-3 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold">
                            تطبيق الفلتر
                        </button>
                        <a href="{{ route('company.orders.index') }}"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 text-center font-bold">
                            مسح
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div
            class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
                <h2 class="text-lg font-black">الطلبات</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-950/40">
                        <tr class="text-slate-600 dark:text-slate-300">
                            <th class="text-start p-4 font-bold">#</th>
                            <th class="text-start p-4 font-bold">Amount</th>
                            <th class="text-start p-4 font-bold">Status</th>
                            <th class="text-start p-4 font-bold">Technician</th>
                            <th class="text-start p-4 font-bold">Created At</th>
                            <th class="text-start p-4 font-bold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $payment = $order->payments?->first();
                                $amount = $payment?->amount ?? $order->total_amount;
                            @endphp

                            <tr class="border-t border-slate-200/70 dark:border-slate-800">
                                <td class="p-4 font-bold">#{{ $order->id }}</td>

                                <td class="p-4 font-bold">
                                    {{ (float)$amount > 0 ? number_format((float) $amount, 2) . ' SAR' : '-' }}
                                </td>

                                <td class="p-4">
                                    <span
                                        class="px-3 py-1 rounded-xl text-xs font-bold
                                        {{ in_array($order->status, ['completed']) ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ in_array($order->status, ['cancelled']) ? 'bg-rose-100 text-rose-700' : '' }}
                                        {{ in_array($order->status, ['pending']) ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ in_array($order->status, ['accepted', 'on_the_way', 'in_progress']) ? 'bg-sky-100 text-sky-700' : '' }}">
                                        {{ $order->status }}
                                    </span>
                                </td>

                                <td class="p-4">
                                    @if ($order->technician)
                                        <div class="font-semibold">{{ $order->technician->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $order->technician->phone ?? '' }}</div>
                                    @else
                                        <span class="text-slate-500">غير مُسنّد</span>
                                    @endif
                                </td>

                                <td class="p-4 text-slate-500">
                                    {{ $order->created_at?->format('Y-m-d H:i') }}
                                </td>

                                <td class="p-4">
                                    <a href="{{ route('company.orders.show', $order->id) }}"
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                                        عرض
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-500">
                                    لا توجد طلبات حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="p-5 border-t border-slate-200/70 dark:border-slate-800">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
