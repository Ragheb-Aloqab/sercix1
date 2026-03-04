@extends('admin.layouts.app')

@section('title', __('vehicles.card_vehicle_images') . ' | ' . ($vehicle->plate_number ?? 'Servx Motors'))
@section('page_title', __('vehicles.card_vehicle_images'))
@section('subtitle', $vehicle->plate_number)

@section('content')
@include('company.partials.glass-start', ['title' => __('vehicles.card_vehicle_images')])

<div class="mb-6">
    <a href="{{ route('company.vehicles.show', $vehicle) }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 hover:bg-slate-700/50 transition-all">
        <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('vehicles.back_to_overview') }}
    </a>
</div>

@if(count($imagesByMonth ?? []) > 0)
    <div class="space-y-8">
        @foreach($imagesByMonth as $monthKey => $monthData)
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <h3 class="text-base font-bold text-slate-300">{{ $monthData['label'] ?? $monthKey }}</h3>
                    <div class="text-sm text-slate-500">
                        {{ __('vehicles.assigned_driver') }}: {{ $monthData['driver_name'] ?? '—' }}
                        · {{ ($monthData['submitted_at'] ?? null)?->translatedFormat('d M Y') ?? '' }}
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    @foreach($monthData['photos'] ?? [] as $item)
                        @php $photo = $item['photo'] ?? $item; $inspection = $item['inspection'] ?? $photo->inspection ?? null; @endphp
                        @if($inspection)
                        <a href="{{ route('company.inspections.photo', [$inspection, $photo]) }}" target="_blank"
                            class="group block rounded-xl overflow-hidden border border-slate-600/50 hover:border-sky-400/50 transition-colors aspect-square relative">
                            <img src="{{ route('company.inspections.photo', [$inspection, $photo]) }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy">
                            @if(($item['has_required'] ?? $inspection->hasRequiredPhotos()))
                                <span class="absolute bottom-1 start-1 px-2 py-0.5 rounded text-xs font-bold bg-emerald-500/80 text-white">{{ __('vehicles.uploaded') }}</span>
                            @endif
                        </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-12 backdrop-blur-sm text-center">
        <p class="text-slate-500">{{ __('vehicles.no_orders') }}</p>
    </div>
@endif

@include('company.partials.glass-end')
@endsection
