@extends('layouts.driver')

@section('title', __('driver.maintenance_request') . ' #' . $request->id)

@section('content')
<div class="max-w-2xl mx-auto w-full">
    <h1 class="dash-page-title mb-6">{{ __('driver.maintenance_request') ?? 'طلب صيانة' }} #{{ $request->id }}</h1>

    <div class="dash-card space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-servx-silver">{{ __('common.status') ?? 'الحالة' }}</span>
            <span class="px-3 py-1 rounded-xl text-sm font-semibold
                @if($request->status === 'new_request') bg-amber-500/20 text-amber-400
                @elseif($request->status === 'rejected') bg-rose-500/20 text-rose-400
                @elseif($request->status === 'closed') bg-emerald-500/20 text-emerald-400
                @else bg-sky-500/20 text-sky-400 @endif">{{ $request->status_label }}</span>
        </div>

        @if ($request->status === 'rejected' && $request->rejection_reason)
            <p class="text-sm text-rose-400">{{ __('orders.rejection_reason') ?? 'سبب الرفض' }}: {{ $request->rejection_reason }}</p>
        @endif

        <div class="flex items-center justify-between">
            <span class="text-servx-silver">{{ __('driver.maintenance_type') ?? 'نوع الصيانة' }}</span>
            <span class="font-bold text-servx-silver-light">{{ \App\Enums\MaintenanceType::tryFrom($request->maintenance_type)?->label() ?? $request->maintenance_type }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-servx-silver">{{ __('driver.description') ?? 'الوصف' }}</span>
            <span class="font-bold text-servx-silver-light">{{ $request->description }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-servx-silver">{{ __('driver.vehicle') ?? 'المركبة' }}</span>
            <span class="font-bold text-servx-silver-light">{{ $request->vehicle?->plate_number ?? '-' }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-servx-silver">{{ __('vehicles.make_model') ?? 'Make / Model' }}</span>
            <span class="font-bold text-servx-silver-light">{{ trim(($request->vehicle?->make ?? '') . ' ' . ($request->vehicle?->model ?? '')) ?: '-' }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-servx-silver">{{ __('vehicles.year') ?? 'Year' }}</span>
            <span class="font-bold text-servx-silver-light">{{ $request->vehicle?->year ?? '-' }}</span>
        </div>
    </div>

    @if ($request->requestImages->isNotEmpty())
        <div class="mt-6 dash-card">
            <h3 class="dash-section-title">{{ __('driver.request_images') ?? 'صور الطلب' }}</h3>
            <div class="grid grid-cols-2 gap-3 mt-2">
                @foreach ($request->requestImages as $img)
                    <a href="{{ asset('storage/' . $img->file_path) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-600/50">
                        <img src="{{ asset('storage/' . $img->file_path) }}" alt="" class="w-full h-32 object-cover">
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-8">
        <a href="{{ route('driver.dashboard') }}" class="px-6 py-3 rounded-2xl border border-slate-600/50 hover:bg-slate-700/50 font-bold inline-block text-servx-silver-light">
            <i class="fa-solid fa-arrow-right me-2"></i>{{ __('orders.back') ?? 'رجوع' }}
        </a>
    </div>
</div>
@endsection
