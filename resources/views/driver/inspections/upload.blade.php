@extends('layouts.driver')

@section('title', __('inspections.upload_photos') . ' — ' . ($vehicle->plate_number ?? $vehicle->display_name))

@section('content')
<div class="max-w-2xl mx-auto w-full">
    <div class="mb-6">
        <a href="{{ route('driver.inspections.index') }}" class="inline-flex items-center gap-2 text-slate-600 dark:text-servx-silver hover:text-slate-700 dark:hover:text-servx-silver-light font-semibold transition-colors">
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('common.back') }}
        </a>
    </div>

    <div class="dash-card p-6 mb-6">
        <h1 class="dash-section-title mb-2">{{ __('inspections.upload_photos') }}</h1>
        <p class="text-slate-600 dark:text-servx-silver text-sm mb-4">{{ $vehicle->plate_number ?? $vehicle->display_name }} — {{ __('inspections.due_date') }}: {{ $inspection->due_date->translatedFormat('d M Y') }}</p>

        <form method="POST" action="{{ route('driver.inspections.upload.store', $inspection) }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-4">
                @foreach ($requiredTypes as $type)
                    @php
                        $labelKey = 'inspections.photo_' . $type;
                        $existing = $inspection->getPhotoByType($type);
                        $templateIcon = match($type) {
                            'front' => 'fa-car',
                            'rear' => 'fa-car',
                            'left_side' => 'fa-arrow-left',
                            'right_side' => 'fa-arrow-right',
                            'interior' => 'fa-chair',
                            'odometer' => 'fa-gauge-high',
                            default => 'fa-camera',
                        };
                    @endphp
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-servx-silver-light mb-2">{{ __($labelKey) }} <span class="text-red-400">*</span></label>
                        <div class="flex flex-col sm:flex-row gap-3 items-start">
                            <div class="w-24 h-24 shrink-0 rounded-xl border border-slate-300 dark:border-slate-600/50 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-center transition-colors" title="{{ __('inspections.photo_example') }}">
                                <i class="fa-solid {{ $templateIcon }} text-3xl text-slate-600 dark:text-slate-500"></i>
                            </div>
                            <div class="flex-1 min-w-0 w-full">
                                @if ($existing)
                                    <p class="text-sm text-emerald-400 mb-1">{{ __('common.uploaded') }}</p>
                                @endif
                                <input type="file" name="photo_{{ $type }}" accept="image/jpeg,image/jpg,image/png" required
                                    class="w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 text-slate-900 dark:text-servx-silver-light file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-sky-500/20 file:text-sky-400 file:font-semibold transition-colors duration-300">
                                @error('photo_' . $type)
                                    <p class="text-sm text-rose-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endforeach

                <div>
                    <label class="block font-bold text-slate-700 dark:text-servx-silver-light mb-2">{{ __('inspections.photo_other') }} ({{ __('common.optional') }})</label>
                    <input type="file" name="photo_other[]" accept="image/jpeg,image/jpg,image/png" multiple
                        class="w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 text-slate-900 dark:text-servx-silver-light file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-slate-200 dark:file:bg-slate-600/50 file:text-slate-600 dark:file:text-slate-300 file:font-semibold transition-colors duration-300">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-servx-silver-light mb-2">{{ __('inspections.odometer_reading') }}</label>
                    <input type="number" name="odometer_reading" value="{{ old('odometer_reading') }}" min="0" step="1"
                        class="w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 text-slate-900 dark:text-servx-silver-light transition-colors duration-300" placeholder="0">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-servx-silver-light mb-2">{{ __('inspections.driver_notes') }}</label>
                    <textarea name="driver_notes" rows="3" class="w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 text-slate-900 dark:text-servx-silver-light transition-colors duration-300" placeholder="{{ __('inspections.driver_notes') }}">{{ old('driver_notes') }}</textarea>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="w-full px-4 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-lg">
                    <i class="fa-solid fa-check me-2"></i>{{ __('inspections.submit_inspection') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
