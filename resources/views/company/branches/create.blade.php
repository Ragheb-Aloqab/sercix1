@extends('admin.layouts.app')

@section('title', 'إضافة فرع | SERV.X')
@section('page_title', 'إضافة فرع')

@section('content')
    <div class="space-y-6">

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black">إضافة فرع جديد</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">املأ بيانات الفرع</p>
                </div>

                <a href="{{ route('company.branches.index') }}"
                    class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                    رجوع
                </a>
            </div>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <form method="POST" action="{{ route('company.branches.store') }}" class="space-y-5">
                @csrf

                @include('company.branches._form')

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black">
                        حفظ الفرع
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection
