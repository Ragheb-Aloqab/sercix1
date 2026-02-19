@extends('errors.layout')

@section('title', __('errors.server_error'))

@section('content')
    <div class="text-center max-w-md">
        <div class="text-8xl font-black text-rose-300 dark:text-rose-600/50 mb-4">500</div>
        <h1 class="text-2xl font-bold mb-2">{{ __('errors.server_error') }}</h1>
        <p class="text-slate-600 dark:text-slate-400 mb-8">{{ $message ?? __('errors.server_error_message') }}</p>
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold hover:opacity-90 transition">
            <i class="fa-solid fa-house"></i> {{ __('errors.go_home') }}
        </a>
    </div>
@endsection
