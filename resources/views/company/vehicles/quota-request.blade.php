@extends('admin.layouts.app')

@section('title', __('admin_dashboard.quota_request') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('admin_dashboard.quota_request'))

@section('content')
@include('company.partials.glass-start', ['title' => __('admin_dashboard.quota_request')])

@if (session('info'))
    <div class="mb-6 p-4 rounded-2xl bg-sky-500/20 text-sky-300 border border-sky-400/50">
        {{ session('info') }}
    </div>
@endif

@if ($pendingRequest)
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm">
        <p class="text-slate-300 mb-4">{{ __('admin_dashboard.quota_request_pending') }}</p>
        <p class="text-sm text-slate-500">{{ __('admin_dashboard.requested_count') }}: {{ $pendingRequest->requested_count }}</p>
        <a href="{{ route('company.vehicles.index') }}" class="inline-block mt-4 px-4 py-2 rounded-2xl bg-slate-700/50 text-white font-bold">
            {{ __('common.back') }}
        </a>
    </div>
@else
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm space-y-4">
        <div class="p-3 rounded-xl bg-slate-700/30">
            <p class="text-sm text-slate-400">{{ __('admin_dashboard.quota_usage') }}</p>
            <p class="text-lg font-bold text-white">{{ $usage['current'] }} / {{ $usage['quota'] ?? __('admin_dashboard.unlimited') }}</p>
            @if($usage['quota'])
                <div class="mt-2 h-2 bg-slate-600 rounded-full overflow-hidden">
                    <div class="h-full bg-sky-500 rounded-full" style="width: {{ min(100, $usage['usage_percent']) }}%"></div>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('company.vehicles.quota-request.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-bold text-slate-400">{{ __('admin_dashboard.requested_count') }} *</label>
                <input type="number" name="requested_count" value="{{ old('requested_count', 1) }}" min="1" max="50" required
                    class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                @error('requested_count')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-sm font-bold text-slate-400">{{ __('admin_dashboard.note') }} ({{ __('common.optional') }})</label>
                <textarea name="note" rows="3" class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500">{{ old('note') }}</textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="dash-btn dash-btn-primary">{{ __('admin_dashboard.submit_request') }}</button>
                <a href="{{ route('company.vehicles.index') }}" class="dash-btn dash-btn-secondary">{{ __('common.back') }}</a>
            </div>
        </form>
    </div>
@endif

@include('company.partials.glass-end')
@endsection
