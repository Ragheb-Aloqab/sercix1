@extends('admin.layouts.app')

@section('title', 'تعديل فرع | SERV.X')
@section('page_title', 'تعديل فرع')

@section('content')
@include('company.partials.glass-start', ['title' => __('common.edit_branch') . ' — ' . $branch->name])

        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-400/50 bg-emerald-500/20 text-emerald-300 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm">
            <form method="POST" action="{{ route('company.branches.update', $branch) }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-slate-500">
                        {{ $branch->name }}
                        @if ($branch->is_default)
                            <span class="ms-2 px-3 py-1 rounded-xl bg-emerald-500/30 text-emerald-300 text-xs font-bold border border-emerald-400/50">Default</span>
                        @endif
                    </p>
                    <a href="{{ route('company.branches.index') }}"
                        class="px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
                        رجوع
                    </a>
                </div>

                @include('company.branches._form', ['branch' => $branch])

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button class="px-5 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-black transition-colors">
                        تحديث
                    </button>
                </div>
            </form>
        </div>

@include('company.partials.glass-end')
@endsection
