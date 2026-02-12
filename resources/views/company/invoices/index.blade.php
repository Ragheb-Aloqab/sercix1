@extends('admin.layouts.app')

@section('page_title', 'الفواتير')
@section('subtitle', 'فواتير الشركة ')

@section('content')
    <div class="space-y-6">

        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="رقم الفاتورة..."
                class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent">

            <select name="status" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent">
                <option value="">كل الحالات </option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>

            <button class="px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold">{{ __('common.search') }}</button>
        </form>

        @if (session('error'))
            <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 font-semibold text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div
            class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <th class="p-4 text-start">رقم الفاتورة</th>
                            <th class="p-4 text-start">فاتورة</th>
                            <th class="p-4 text-start">الاجمالي</th>
                            <th class="p-4 text-start">المدفوع</th>
                            <th class="p-4 text-start">المتبقي</th>
                            <th class="p-4 text-start">حالة الدفع</th>
                            <th class="p-4 text-start">التاريخ</th>
                            <th class="p-4 text-start">اجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            
                            <tr class="border-t border-slate-200 dark:border-slate-800">

                                <td class="p-4 font-bold">#{{ $invoice->id }}</td>

                                <td class="p-4">{{ $invoice->invoice_number ?? '-' }}</td>

                                <td class="p-4 font-semibold">{{ number_format((float) ($invoice->total ?? 0), 2) }} SAR</td>
                                <td class="p-4 font-semibold text-emerald-700">
                                    {{ number_format((float) ($invoice->paid_amount ?? 0), 2) }} SAR</td>
                                <td class="p-4 font-semibold text-amber-700">
                                    {{ number_format((float) ($invoice->remaining_amount ?? 0), 2) }} SAR</td>

                                <td class="p-4">
                                    @php
                                        $isPaid = ($invoice->remaining_amount ?? 0) <= 0 && (float)($invoice->total ?? 0) > 0;
                                        $isPartial = (float)($invoice->paid_amount ?? 0) > 0 && ($invoice->remaining_amount ?? 0) > 0;
                                    @endphp
                                    @if ($isPaid)
                                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700">
                                            <i class="fa-solid fa-check-circle me-1"></i> مدفوع
                                        </span>
                                    @elseif ($isPartial)
                                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700">
                                            <i class="fa-solid fa-clock me-1"></i> مدفوع جزئياً
                                        </span>
                                    @else
                                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-700">
                                            <i class="fa-solid fa-times-circle me-1"></i> غير مدفوع
                                        </span>
                                    @endif
                                </td>

                                <td class="p-4 text-slate-500">{{ optional($invoice->created_at)->format('Y-m-d') }}</td>

                                <td class="p-4 flex flex-wrap gap-2">
                                    <a href="{{ route('company.invoices.show', $invoice) }}"
                                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                                        عرض
                                         <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <a href="{{ route('company.invoices.pdf', $invoice) }}"
                                        download="invoice-{{ $invoice->invoice_number ?? $invoice->id }}.pdf"
                                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> تحميل PDF
                                    </a>

                                    @if (($invoice->remaining_amount ?? 0) > 0 && $invoice->order_id)

                                        <a href="{{ route('company.payments.index', ['order_id' => $invoice->order_id]) }}"
                                        target="_blank"
                                            class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                                            Pay
                                            <i class="fa-solid fa-credit-card"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-6 text-center text-slate-500"> لا يوجد فواتير </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $invoices->links() }}
        </div>

    </div>
@endsection
