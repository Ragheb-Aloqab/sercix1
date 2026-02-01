{{-- ✅ Mobile (Cards) --}}
<div class="space-y-3 md:hidden">
    @forelse($orders as $order)
        <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white dark:bg-slate-900 p-4">
            {{-- Top --}}
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-black text-slate-900 dark:text-white">#{{ $order->id }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ $order->created_at?->format('Y-m-d') }} • {{ $order->created_at?->format('H:i') }}
                    </p>
                </div>

                <div>
                    @include('admin.orders.partials._status_badge', ['status' => $order->status])
                </div>
            </div>

            {{-- Company --}}
            <div class="mt-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">الشركة</p>
                <p class="font-semibold">{{ $order->company?->name ?? '—' }}</p>
                @if (!empty($order->company?->phone))
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $order->company?->phone }}</p>
                @endif
            </div>

            {{-- Services --}}
            <div class="mt-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">الخدمات</p>
                @if ($order->services->count())
                    <div class="mt-1 flex flex-wrap gap-1">
                        @foreach ($order->services as $service)
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-semibold">
                                {{ $service->name }}
                            </span>
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs text-slate-500">
                        عدد الخدمات: {{ $order->services_count }}
                    </p>
                @else
                    <span class="text-slate-400">—</span>
                @endif
            </div>

            {{-- Payment --}}
            <div class="mt-3 grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">طريقة الدفع</p>
                    <span
                        class="inline-flex px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-xs font-semibold">
                        {{ $order->payment?->method ?? '—' }}
                    </span>
                </div>

                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">حالة الدفع</p>
                    @php($paymentStatus = $order->payment?->status)
                    @if ($paymentStatus === 'paid')
                        <span
                            class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">
                            مدفوع
                        </span>
                    @elseif($paymentStatus === 'partial')
                        <span class="inline-flex px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                            مدفوع جزئي
                        </span>
                    @elseif($paymentStatus === 'failed')
                        <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold">
                            فشل الدفع
                        </span>
                    @else
                        <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">
                            غير مدفوع
                        </span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-4 flex justify-end">
                <a href="{{ route('admin.orders.show', $order) }}"
                    class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold">
                    فتح
                </a>
            </div>
        </div>
    @empty
        <div class="py-8 text-center text-slate-500">
            لا توجد طلبات
        </div>
    @endforelse
</div>

{{-- ✅ Desktop / Tablet (Table) --}}
<div class="hidden md:block">
    <div class="overflow-x-auto rounded-2xl border border-slate-200/70 dark:border-slate-800">
        <table class="min-w-[1000px] w-full text-sm bg-white dark:bg-slate-900">
            <thead class="text-slate-500 dark:text-slate-400">
                <tr class="text-start">
                    <th class="py-3 px-4 font-semibold">رقم</th>
                    <th class="py-3 px-4 font-semibold">الشركة</th>
                    <th class="py-3 px-4 font-semibold">الخدمات</th>
                    <th class="py-3 px-4 font-semibold">تاريخ الطلب</th>
                    <th class="py-3 px-4 font-semibold">الدفع</th>
                    <th class="py-3 px-4 font-semibold">حالة الدفع</th>
                    <th class="py-3 px-4 font-semibold">الحالة</th>
                    <th class="py-3 px-4 font-semibold">إجراءات</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800">
                @forelse($orders as $order)
                    <tr class="align-top">
                        <td class="py-4 px-4 font-bold">#{{ $order->id }}</td>

                        <td class="py-4 px-4">
                            <p class="font-semibold">{{ $order->company?->name ?? '—' }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $order->company?->phone ?? '' }}
                            </p>
                        </td>

                        <td class="py-4 px-4">
                            @if ($order->services->count())
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($order->services as $service)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-semibold">
                                            {{ $service->name }}
                                        </span>
                                    @endforeach
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    عدد الخدمات: {{ $order->services_count }}
                                </p>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            <p class="font-semibold">{{ $order->created_at?->format('Y-m-d') }}</p>
                            <p class="text-xs text-slate-500">{{ $order->created_at?->format('H:i') }}</p>
                        </td>

                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-xs font-semibold">
                                {{ $order->payment?->method ?? '—' }}
                            </span>
                        </td>

                        <td class="py-4 px-4">
                            @php($paymentStatus = $order->payment?->status)
                            @if ($paymentStatus === 'paid')
                                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">
                                    مدفوع
                                </span>
                            @elseif($paymentStatus === 'partial')
                                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                                    مدفوع جزئي
                                </span>
                            @elseif($paymentStatus === 'failed')
                                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold">
                                    فشل الدفع
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">
                                    غير مدفوع
                                </span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            @include('admin.orders.partials._status_badge', ['status' => $order->status])
                        </td>

                        <td class="py-4 px-4">
                            <a href="{{ route('admin.orders.show', $order) }}"
                                class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold">
                                فتح
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">
                            لا توجد طلبات
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
