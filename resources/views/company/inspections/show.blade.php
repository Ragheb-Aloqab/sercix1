@extends('admin.layouts.app')

@section('title', __('inspections.title') . ' #' . $inspection->id . ' | Servx Motors')
@section('page_title', __('inspections.title') . ' #' . $inspection->id)
@section('subtitle', $inspection->vehicle->plate_number ?? $inspection->vehicle->display_name)

@section('content')
@include('company.partials.glass-start', ['title' => __('inspections.title') . ' — ' . ($inspection->vehicle->plate_number ?? $inspection->vehicle->display_name)])

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <a href="{{ route('company.inspections.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50">
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('common.back') }}
        </a>
        <div class="flex flex-wrap gap-2">
            @if (in_array($inspection->status, ['submitted', 'pending']) && $inspection->photos->count() > 0)
                <a href="{{ route('company.inspections.download', $inspection) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 text-white font-bold hover:bg-slate-700/50">
                    <i class="fa-solid fa-file-zipper"></i> {{ __('inspections.download_zip') }}
                </a>
            @endif
            @if ($inspection->status === 'submitted')
                <form method="POST" action="{{ route('company.inspections.approve', $inspection) }}" class="inline" id="approve-form">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="reviewer_notes" value="">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">
                        <i class="fa-solid fa-check"></i> {{ __('inspections.approve') }}
                    </button>
                </form>
                <form method="POST" action="{{ route('company.inspections.reject', $inspection) }}" class="inline" x-data x-on:submit="if(!confirm('{{ __('inspections.reject') }}?')) $event.preventDefault()">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="reviewer_notes" value="">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold">
                        <i class="fa-solid fa-xmark"></i> {{ __('inspections.reject') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50 mb-6">{{ session('success') }}</div>
    @endif

    {{-- Meta --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
            <p class="text-sm text-slate-400">{{ __('inspections.due_date') }}</p>
            <p class="font-bold text-white">{{ $inspection->due_date->translatedFormat('d M Y') }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
            <p class="text-sm text-slate-400">{{ __('inspections.filter_driver') }}</p>
            <p class="font-bold text-white">{{ $inspection->driver_name ?? '—' }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
            <p class="text-sm text-slate-400">{{ __('inspections.odometer_reading') }}</p>
            <p class="font-bold text-white">{{ $inspection->odometer_reading ? number_format($inspection->odometer_reading) : '—' }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
            <p class="text-sm text-slate-400">{{ __('inspections.filter_status') }}</p>
            @php
                $statusClass = match($inspection->status) {
                    'approved' => 'border-emerald-400/50 bg-emerald-500/20 text-emerald-300',
                    'rejected' => 'border-red-400/50 bg-red-500/20 text-red-300',
                    'submitted' => 'border-sky-400/50 bg-sky-500/20 text-sky-300',
                    default => 'border-amber-400/50 bg-amber-500/20 text-amber-300',
                };
            @endphp
            <span class="inline-block px-2 py-1 rounded-xl text-sm font-bold border {{ $statusClass }}">{{ __('inspections.' . $inspection->status) }}</span>
        </div>
    </div>

    {{-- Reviewer notes (when submitted) --}}
    @if ($inspection->status === 'submitted')
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 mb-6">
            <h3 class="font-bold text-white mb-3">{{ __('inspections.reviewer_notes') }}</h3>
            <form method="POST" action="{{ route('company.inspections.approve', $inspection) }}" id="approve-with-notes">
                @csrf
                @method('PATCH')
                <textarea name="reviewer_notes" rows="2" placeholder="{{ __('inspections.reviewer_notes') }}"
                    class="w-full rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white px-4 py-3 mb-3">{{ $inspection->reviewer_notes }}</textarea>
                <button type="submit" class="px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">{{ __('inspections.approve') }}</button>
            </form>
            <form method="POST" action="{{ route('company.inspections.reject', $inspection) }}" class="mt-4 pt-4 border-t border-slate-600/50" x-data x-on:submit="if(!confirm('{{ __('inspections.reject') }}?')) $event.preventDefault()">
                @csrf
                @method('PATCH')
                <textarea name="reviewer_notes" rows="2" placeholder="{{ __('inspections.reviewer_notes') }} ({{ __('inspections.reject') }})"
                    class="w-full rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white px-4 py-3 mb-2">{{ $inspection->reviewer_notes }}</textarea>
                <button type="submit" class="px-4 py-2 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold">{{ __('inspections.reject') }}</button>
            </form>
        </div>
    @endif

    @if ($inspection->reviewer_notes && in_array($inspection->status, ['approved', 'rejected']))
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4 mb-6">
            <p class="text-sm text-slate-400">{{ __('inspections.reviewer_notes') }}</p>
            <p class="text-white">{{ $inspection->reviewer_notes }}</p>
        </div>
    @endif

    {{-- Photo gallery --}}
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6">
        <h2 class="text-base font-bold text-slate-300 mb-4">{{ __('inspections.gallery') }}</h2>
        @if ($inspection->photos->count())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ($inspection->photos as $photo)
                    <a href="{{ route('company.inspections.photo', ['inspection' => $inspection->id, 'photo' => $photo->id]) }}"
                        target="_blank"
                        class="block rounded-xl overflow-hidden border border-slate-500/30 hover:border-sky-400/50 transition-all group">
                        <div class="aspect-square relative">
                            <img src="{{ route('company.inspections.photo', ['inspection' => $inspection->id, 'photo' => $photo->id]) }}"
                                alt="{{ __('inspections.photo_' . $photo->photo_type) }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                loading="lazy">
                            <span class="absolute bottom-0 left-0 right-0 bg-black/70 px-2 py-1 text-white text-xs font-bold">
                                {{ __('inspections.photo_' . $photo->photo_type) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-slate-500 py-8">{{ __('inspections.upload_required') }}</p>
        @endif
    </div>

    @if ($inspection->driver_notes)
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4 mt-6">
            <p class="text-sm text-slate-400">{{ __('inspections.driver_notes') }}</p>
            <p class="text-white">{{ $inspection->driver_notes }}</p>
        </div>
    @endif

@include('company.partials.glass-end')
@endsection
