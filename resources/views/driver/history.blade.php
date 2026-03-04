@extends('layouts.driver')

@section('title', __('driver.latest_requests'))

@section('content')
<div class="max-w-4xl mx-auto w-full pb-24 lg:pb-0">
    <h1 class="dash-page-title mb-6">{{ __('driver.latest_requests') }}</h1>

    <div class="dash-card">
        @if($requests->isEmpty())
            <p class="text-slate-600 dark:text-servx-silver">{{ __('driver.no_requests_yet') }}</p>
        @else
            <ul class="space-y-3">
                @foreach($requestsWithDisplay as $row)
                    <li class="flex items-center justify-between p-4 rounded-2xl border border-slate-200 dark:border-slate-600/40 bg-slate-50 dark:bg-slate-800/40 transition-colors duration-300">
                        <div>
                            <span class="font-bold text-slate-900 dark:text-servx-silver-light">{{ __('driver.request') }} #{{ $row->request->id }}</span>
                            <span class="text-slate-600 dark:text-servx-silver text-sm ms-2">— {{ $row->request->vehicle ? $row->request->vehicle->plate_number : '-' }}</span>
                            <p class="text-xs text-slate-500 dark:text-servx-silver mt-1">{{ __('driver.status') }}: {{ $row->statusLabel }} — {{ $row->request->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('driver.maintenance-request.show', $row->request) }}" class="px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold">{{ __('common.view') }}</a>
                            <span class="px-3 py-1 rounded-xl text-sm font-semibold
                                @if($row->request->status === 'new_request') bg-amber-500/20 text-amber-400
                                @elseif($row->request->status === 'rejected') bg-rose-500/20 text-rose-400
                                @elseif($row->request->status === 'closed') bg-emerald-500/20 text-emerald-400
                                @else bg-slate-200 dark:bg-slate-600/50 text-slate-600 dark:text-slate-300 @endif">{{ $row->statusLabel }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
            @if($requests->hasPages())
                <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-600/40">
                    {{ $requests->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
