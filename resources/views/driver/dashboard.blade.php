@extends('layouts.driver')

@section('title', __('driver.dashboard'))

@section('content')
<div class="max-w-4xl mx-auto w-full pb-24 lg:pb-0">
    <h1 class="dash-page-title mb-6">{{ __('driver.my_vehicles_orders') }}</h1>

    {{-- Services grid: 2x2 on mobile, 4 columns on larger screens --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-8">
        <a href="{{ route('driver.request.create') }}"
           class="dash-card dash-card-interactive p-6 flex flex-col items-center justify-center active:scale-95 transition duration-200">
            <span class="w-14 h-14 rounded-full flex items-center justify-center bg-emerald-500/20 text-emerald-400">
                <i class="fa-solid fa-screwdriver-wrench text-xl"></i>
            </span>
            <span class="mt-3 text-sm font-semibold text-servx-silver-light">{{ __('driver.maintenance') }}</span>
        </a>
        <a href="{{ route('driver.fuel-refill.create') }}"
           class="dash-card dash-card-interactive p-6 flex flex-col items-center justify-center active:scale-95 transition duration-200">
            <span class="w-14 h-14 rounded-full flex items-center justify-center bg-amber-500/20 text-amber-400">
                <i class="fa-solid fa-gas-pump text-xl"></i>
            </span>
            <span class="mt-3 text-sm font-semibold text-servx-silver-light">{{ __('fuel.fuel_refill_btn') }}</span>
        </a>
        <a href="{{ route('driver.inspections.index') }}"
           class="dash-card dash-card-interactive p-6 flex flex-col items-center justify-center active:scale-95 transition duration-200 relative">
            @if(($pendingInspectionsCount ?? 0) > 0)
                <span class="absolute top-2 end-2 px-1.5 py-0.5 rounded-full bg-amber-500 text-white text-xs font-bold">{{ $pendingInspectionsCount }}</span>
            @endif
            <span class="w-14 h-14 rounded-full flex items-center justify-center bg-sky-500/20 text-sky-400">
                <i class="fa-solid fa-camera text-xl"></i>
            </span>
            <span class="mt-3 text-sm font-semibold text-servx-silver-light">{{ __('driver.upload_vehicle_images') }}</span>
        </a>
        <a href="{{ $trackingUrl ?? route('driver.dashboard') }}"
           class="dash-card dash-card-interactive p-6 flex flex-col items-center justify-center active:scale-95 transition duration-200">
            <span class="w-14 h-14 rounded-full flex items-center justify-center bg-emerald-500/20 text-emerald-400">
                <i class="fa-solid fa-location-dot text-xl"></i>
            </span>
            <span class="mt-3 text-sm font-semibold text-servx-silver-light">{{ __('tracking.tracking') }}</span>
        </a>
    </div>

    {{-- Latest requests: hidden on mobile (shown in History tab), visible on desktop --}}
    <div class="hidden lg:block dash-card">
        <h2 class="dash-section-title">{{ __('driver.latest_requests') }}</h2>
        @if($requests->isEmpty())
            <p class="text-servx-silver">{{ __('driver.no_requests_yet') }}</p>
        @else
            <ul class="space-y-3">
                @foreach($requestsWithDisplay as $row)
                    <li class="flex items-center justify-between p-4 rounded-2xl border border-slate-600/40 bg-slate-800/40">
                        <div>
                            <span class="font-bold text-servx-silver-light">طلب #{{ $row->request->id }}</span>
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
