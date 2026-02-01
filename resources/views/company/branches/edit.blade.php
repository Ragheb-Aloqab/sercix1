@extends('admin.layouts.app')

@section('title', 'تعديل فرع | SERV.X')
@section('page_title', 'تعديل فرع')

@section('content')
    <div class="space-y-6">

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black">تعديل الفرع</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ $branch->name }}
                        @if ($branch->is_default)
                            <span
                                class="ms-2 px-3 py-1 rounded-xl bg-emerald-600 text-white text-xs font-bold">Default</span>
                        @endif
                    </p>
                </div>

                <a href="{{ route('company.branches.index') }}"
                    class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                    رجوع
                </a>
            </div>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <form method="POST" action="{{ route('company.branches.update', $branch) }}" class="space-y-5">
                @csrf
                @method('PATCH')

                @include('company.branches._form', ['branch' => $branch])

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button class="px-5 py-3 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-black">
                        تحديث
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection
