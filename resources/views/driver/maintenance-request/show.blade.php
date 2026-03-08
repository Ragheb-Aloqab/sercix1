@extends('layouts.driver')

@section('title', __('driver.maintenance_request') . ' #' . $request->id)

@section('content')
<div class="max-w-2xl mx-auto w-full">
    <h1 class="dash-page-title mb-6">{{ __('driver.maintenance_request') ?? 'طلب صيانة' }} #{{ $request->id }}</h1>

    <div class="dash-card space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-slate-600 dark:text-servx-silver">{{ __('common.status') ?? 'الحالة' }}</span>
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
            <span class="text-slate-600 dark:text-servx-silver">{{ __('driver.maintenance_type') ?? 'نوع الصيانة' }}</span>
            <span class="font-bold text-slate-900 dark:text-servx-silver-light">{{ \App\Enums\MaintenanceType::tryFrom($request->maintenance_type)?->label() ?? $request->maintenance_type }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-600 dark:text-servx-silver">{{ __('driver.description') ?? 'الوصف' }}</span>
            <span class="font-bold text-slate-900 dark:text-servx-silver-light">{{ $request->description }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-600 dark:text-servx-silver">{{ __('driver.vehicle') ?? 'المركبة' }}</span>
            <span class="font-bold text-slate-900 dark:text-servx-silver-light">{{ $request->vehicle?->plate_number ?? '-' }}</span>
        </div>
        @if($request->requestServices->isNotEmpty())
        <div class="md:col-span-2">
            <span class="text-slate-600 dark:text-servx-silver block mb-2">{{ __('maintenance.services') ?? 'Services' }}:</span>
            <ul class="list-disc list-inside space-y-1">
                @foreach($request->requestServices as $rs)
                    <li class="font-medium text-slate-900 dark:text-servx-silver-light">{{ $rs->display_name }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        @if($request->approvedCenter)
        <div class="md:col-span-2 mt-4 p-4 rounded-2xl border border-emerald-500/40 bg-emerald-500/10">
            <h3 class="font-bold text-emerald-700 dark:text-emerald-400 mb-2">{{ __('maintenance.approved_center_contact') ?? 'Approved center — contact' }}</h3>
            <p class="font-semibold text-slate-900 dark:text-servx-silver-light">{{ $request->approvedCenter->name }}</p>
            @if($request->approvedCenter->phone)
            <div class="flex flex-wrap gap-3 mt-2">
                <a href="tel:{{ $request->approvedCenter->phone }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-sm">
                    <i class="fa-solid fa-phone"></i> {{ __('maintenance.call') ?? 'Call' }}
                </a>
                @php
                    $pn = preg_replace('/[^0-9]/', '', $request->approvedCenter->phone);
                    if (str_starts_with($pn, '0') && strlen($pn) >= 10) { $pn = '966' . substr($pn, 1); }
                    elseif (!str_starts_with($pn, '966')) { $pn = '966' . $pn; }
                @endphp
                <a href="https://wa.me/{{ $pn }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm">
                    <i class="fa-brands fa-whatsapp"></i> {{ __('maintenance.whatsapp') ?? 'WhatsApp' }}
                </a>
            </div>
            @endif
        </div>
        @endif
        <div class="flex items-center justify-between">
            <span class="text-slate-600 dark:text-servx-silver">{{ __('vehicles.make_model') ?? 'Make / Model' }}</span>
            <span class="font-bold text-slate-900 dark:text-servx-silver-light">{{ trim(($request->vehicle?->make ?? '') . ' ' . ($request->vehicle?->model ?? '')) ?: '-' }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-600 dark:text-servx-silver">{{ __('vehicles.year') ?? 'Year' }}</span>
            <span class="font-bold text-slate-900 dark:text-servx-silver-light">{{ $request->vehicle?->year ?? '-' }}</span>
        </div>
    </div>

    @if ($request->requestImages->isNotEmpty())
        <div class="mt-6 dash-card">
            <h3 class="dash-section-title">{{ __('driver.request_images') ?? 'صور الطلب' }}</h3>
            <div class="grid grid-cols-2 gap-3 mt-2">
                @foreach ($request->requestImages as $img)
                    <a href="{{ asset('storage/' . $img->file_path) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-300 dark:border-slate-600/50">
                        <img src="{{ asset('storage/' . $img->file_path) }}" alt="" class="w-full h-32 object-cover">
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-8">
        <a href="{{ route('driver.dashboard') }}" class="px-6 py-3 rounded-2xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 font-bold inline-block text-slate-700 dark:text-servx-silver-light transition-colors duration-300">
            <i class="fa-solid fa-arrow-right me-2"></i>{{ __('orders.back') ?? 'رجوع' }}
        </a>
    </div>
</div>
@endsection
