@php
    $isCompany = auth('company')->check();
    $cardClass = $isCompany ? 'dash-card' : 'rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 p-5';
    $labelClass = $isCompany ? 'text-sm font-bold text-servx-silver-light' : 'text-sm font-bold';
    $inputClass = $isCompany ? 'mt-2 w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 text-servx-silver-light px-4 py-3 outline-none focus:ring-2 focus:ring-sky-500/30' : 'mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none';
    $h2Class = $isCompany ? 'dash-section-title' : 'font-black mb-2';
    $descClass = $isCompany ? 'text-sm text-servx-silver mb-4' : 'text-sm text-slate-500 dark:text-slate-400 mb-4';
@endphp

<div class="{{ $cardClass }}">
    <h2 class="{{ $h2Class }}">{{ __('inspections.settings_title') }}</h2>
    <p class="{{ $descClass }}">{{ __('inspections.settings_desc') }}</p>

    <div class="space-y-4">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" wire:model.defer="is_enabled"
                class="w-5 h-5 rounded {{ $isCompany ? 'border-slate-600 text-emerald-500 focus:ring-emerald-500/30' : 'border-slate-300 text-emerald-600 focus:ring-emerald-500' }}">
            <span class="{{ $isCompany ? 'font-bold text-servx-silver-light' : 'font-bold' }}">{{ __('inspections.enable_required') }}</span>
        </label>

        <div>
            <label class="{{ $labelClass }}">{{ __('inspections.frequency') }}</label>
            <select wire:model.defer="frequency_type" class="{{ $inputClass }}">
                <option value="monthly">{{ __('inspections.frequency_monthly') }}</option>
                <option value="every_x_days">{{ __('inspections.frequency_every_x_days') }}</option>
                <option value="manual">{{ __('inspections.frequency_manual') }}</option>
            </select>
        </div>

        @if ($frequency_type === 'every_x_days')
            <div>
                <label class="{{ $labelClass }}">{{ __('inspections.frequency_days') }}</label>
                <input type="number" wire:model.defer="frequency_days" min="1" max="365"
                    class="{{ $inputClass }}" placeholder="30">
                @error('frequency_days')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div>
            <label class="{{ $labelClass }}">{{ __('inspections.deadline_days') }}</label>
            <input type="number" wire:model.defer="deadline_days" min="1" max="30" class="{{ $inputClass }}">
            <p class="text-xs {{ $isCompany ? 'text-servx-silver' : 'text-slate-500' }} mt-1">{{ __('inspections.deadline_hint') }}</p>
            @error('deadline_days')
                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" wire:model.defer="block_if_overdue"
                class="w-5 h-5 rounded {{ $isCompany ? 'border-slate-600 text-amber-500 focus:ring-amber-500/30' : 'border-slate-300 text-amber-600 focus:ring-amber-500' }}">
            <span class="{{ $isCompany ? 'font-bold text-servx-silver-light' : 'font-bold' }}">{{ __('inspections.block_if_overdue') }}</span>
        </label>
        <p class="text-xs {{ $isCompany ? 'text-servx-silver' : 'text-slate-500' }}">{{ __('inspections.block_if_overdue_hint') }}</p>
    </div>

    <div class="mt-4">
        <button wire:click="save" class="dash-btn dash-btn-primary">
            {{ __('inspections.save_settings') }}
        </button>
    </div>
</div>
