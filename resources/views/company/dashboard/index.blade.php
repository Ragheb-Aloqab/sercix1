@extends('admin.layouts.app')

@section('title', 'لوحة تحكم الشركة | SERV.X')
@section('page_title', 'لوحة تحكم الشركة')

@section('content')
    <div class="space-y-6">

        {{-- Welcome --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6">
            <h1 class="text-2xl font-black">
                مرحبًا 👋 {{ $company->company_name }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                هذه لوحة تحكم شركتك – يمكنك متابعة الطلبات، الفواتير، الفروع، والخدمات.
            </p>
        </div>

        {{-- Quick Stats (placeholder) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div
                class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <p class="text-sm text-slate-500">الطلبات</p>
                <p class="text-3xl font-black mt-2">{{$company->orders->count();}}</p>
            </div>

            <div
                class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <p class="text-sm text-slate-500">الفواتير</p>
                <p class="text-3xl font-black mt-2">{{ $company->invoices->count();}}</p>
            </div>

            <div
                class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <p class="text-sm text-slate-500">الفروع</p>
                <p class="text-3xl font-black mt-2">{{$company->branches->count();}}</p>
            </div>

        </div>

        {{-- Quick Actions --}}
        <div
            class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6">
            <h2 class="text-lg font-black mb-4">إجراءات سريعة</h2>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('company.orders.index') }}"
                    class="px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold">
                    <i class="fa-solid fa-receipt me-2"></i> الطلبات
                </a>

                <a href="{{ route('company.invoices.index') }}"
                    class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                    <i class="fa-solid fa-file-invoice me-2"></i> الفواتير
                </a>

                <a href="{{ route('company.branches.index') }}"
                    class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold">
                    <i class="fa-solid fa-code-branch me-2"></i> الفروع
                </a>
            </div>
        </div>

    </div>
@endsection
