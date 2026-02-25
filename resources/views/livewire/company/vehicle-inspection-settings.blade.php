<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 p-5">
    <h2 class="font-black mb-2">{{ __('inspections.settings_title') }}</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ __('inspections.settings_desc') }}</p>

    <div class="space-y-4">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" wire:model.defer="is_enabled"
                class="w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <span class="font-bold">{{ __('inspections.enable_required') }}</span>
        </label>

        <div>
            <label class="text-sm font-bold">{{ __('inspections.frequency') }}</label>
            <select wire:model.defer="frequency_type"
                class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                <option value="monthly">{{ __('inspections.frequency_monthly') }}</option>
                <option value="every_x_days">{{ __('inspections.frequency_every_x_days') }}</option>
                <option value="manual">{{ __('inspections.frequency_manual') }}</option>
            </select>
        </div>

        @if ($frequency_type === 'every_x_days')
            <div>
                <label class="text-sm font-bold">{{ __('inspections.frequency_days') }}</label>
                <input type="number" wire:model.defer="frequency_days" min="1" max="365"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none"
                    placeholder="30">
                @error('frequency_days')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div>
            <label class="text-sm font-bold">{{ __('inspections.deadline_days') }}</label>
            <input type="number" wire:model.defer="deadline_days" min="1" max="30"
                class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
            <p class="text-xs text-slate-500 mt-1">{{ __('inspections.deadline_hint') }}</p>
            @error('deadline_days')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" wire:model.defer="block_if_overdue"
                class="w-5 h-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
            <span class="font-bold">{{ __('inspections.block_if_overdue') }}</span>
        </label>
        <p class="text-xs text-slate-500">{{ __('inspections.block_if_overdue_hint') }}</p>
    </div>

    <div class="mt-4">
        <button wire:click="save"
            class="px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">
            {{ __('inspections.save_settings') }}
        </button>
    </div>
</div>
