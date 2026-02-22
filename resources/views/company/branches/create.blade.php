@extends('admin.layouts.app')

@section('title', 'إضافة فرع | SERV.X')
@section('page_title', 'إضافة فرع')

@section('content')
@include('company.partials.glass-start', ['title' => __('common.add_branch')])

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm">
            <form method="POST" action="{{ route('company.branches.store') }}" class="space-y-5">
                @csrf

                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-slate-500">املأ بيانات الفرع</p>
                    <a href="{{ route('company.branches.index') }}"
                        class="px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
                        رجوع
                    </a>
                </div>

                @include('company.branches._form')

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button class="px-5 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-black transition-colors">
                        حفظ الفرع
                    </button>
                </div>
            </form>
        </div>

@include('company.partials.glass-end')
@endsection
