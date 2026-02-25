@extends('admin.layouts.app')

@section('title', __('inspections.title') . ' | Servx Motors')
@section('page_title', __('inspections.title'))
@section('subtitle', __('inspections.gallery'))

@section('content')
@include('company.partials.glass-start', ['title' => __('inspections.title')])

    {{-- Filters --}}
    <form method="GET" action="{{ route('company.inspections.index') }}" class="flex flex-col sm:flex-row flex-wrap gap-3 mb-6">
        <select name="vehicle_id" class="px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
            <option value="">{{ __('inspections.filter_vehicle') }}</option>
            @foreach($vehicles as $v)
                <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->plate_number ?? $v->display_name }}</option>
            @endforeach
        </select>
        <select name="status" class="px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
            <option value="">{{ __('inspections.filter_status') }}</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('inspections.pending') }}</option>
            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>{{ __('inspections.submitted') }}</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('inspections.approved') }}</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('inspections.rejected') }}</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}" placeholder="{{ __('inspections.filter_date_range') }}"
            class="px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
        <input type="date" name="to" value="{{ request('to') }}"
            class="px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
        <button type="submit" class="px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold">{{ __('vehicles.search') }}</button>
    </form>

    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50 mb-6">{{ session('success') }}</div>
    @endif

    {{-- Summary --}}
    <div class="flex flex-wrap gap-4 mb-6">
        @if($pendingCount > 0)
            <div class="px-4 py-2 rounded-2xl {{ $overdueCount > 0 ? 'bg-red-500/20 text-red-300 border border-red-400/50' : 'bg-amber-500/20 text-amber-300 border border-amber-400/50' }}">
                {{ $pendingCount }} {{ __('inspections.pending') }}
                @if($overdueCount > 0)
                    <span class="font-bold">({{ $overdueCount }} {{ __('inspections.overdue') }})</span>
                @endif
            </div>
        @endif
    </div>

    {{-- Grid --}}
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6">
        @if ($inspections->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($inspections as $inspection)
                    @php
                        $firstPhoto = $inspection->photos->first();
                        $statusClass = match($inspection->status) {
                            'approved' => 'border-emerald-400/50 bg-emerald-500/20 text-emerald-300',
                            'rejected' => 'border-red-400/50 bg-red-500/20 text-red-300',
                            'submitted' => 'border-sky-400/50 bg-sky-500/20 text-sky-300',
                            default => 'border-amber-400/50 bg-amber-500/20 text-amber-300',
                        };
                    @endphp
                    <a href="{{ route('company.inspections.show', $inspection) }}"
                        class="block rounded-2xl border border-slate-500/30 bg-slate-700/30 overflow-hidden hover:border-slate-400/50 transition-all group">
                        <div class="aspect-video bg-slate-800 flex items-center justify-center overflow-hidden">
                            @if ($firstPhoto)
                                <img src="{{ route('company.inspections.photo', ['inspection' => $inspection->id, 'photo' => $firstPhoto->id]) }}"
                                    alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                            @else
                                <i class="fa-solid fa-camera text-4xl text-slate-600"></i>
                            @endif
                        </div>
                        <div class="p-4">
                            <p class="font-bold text-white">{{ $inspection->vehicle->plate_number ?? $inspection->vehicle->display_name }}</p>
                            <p class="text-sm text-slate-400">{{ $inspection->inspection_date->translatedFormat('d M Y') }} · {{ $inspection->driver_name ?? '—' }}</p>
                            <span class="inline-block mt-2 px-2 py-1 rounded-xl text-xs font-bold border {{ $statusClass }}">{{ __('inspections.' . $inspection->status) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-6">{{ $inspections->links() }}</div>
        @else
            <p class="text-slate-500 py-12 text-center">{{ __('inspections.no_inspections') }}</p>
        @endif
    </div>

@include('company.partials.glass-end')
@endsection
