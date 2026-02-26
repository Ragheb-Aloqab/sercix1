@extends('layouts.driver')

@section('title', __('inspections.title'))

@section('content')
<div class="max-w-4xl mx-auto w-full">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="dash-page-title">{{ __('inspections.upload_photos') }}</h1>
        <a href="{{ route('driver.dashboard') }}" class="px-4 py-3 rounded-2xl border border-slate-600/50 text-servx-silver-light font-bold hover:bg-slate-700/50">
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} me-2"></i>{{ __('common.back') }}
        </a>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/40 mb-6">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 text-rose-400 border border-rose-500/40 mb-6">{{ session('error') }}</div>
    @endif

    @if ($pendingInspections->isEmpty())
        <div class="dash-card p-8 text-center mb-6">
            <i class="fa-solid fa-circle-check text-5xl text-emerald-500 mb-4"></i>
            <p class="font-bold text-servx-silver-light">{{ __('inspections.no_pending') }}</p>
            <p class="text-servx-silver mt-2">{{ __('inspections.compliant') }}</p>
        </div>

        @if ($vehicles->isNotEmpty())
            <div class="dash-card p-6">
                <h2 class="dash-section-title mb-4">{{ __('inspections.upload_photos') }}</h2>
                <p class="text-servx-silver text-sm mb-4">{{ __('inspections.upload_when_compliant') }}</p>
                <ul class="space-y-3">
                    @foreach ($vehicles as $v)
                        <li class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-2xl border border-slate-600/40 bg-slate-800/40">
                            <div>
                                <span class="font-bold text-servx-silver-light">{{ $v->plate_number ?? $v->display_name }}</span>
                                @if ($v->make || $v->model)
                                    <p class="text-sm text-servx-silver mt-1">{{ $v->make }} {{ $v->model }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('driver.inspections.request', $v) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold">
                                    <i class="fa-solid fa-camera me-2"></i>{{ __('inspections.upload_photos') }}
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @else
        <div class="dash-card p-6 mb-8">
            <h2 class="dash-section-title">{{ __('inspections.vehicles_pending') }}</h2>
            <ul class="space-y-3">
                @foreach ($pendingInspections as $insp)
                    <li class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-2xl border {{ $insp->isOverdue() ? 'border-red-500/40 bg-red-500/10' : 'border-amber-500/40 bg-amber-500/10' }}">
                        <div>
                            <span class="font-bold text-servx-silver-light">{{ $insp->vehicle->plate_number ?? $insp->vehicle->display_name }}</span>
                            <p class="text-sm text-servx-silver mt-1">{{ __('inspections.due_date') }}: {{ $insp->due_date->translatedFormat('d M Y') }}</p>
                            @if ($insp->isOverdue())
                                <span class="inline-block mt-2 px-2 py-1 rounded-xl text-xs font-bold bg-red-500/20 text-red-400">{{ __('inspections.overdue') }}</span>
                            @endif
                        </div>
                        <a href="{{ route('driver.inspections.upload', $insp) }}"
                            class="px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold">
                            <i class="fa-solid fa-camera me-2"></i>{{ __('inspections.upload_photos') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
