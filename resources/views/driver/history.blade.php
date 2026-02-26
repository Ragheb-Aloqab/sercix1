@extends('layouts.driver')

@section('title', __('driver.latest_requests'))

@section('content')
<div class="max-w-4xl mx-auto w-full pb-24 lg:pb-0">
    <h1 class="dash-page-title mb-6">{{ __('driver.latest_requests') }}</h1>

    <div class="dash-card">
        @if($requests->isEmpty())
            <p class="text-servx-silver">{{ __('driver.no_requests_yet') }}</p>
        @else
            <ul class="space-y-3">
                @foreach($requestsWithDisplay as $row)
                    <li class="flex items-center justify-between p-4 rounded-2xl border border-slate-600/40 bg-slate-800/40">
                        <div>
                            <span class="font-bold text-servx-silver-light">{{ __('driver.request') }} #{{ $row->request->id }}</span>
                            <span class="text-servx-silver text-sm ms-2">— {{ $row->request->vehicle ? $row->request->vehicle->plate_number : '-' }}</span>
                            <p class="text-xs text-servx-silver mt-1">{{ __('driver.status') }}: {{ $row->statusLabel }} — {{ $row->request->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('driver.request.show', $row->request) }}" class="px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold">{{ __('common.view') }}</a>
                            <span class="px-3 py-1 rounded-xl text-sm font-semibold
                                @if($row->request->status === 'pending_approval') bg-amber-500/20 text-amber-400
                                @elseif($row->request->status === 'rejected') bg-rose-500/20 text-rose-400
                                @elseif($row->request->status === 'completed') bg-emerald-500/20 text-emerald-400
                                @else bg-slate-600/50 text-slate-300 @endif">{{ $row->statusLabel }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
