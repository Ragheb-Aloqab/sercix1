@php $plan = $plan ?? null; @endphp

<div class="space-y-4">
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="text-xs font-semibold text-slate-400">{{ __('plans.name') }}</label>
            <input name="name" value="{{ old('name', $plan?->name) }}" required
                   class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400">{{ __('plans.slug') }}</label>
            <input name="slug" value="{{ old('slug', $plan?->slug) }}" required placeholder="basic"
                   class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
        </div>
    </div>

    <div>
        <label class="text-xs font-semibold text-slate-400">{{ __('plans.tag') }}</label>
        <input name="tag" value="{{ old('tag', $plan?->tag) }}" placeholder="e.g. Most popular"
               class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
    </div>

    <div>
        <label class="text-xs font-semibold text-slate-400">{{ __('plans.description') }}</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">{{ old('description', $plan?->description) }}</textarea>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="text-xs font-semibold text-slate-400">{{ __('plans.price') }}</label>
            <input name="price" type="number" step="0.01" min="0" value="{{ old('price', $plan?->price) }}" placeholder="Leave empty for Contact us"
                   class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400">{{ __('plans.price_unit') }}</label>
            <input name="price_unit" value="{{ old('price_unit', $plan?->price_unit) }}" placeholder="e.g. per_vehicle_month"
                   class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="text-xs font-semibold text-slate-400">{{ __('plans.sort_order') }}</label>
            <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}"
                   class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
        </div>
        <div class="flex items-end pb-2">
            <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan?->is_active ?? true) ? 'checked' : '' }}
                       class="rounded accent-sky-500">
                {{ __('plans.is_active') }}
            </label>
        </div>
    </div>

    <div class="border-t border-slate-600/50 pt-6">
        <h3 class="text-sm font-bold text-slate-300 mb-3">{{ __('plans.features') }}</h3>
        <div class="grid sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto">
            @foreach($featureLabels as $key => $label)
                <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                    <input type="checkbox" name="features[]" value="{{ $key }}"
                           {{ in_array($key, old('features', $plan?->features ?? []), true) ? 'checked' : '' }}
                           class="rounded accent-sky-500">
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </div>

    <div class="flex gap-2 pt-4">
        <button type="submit" class="dash-btn dash-btn-primary">{{ __('common.save') }}</button>
        <a href="{{ route('admin.plans.index') }}" class="dash-btn dash-btn-secondary">{{ __('common.back') }}</a>
    </div>
</div>
