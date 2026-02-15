<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black">{{ __('settings.account_data') }}</h2>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('common.edit_profile') }}</p>

    @if (session('success'))
        <div class="mt-4 p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-4 space-y-3">
        <input wire:model.defer="name"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="{{ __('settings.name_placeholder') }}">
        @error('name')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <input wire:model.defer="email"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="{{ __('settings.email_placeholder_user') }}">
        @error('email')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <input wire:model.defer="phone"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="{{ __('settings.phone_placeholder_optional') }}">
        @error('phone')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <button wire:click="save"
            class="w-full px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold">
            {{ __('settings.save') }}
        </button>
    </div>
</div>
