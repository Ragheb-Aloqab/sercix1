@extends('layouts.driver')

@section('title', __('driver.dashboard'))

@section('content')
<div class="max-w-4xl mx-auto w-full">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-black">{{ __('driver.my_vehicles_orders') }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('driver.fuel-refill.create') }}" class="px-4 py-3 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold">
                <i class="fa-solid fa-gas-pump me-2"></i>{{ __('fuel.fuel_refill_btn') }}
            </a>
            <a href="{{ route('driver.request.create') }}" class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                <i class="fa-solid fa-plus me-2"></i>{{ __('driver.new_service_request') }}
            </a>
        </div>
    </div>

    <div class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 mb-8">
        <h2 class="font-black text-lg mb-4">{{ __('driver.vehicles_linked') }}</h2>
        @if($vehicles->isEmpty())
            <p class="text-slate-500">{{ __('driver.no_vehicles') }}</p>
        @else
            <ul class="space-y-3">
                @foreach($vehicles as $v)
                    <li class="flex items-center justify-between p-4 rounded-2xl border border-slate-100">
                        <div>
                            <span class="font-bold">{{ $v->make }} {{ $v->model }}</span>
                            <span class="text-slate-500 text-sm ms-2">— {{ $v->plate_number }}</span>
                            @if($v->company)<p class="text-xs text-slate-500 mt-1">{{ $v->company->company_name }}</p>@endif
                        </div>
                        <a href="{{ route('driver.request.create') }}?vehicle={{ $v->id }}" class="px-3 py-2 rounded-xl bg-sky-600 text-white text-sm font-semibold">{{ __('driver.request_service') }}</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6">
        <h2 class="font-black text-lg mb-4">{{ __('driver.latest_requests') }}</h2>
        @if($requests->isEmpty())
            <p class="text-slate-500">{{ __('driver.no_requests_yet') }}</p>
        @else
            <ul class="space-y-3">
                @foreach($requestsWithDisplay as $row)
                    <li class="flex items-center justify-between p-4 rounded-2xl border border-slate-100">
                        <div>
                            <span class="font-bold">طلب #{{ $row->request->id }}</span>
                            <span class="text-slate-500 text-sm ms-2">— {{ $row->request->vehicle ? $row->request->vehicle->plate_number : '-' }}</span>
                            <p class="text-xs text-slate-500 mt-1">{{ __('driver.status') }}: {{ $row->statusLabel }} — {{ $row->request->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('driver.request.show', $row->request) }}" class="px-3 py-2 rounded-xl bg-sky-600 text-white text-sm font-semibold">{{ __('common.view') }}</a>
                            <span class="px-3 py-1 rounded-xl text-sm font-semibold
                                @if($row->request->status === 'pending_approval') bg-amber-100 text-amber-800
                                @elseif($row->request->status === 'rejected') bg-rose-100 text-rose-800
                                @elseif($row->request->status === 'completed') bg-emerald-100 text-emerald-800
                                @else bg-slate-100 text-slate-700 @endif">{{ $row->statusLabel }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
