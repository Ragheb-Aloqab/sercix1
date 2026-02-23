<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-black">{{ __('livewire.company_settings') }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ __('livewire.company_settings_desc') }}
        </p>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Profile --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 p-5">
        <h2 class="font-black mb-4">{{ __('livewire.company_data') }}</h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-sm font-bold">{{ __('livewire.company_name') }}</label>
                <input wire:model.defer="name" type="text"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                @error('name')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold">{{ __('livewire.email') }}</label>
                <input wire:model.defer="email" type="email"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                @error('email')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold">{{ __('livewire.phone_label') }}</label>
                <input wire:model.defer="phone" type="text"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                @error('phone')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="saveProfile"
                class="px-4 py-2 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold">
                {{ __('livewire.save_changes') }}
            </button>
        </div>
    </div>

    {{-- Tracking API --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 p-5">
        <h2 class="font-black mb-2">{{ __('tracking.settings_title') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ __('tracking.settings_desc') }}</p>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="text-sm font-bold">{{ __('tracking.base_url') }}</label>
                <input wire:model.defer="tracking_base_url" type="url"
                    placeholder="{{ __('tracking.base_url_placeholder') }}"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                @error('tracking_base_url')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="text-sm font-bold">{{ __('tracking.api_key') }}</label>
                <input wire:model.defer="tracking_api_key" type="password"
                    placeholder="{{ __('tracking.api_key_placeholder') }}"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none"
                    autocomplete="new-password">
                <p class="text-xs text-slate-500 mt-1">{{ __('tracking.api_key_hint') }}</p>
                @error('tracking_api_key')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="saveTrackingSettings"
                class="px-4 py-2 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold">
                {{ __('tracking.save_settings') }}
            </button>
        </div>
    </div>

    {{-- Password --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 p-5">
        <h2 class="font-black mb-4">{{ __('livewire.change_password') }}</h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-sm font-bold">{{ __('livewire.current_password') }}</label>
                <input wire:model.defer="current_password" type="password"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                @error('current_password')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold">{{ __('livewire.new_password') }}</label>
                <input wire:model.defer="password" type="password"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                @error('password')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold">{{ __('livewire.confirm_password') }}</label>
                <input wire:model.defer="password_confirmation" type="password"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="changePassword" class="px-4 py-2 rounded-2xl bg-emerald-600 text-white font-bold">
                {{ __('livewire.update_password') }}
            </button>
        </div>
    </div>

</div>
