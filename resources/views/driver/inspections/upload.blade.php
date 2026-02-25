@extends('layouts.driver')

@section('title', __('inspections.upload_photos') . ' — ' . ($vehicle->plate_number ?? $vehicle->display_name))

@section('content')
<div class="max-w-2xl mx-auto w-full">
    <div class="mb-6">
        <a href="{{ route('driver.inspections.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-800 font-semibold">
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('common.back') }}
        </a>
    </div>

    <div class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 mb-6">
        <h1 class="text-xl font-black mb-2">{{ __('inspections.upload_photos') }}</h1>
        <p class="text-slate-500 text-sm mb-4">{{ $vehicle->plate_number ?? $vehicle->display_name }} — {{ __('inspections.due_date') }}: {{ $inspection->due_date->translatedFormat('d M Y') }}</p>

        <form method="POST" action="{{ route('driver.inspections.upload.store', $inspection) }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-4">
                @foreach ($requiredTypes as $type)
                    @php
                        $labelKey = 'inspections.photo_' . $type;
                        $existing = $inspection->getPhotoByType($type);
                    @endphp
                    <div>
                        <label class="block font-bold text-slate-800 mb-2">{{ __($labelKey) }} <span class="text-red-500">*</span></label>
                        @if ($existing)
                            <p class="text-sm text-emerald-600 mb-1">{{ __('common.uploaded') }}</p>
                        @endif
                        <input type="file" name="photo_{{ $type }}" accept="image/jpeg,image/jpg,image/png" required
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-700 file:font-semibold">
                        @error('photo_' . $type)
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                <div>
                    <label class="block font-bold text-slate-800 mb-2">{{ __('inspections.photo_other') }} ({{ __('common.optional') }})</label>
                    <input type="file" name="photo_other[]" accept="image/jpeg,image/jpg,image/png" multiple
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700 file:font-semibold">
                </div>

                <div>
                    <label class="block font-bold text-slate-800 mb-2">{{ __('inspections.odometer_reading') }}</label>
                    <input type="number" name="odometer_reading" value="{{ old('odometer_reading') }}" min="0" step="1"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="0">
                </div>

                <div>
                    <label class="block font-bold text-slate-800 mb-2">{{ __('inspections.driver_notes') }}</label>
                    <textarea name="driver_notes" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="{{ __('inspections.driver_notes') }}">{{ old('driver_notes') }}</textarea>
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
