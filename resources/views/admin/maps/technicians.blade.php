@extends('admin.layouts.app')

@section('title', 'خريطة الفنيين | SERV.X')
@section('page_title', 'خريطة الفنيين')

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div
            class="xl:col-span-2 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h2 class="text-lg font-black mb-2">الخريطة</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                (حالياً) مكان مخصص للخريطة — لاحقاً نربط Google Maps / Leaflet.
            </p>

            <div
                class="h-[420px] rounded-3xl border border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center">
                <div class="text-center">
                    <i class="fa-solid fa-map-location-dot text-3xl opacity-60"></i>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Map Placeholder</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
            <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
                <h2 class="text-lg font-black">آخر مواقع الفنيين</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">آخر تحديث لكل فني</p>
            </div>

            <div class="p-5 space-y-3">
                @forelse($locations as $loc)
                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                        <p class="font-bold">{{ $loc->technician?->name ?? '—' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            {{ $loc->technician?->phone ?? '' }}
                            {{ $loc->technician?->email ? '• ' . $loc->technician->email : '' }}
                        </p>
                        <p class="text-sm mt-2">
                            Lat: <span class="font-semibold">{{ $loc->lat }}</span>
                            • Lng: <span class="font-semibold">{{ $loc->lng }}</span>
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                            آخر تحديث: {{ optional($loc->tracked_at ?? $loc->updated_at)->format('Y-m-d H:i') }}
                        </p>
                    </div>
                @empty
                    <div class="text-center text-slate-500 py-10">لا توجد مواقع مسجلة بعد</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
