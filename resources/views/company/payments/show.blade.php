@extends('admin.layouts.app')

@section('page_title', __('payments.payment_details') ?? 'Payment Details')
@section('subtitle', __('payments.track_status') ?? 'Track payment status')

@section('content')
@include('company.partials.glass-start', ['title' => __('payments.payment_details') ?? 'تفاصيل المدفوع'])

    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50 mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-red-500/20 text-red-300 border border-red-400/50 mb-6">
            {{ session('error') }}
        </div>
    @endif

    @if (session('info'))
        <div class="p-4 rounded-2xl bg-sky-500/20 text-sky-300 border border-sky-400/50 mb-6">
            {{ session('info') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('payments.amount') ?? 'المبلغ' }}</p>
            <p class="text-2xl font-black text-white text-end">{{ number_format((float) $payment->amount, 2) }} {{ __('company.sar') }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('payments.status') ?? 'الحالة' }}</p>
            <p class="text-end">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold {{ $payment->status === 'paid' ? 'bg-emerald-500/30 text-emerald-300 border border-emerald-400/50' : 'bg-amber-500/30 text-amber-300 border border-amber-400/50' }}">
                    {{ $payment->status === 'paid' ? __('vehicles.paid') : __('payments.unpaid') ?? 'Unpaid' }}
                </span>
            </p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('payments.method') ?? 'طريقة الدفع' }}</p>
            <p class="font-bold text-white text-end">{{ $payment->method ? strtoupper($payment->method) : '—' }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('payments.date') ?? 'التاريخ' }}</p>
            <p class="font-bold text-white text-end">{{ optional($payment->created_at)->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    @if (!empty($payment->paid_at))
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm mb-6">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('payments.paid_at') ?? 'Paid at' }}</p>
            <p class="font-bold text-white text-end">{{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('Y-m-d H:i') }}</p>
        </div>
    @endif

    @if ($payment->status !== 'paid')
        <div class="rounded-2xl bg-amber-500/20 border border-amber-400/50 p-4 text-amber-300 font-semibold text-sm mb-6">
            {{ __('messages.payment_temporarily_disabled') }}
        </div>
    @else
        <div class="rounded-2xl bg-emerald-500/20 border border-emerald-400/50 p-4 text-emerald-300 font-bold text-center mb-6">
            {{ __('payments.already_paid') ?? 'This payment is already paid.' }}
        </div>
    @endif

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('company.orders.show', $payment->order) }}"
            class="px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 hover:bg-slate-700/50 transition-all">
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} me-2"></i> {{ __('common.back') ?? 'رجوع' }}
        </a>
        <a href="{{ route('company.payments.index') }}"
            class="px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 hover:bg-slate-700/50 transition-all">
            {{ __('payments.all_payments') ?? 'كل المدفوعات' }}
        </a>
    </div>

@include('company.partials.glass-end')
@endsection
