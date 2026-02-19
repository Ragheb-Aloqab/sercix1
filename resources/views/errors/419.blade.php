@extends('errors.layout')

@section('title', __('errors.page_expired'))

@section('content')
    <div class="text-center max-w-md">
        <div class="text-8xl font-black text-amber-300 dark:text-amber-600/50 mb-4">419</div>
        <h1 class="text-2xl font-bold mb-2">{{ __('errors.page_expired') }}</h1>
        <p class="text-slate-600 dark:text-slate-400 mb-6">{{ __('errors.page_expired_message') }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-500 mb-8">{{ __('errors.page_expired_suggestion') }}</p>
        <a href="{{ url()->current() }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold transition">
            <i class="fa-solid fa-rotate-right"></i> {{ __('errors.refresh_page') }}
        </a>
    </div>
@endsection
