@extends('admin.layouts.app')

@section('page_title','المدفوعات')
@section('subtitle','متابعة الدفعات')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-6">

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black">المدفوعات</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('company.payments_page_desc') }}</p>
            @if(request('order_id'))
                <p class="text-sm text-emerald-600 dark:text-emerald-400 mt-1 font-semibold">دفعات الطلب #{{ request('order_id') }}</p>
            @endif
        </div>

        <form class="flex flex-wrap gap-2" method="GET">
            @if(request('order_id'))
                <input type="hidden" name="order_id" value="{{ request('order_id') }}">
            @endif
            <input name="q" value="{{ request('q') }}" placeholder="رقم الدفع أو الطلب"
                class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent">

            <select name="status" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent">
                <option value="all">كل الحالات</option>
                <option value="pending" @selected(request('status')==='pending')>معلّق</option>
                <option value="paid" @selected(request('status')==='paid')>مدفوع</option>
                <option value="failed" @selected(request('status')==='failed')>فشل</option>
                <option value="refunded" @selected(request('status')==='refunded')>مسترجع</option>
            </select>

            <select name="method" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent">
                <option value="all">كل الطرق</option>
                <option value="tap" @selected(request('method')==='tap')>Tap</option>
                <option value="cash" @selected(request('method')==='cash')>كاش</option>
                <option value="bank" @selected(request('method')==='bank')>تحويل بنكي</option>
            </select>

            <button class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">{{ __('common.search') }}</button>
        </form>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500">
                <tr>
                    <th class="px-5 py-4 text-start font-bold">#</th>
                    <th class="px-5 py-4 text-start font-bold">الطلب</th>
                    <th class="px-5 py-4 text-start font-bold">المبلغ</th>
                    <th class="px-5 py-4 text-start font-bold">الطريقة</th>
                    <th class="px-5 py-4 text-start font-bold">الحالة</th>
                    <th class="px-5 py-4 text-start font-bold">التاريخ</th>
                    <th class="px-5 py-4 text-center font-bold">{{ __('common.view') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800">
                @forelse($payments as $p)
                    <tr>
                        <td class="px-5 py-4 font-bold">#{{ $p->id }}</td>
                        <td class="px-5 py-4">#{{ $p->order_id }}</td>
                        <td class="px-5 py-4 font-bold">{{ number_format($p->amount,2) }} SAR</td>
                        <td class="px-5 py-4">{{ $p->method ? strtoupper($p->method) : '—' }}</td>
                        <td class="px-5 py-4">
                            <span class="text-xs px-3 py-1 rounded-full
                                {{ $p->status==='paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">
                                {{ $p->status }}
                                <i class="fa-solid fa-{{ $p->status==='paid' ? 'check' : 'xmark' }}"></i>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500">{{ $p->created_at->format('Y-m-d') }}</td>
                        <td class="px-5 py-4 text-center">
                            <a href="{{ route('company.payments.show', $p) }}" class="font-bold text-emerald-700 hover:underline">
                                {{ $p->status === 'pending' ? 'دفع / تفاصيل' : 'التفاصيل' }}
                                <i class="fa-solid fa-{{ $p->status === 'pending' ? 'credit-card' : 'eye' }}"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">لا توجد مدفوعات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $payments->links() }}</div>
</div>
@endsection
