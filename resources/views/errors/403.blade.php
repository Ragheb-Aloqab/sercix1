@extends('errors.layout')

@section('title', __('errors.forbidden'))

@section('content')
    <div class="text-center max-w-md">
        <div class="text-8xl font-black text-slate-300 dark:text-slate-600 mb-4">403</div>
        <h1 class="text-2xl font-bold mb-2">{{ __('errors.forbidden') }}</h1>
        <p class="text-slate-600 dark:text-slate-400 mb-8">{{ __('errors.forbidden_message') }}</p>
        <a href="{{ url()->previous() ?: url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold hover:opacity-90 transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('errors.go_back') }}
        </a>
    </div>
@endsection
