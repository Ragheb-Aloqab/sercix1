<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
    <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
        <h2 class="text-lg font-black">{{ __('common.services') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('common.services_in_order') }}</p>
    </div>

    <div class="p-5 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-slate-500 dark:text-slate-400">
                <tr class="text-start">
                    <th class="py-3 font-semibold">الخدمة</th>
                    <th class="py-3 font-semibold">الكمية</th>
                    <th class="py-3 font-semibold">سعر الوحدة</th>
                    <th class="py-3 font-semibold">الإجمالي</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800">
                @forelse($itemsWithTotals ?? [] as $row)
                    <tr>
                        <td class="py-4">
                            <p class="font-bold">{{ $row->service->name ?? ('#'.$row->service->id) }}</p>
                            @if(!empty($row->service->description))
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    {{ \Illuminate\Support\Str::limit($row->service->description, 60) }}
                                </p>
                            @endif
                        </td>

                        <td class="py-4">{{ $row->qty }}</td>
                        <td class="py-4">{{ number_format($row->unit, 2) }} <span class="text-xs text-slate-500">SAR</span></td>
                        <td class="py-4 font-bold">{{ number_format($row->total, 2) }} <span class="text-xs text-slate-500">SAR</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-500">لا توجد خدمات</td>
                    </tr>
                @endforelse
            </tbody>

            {{-- ملخص --}}
            @if(($itemsWithTotals ?? collect())->count())
                <tfoot class="border-t border-slate-200/70 dark:border-slate-800">
                    <tr>
                        <td colspan="3" class="py-3 text-end text-slate-500 font-semibold">المجموع الفرعي</td>
                        <td class="py-3 font-bold">{{ number_format($subtotal, 2) }} SAR</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="py-3 text-end text-slate-500 font-semibold">الخصم</td>
                        <td class="py-3 font-bold">{{ number_format($discount, 2) }} SAR</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="py-3 text-end text-slate-500 font-semibold">الضريبة</td>
                        <td class="py-3 font-bold">{{ number_format($tax, 2) }} SAR</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="py-4 text-end font-black">الإجمالي النهائي</td>
                        <td class="py-4 font-black text-lg">{{ number_format($grandTotal, 2) }} SAR</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
