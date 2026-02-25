@extends('layouts.driver')

@section('title', __('inspections.title'))

@section('content')
<div class="max-w-4xl mx-auto w-full">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-black">{{ __('inspections.upload_photos') }}</h1>
        <a href="{{ route('driver.dashboard') }}" class="px-4 py-3 rounded-2xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-50">
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} me-2"></i>{{ __('common.back') }}
        </a>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-100 text-emerald-800 border border-emerald-200 mb-6">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-100 text-rose-800 border border-rose-200 mb-6">{{ session('error') }}</div>
    @endif

    @if ($pendingInspections->isEmpty())
        <div class="rounded-3xl bg-white border border-slate-200 shadow-soft p-8 text-center">
            <i class="fa-solid fa-circle-check text-5xl text-emerald-500 mb-4"></i>
            <p class="font-bold text-slate-800">{{ __('inspections.no_pending') }}</p>
            <p class="text-slate-500 mt-2">{{ __('inspections.compliant') }}</p>
        </div>
    @else
        <div class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 mb-8">
            <h2 class="font-black text-lg mb-4">{{ __('inspections.vehicles_pending') }}</h2>
            <ul class="space-y-3">
                @foreach ($pendingInspections as $insp)
                    <li class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-2xl border {{ $insp->isOverdue() ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50' }}">
                        <div>
                            <span class="font-bold">{{ $insp->vehicle->plate_number ?? $insp->vehicle->display_name }}</span>
                            <p class="text-sm text-slate-500 mt-1">{{ __('inspections.due_date') }}: {{ $insp->due_date->translatedFormat('d M Y') }}</p>
                            @if ($insp->isOverdue())
                                <span class="inline-block mt-2 px-2 py-1 rounded-xl text-xs font-bold bg-red-200 text-red-800">{{ __('inspections.overdue') }}</span>
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
