@extends('admin.layouts.app')

@section('page_title', __('dashboard.payments'))
@section('subtitle', __('company.payments_page_desc'))

@section('content')
@include('company.partials.glass-start', ['title' => __('dashboard.payments')])
<div class="space-y-6">

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        @if(request('order_id'))
            <p class="text-sm text-emerald-400 mt-1 font-semibold">دفعات الطلب #{{ request('order_id') }}</p>
        @endif

        <form class="flex flex-wrap gap-2" method="GET">
            @if(request('order_id'))
                <input type="hidden" name="order_id" value="{{ request('order_id') }}">
            @endif
            <input name="q" value="{{ request('q') }}" placeholder="رقم الدفع أو الطلب"
                class="px-4 py-2 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500">

            <select name="status" class="px-4 py-2 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white">
                <option value="all">كل الحالات</option>
                <option value="pending" @selected(request('status')==='pending')>معلّق</option>
                <option value="paid" @selected(request('status')==='paid')>مدفوع</option>
                <option value="failed" @selected(request('status')==='failed')>فشل</option>
                <option value="refunded" @selected(request('status')==='refunded')>مسترجع</option>
            </select>

            <select name="method" class="px-4 py-2 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white">
                <option value="all">كل الطرق</option>
                <option value="tap" @selected(request('method')==='tap')>Tap</option>
                <option value="cash" @selected(request('method')==='cash')>كاش</option>
                <option value="bank" @selected(request('method')==='bank')>تحويل بنكي</option>
            </select>

            <button class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors">{{ __('common.search') }}</button>
        </form>
    </div>

    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-600/50 text-slate-400">
                <tr>
                    <th class="px-5 py-4 text-end font-bold">#</th>
                    <th class="px-5 py-4 text-end font-bold">الطلب</th>
                    <th class="px-5 py-4 text-end font-bold">المبلغ</th>
                    <th class="px-5 py-4 text-end font-bold">الطريقة</th>
                    <th class="px-5 py-4 text-end font-bold">الحالة</th>
                    <th class="px-5 py-4 text-end font-bold">التاريخ</th>
                    <th class="px-5 py-4 text-start font-bold">{{ __('common.view') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-600/50">
                @forelse($payments as $p)
                    <tr class="hover:bg-slate-700/30 transition-colors">
                        <td class="px-5 py-4 font-bold text-white text-end">#{{ $p->id }}</td>
                        <td class="px-5 py-4 text-white text-end">#{{ $p->order_id }}</td>
                        <td class="px-5 py-4 font-bold text-white text-end">{{ number_format($p->amount,2) }} {{ __('company.sar') }}</td>
                        <td class="px-5 py-4 text-white text-end">{{ $p->method ? strtoupper($p->method) : '—' }}</td>
                        <td class="px-5 py-4 text-end">
                            <span class="text-xs px-3 py-1 rounded-full {{ $p->status==='paid' ? 'bg-emerald-500/30 text-emerald-300 border border-emerald-400/50' : 'bg-amber-500/30 text-amber-300 border border-amber-400/50' }}">
                                {{ $p->status }} <i class="fa-solid fa-{{ $p->status==='paid' ? 'check' : 'xmark' }}"></i>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-400 text-end">{{ $p->created_at->format('Y-m-d') }}</td>
                        <td class="px-5 py-4">
                            <a href="{{ route('company.payments.show', $p) }}" class="font-bold text-sky-400 hover:text-sky-300">
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

    <div class="mt-6">{{ $payments->links() }}</div>
</div>
@include('company.partials.glass-end')
@endsection
