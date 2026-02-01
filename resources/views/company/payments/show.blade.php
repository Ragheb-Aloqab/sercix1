@extends('admin.layouts.app')

@section('page_title', 'Payment Details')
@section('subtitle', 'Track payment status')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6">

    <div>
        <h1 class="text-2xl font-black">المدفوع #{{ $payment->id }}</h1>
        <p class="text-sm text-slate-500 mt-1">الطلب #{{ $payment->order_id }}</p>
    </div>

    @if (session('success'))
        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-semibold text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 font-semibold text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-slate-500">المبلغ</p>
                <p class="font-black text-lg">{{ number_format((float) $payment->amount, 2) }} SAR</p>
            </div>

            <div>
                <p class="text-slate-500">الحالة</p>
                <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-bold {{ $payment->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">
                    {{ $payment->status === 'paid' ? 'Paid' : 'Unpaid' }}
                </span>
            </div>

            <div>
                <p class="text-slate-500">طريقة الدفع</p>
                <p class="font-bold">{{ $payment->method ? strtoupper($payment->method) : '—' }}</p>
            </div>

            <div>
                <p class="text-slate-500">التاريخ</p>
                <p>{{ optional($payment->created_at)->format('Y-m-d H:i') }}</p>
            </div>

            @if (!empty($payment->paid_at))
                <div>
                    <p class="text-slate-500">Paid at</p>
                    <p>{{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('Y-m-d H:i') }}</p>
                </div>
            @endif
        </div>
    </div>

    @php
        $mode = request()->string('mode')->toString(); // '' | 'online' | 'bank'
    @endphp

    @if ($payment->status !== 'paid')
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ route('company.payments.show', $payment) }}?mode=online"
                   class="w-full px-5 py-3 rounded-2xl font-black text-center border {{ $mode === 'online' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                    دفع اونلاين
                </a>

                <a href="{{ route('company.payments.show', $payment) }}?mode=bank"
                   class="w-full px-5 py-3 rounded-2xl font-black text-center border {{ $mode === 'bank' ? 'bg-sky-600 text-white border-sky-600' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                    تحويل بنكي
                </a>
            </div>

            @if ($mode === 'online')
                @if (!empty($enabled['tap']))
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-black">دفع اونلاين</p>
                                <p class="text-sm text-slate-500 mt-1">Amount: {{ number_format((float) $payment->amount, 2) }} SAR</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('company.payments.tap', $payment) }}">
                            @csrf
                            <button class="w-full px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black">
                                دفع الان
                            </button>
                        </form>
                    </div>
                @else
                    <div class="p-4 rounded-2xl border border-amber-200 bg-amber-50 text-amber-900 font-semibold text-sm">
                        الدفع اونلاين غير متوفر حاليا
                    </div>
                @endif
            @endif

            @if ($mode === 'bank')
                @if (!empty($enabled['bank']) && $bankAccounts->count())
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                        <div>
                            <p class="font-black">تحويل بنكي</p>
                            <p class="text-sm text-slate-500 mt-1">Amount: {{ number_format((float) $payment->amount, 2) }} SAR</p>
                        </div>

                        <form method="POST" action="{{ route('company.payments.bank.receipt', $payment) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            <div>
                                <label class="text-sm font-bold">حساب بنكي</label>
                                <select name="bank_account_id" required
                                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                                    @foreach ($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}">
                                            {{ $acc->bank_name }} - {{ $acc->account_name }} ({{ $acc->iban }})
                                            @if ($acc->is_default) * @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_account_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm font-bold">اسم المرسل</label>
                                <input name="sender_name" required
                                       class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                                @error('sender_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm font-bold">Transfer receipt</label>
                                <input type="file" name="receipt" accept="image/*" required class="mt-2 w-full text-sm">
                                @error('receipt')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button class="w-full px-5 py-3 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-black">
                                Submit receipt
                            </button>
                        </form>
                    </div>
                @else
                    <div class="p-4 rounded-2xl border border-amber-200 bg-amber-50 text-amber-900 font-semibold text-sm">
                        الدفع البنكي غير متوفر حاليا.
                    </div>
                @endif
            @endif

            @if ($mode !== 'online' && $mode !== 'bank')
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 text-sm">
                    اختر طريقة الدفع للاستمرار
                </div>
            @endif
        </div>
    @else
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 font-bold text-center">
            This payment is already paid.
        </div>
    @endif

</div>
@endsection
