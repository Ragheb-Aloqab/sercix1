@php
    $isCompany = auth('company')->check();
    $cardClass = $isCompany ? 'dash-card' : 'rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 p-5';
    $labelClass = $isCompany ? 'text-sm font-bold text-servx-silver-light' : 'text-sm font-bold';
    $inputClass = $isCompany ? 'mt-2 w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 text-servx-silver-light px-4 py-3 outline-none focus:ring-2 focus:ring-sky-500/30' : 'mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none';
    $h2Class = $isCompany ? 'dash-section-title' : 'font-black mb-4';
    $descClass = $isCompany ? 'text-sm text-servx-silver mb-4' : 'text-sm text-slate-500 dark:text-slate-400 mb-4';
@endphp

<div class="space-y-6 font-servx">
    <div>
        <h1 class="{{ $isCompany ? 'dash-page-title' : 'text-2xl font-black' }}">{{ __('livewire.company_settings') }}</h1>
        <p class="{{ $descClass }}">
            {{ __('livewire.company_settings_desc') }}
        </p>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 text-emerald-400 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Profile --}}
    <div class="{{ $cardClass }}">
        <h2 class="{{ $h2Class }}">{{ __('fleet.edit_profile') }}</h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="{{ $labelClass }}">{{ __('livewire.company_name') }}</label>
                <input wire:model.defer="name" type="text" class="{{ $inputClass }}">
                @error('name')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">{{ __('livewire.email') }}</label>
                <input wire:model.defer="email" type="email" class="{{ $inputClass }}">
                @error('email')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">{{ __('livewire.phone_label') }}</label>
                <input wire:model.defer="phone" type="text" class="{{ $inputClass }}">
                @error('phone')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="saveProfile"
                class="dash-btn dash-btn-primary">
                {{ __('livewire.save_changes') }}
            </button>
        </div>
    </div>

    {{-- Tracking API --}}
    <div class="{{ $cardClass }}">
        <h2 class="{{ $h2Class }}">{{ __('tracking.settings_title') }}</h2>
        <p class="{{ $descClass }}">{{ __('tracking.settings_desc') }}</p>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="{{ $labelClass }}">{{ __('tracking.base_url') }}</label>
                <input wire:model.defer="tracking_base_url" type="url"
                    placeholder="{{ __('tracking.base_url_placeholder') }}"
                    class="{{ $inputClass }}">
                @error('tracking_base_url')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="{{ $labelClass }}">{{ __('tracking.api_key') }}</label>
                <div class="mt-2 flex gap-2">
                    <input wire:model.defer="tracking_api_key"
                        type="{{ $show_api_key ? 'text' : 'password' }}"
                        placeholder="{{ __('tracking.api_key_placeholder') }}"
                        class="flex-1 {{ $inputClass }}"
                        autocomplete="new-password">
                    <button type="button" wire:click="$toggle('show_api_key')"
                        class="shrink-0 px-4 py-3 rounded-2xl border {{ $isCompany ? 'border-slate-600/50 bg-slate-800/60 text-servx-silver-light hover:bg-slate-700/60' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800' }} font-semibold text-sm">
                        {{ $show_api_key ? __('common.hide') : __('common.show') }}
                    </button>
                </div>
                <p class="text-xs {{ $isCompany ? 'text-servx-silver' : 'text-slate-500' }} mt-1">{{ __('tracking.api_key_hint') }}</p>
                @error('tracking_api_key')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="saveTrackingSettings"
                class="dash-btn dash-btn-primary">
                {{ __('tracking.save_settings') }}
            </button>
        </div>
    </div>

    {{-- Manage Drivers --}}
    <div class="{{ $cardClass }}">
        <h2 class="{{ $h2Class }}">{{ __('fleet.manage_drivers') }}</h2>
        <p class="{{ $descClass }}">{{ __('fleet.assigned_driver') }} — {{ __('fleet.my_vehicles') }}</p>
        <a href="{{ route('company.vehicles.index') }}" class="dash-btn dash-btn-primary">
            <i class="fa-solid fa-car me-2"></i>{{ __('fleet.my_vehicles') }}
        </a>
    </div>

    {{-- Subscription Plan --}}
    <div class="{{ $cardClass }}">
        <h2 class="{{ $h2Class }}">{{ __('fleet.subscription_plan') }}</h2>
        <p class="{{ $descClass }}">{{ __('fleet.subscription_plan') }} — {{ __('common.view') }}</p>
        <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-600/50">
            <p class="font-bold text-white">{{ __('company.vehicles_count') }}: {{ auth('company')->user()?->vehicles()->count() ?? 0 }}</p>
            <p class="text-sm {{ $isCompany ? 'text-servx-silver' : 'text-slate-500' }} mt-1">{{ __('fleet.subscription_plan') }}</p>
        </div>
    </div>

    {{-- Password --}}
    <div class="{{ $cardClass }}">
        <h2 class="{{ $h2Class }}">{{ __('fleet.change_password') }}</h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="{{ $labelClass }}">{{ __('livewire.current_password') }}</label>
                <input wire:model.defer="current_password" type="password" class="{{ $inputClass }}">
                @error('current_password')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">{{ __('livewire.new_password') }}</label>
                <input wire:model.defer="password" type="password" class="{{ $inputClass }}">
                @error('password')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">{{ __('livewire.confirm_password') }}</label>
                <input wire:model.defer="password_confirmation" type="password" class="{{ $inputClass }}">
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="changePassword" class="dash-btn dash-btn-primary">
                {{ __('livewire.update_password') }}
            </button>
        </div>
    </div>

    {{-- Vehicle Inspection --}}
    <livewire:company.vehicle-inspection-settings />

    {{-- Sessions / Devices --}}
    <livewire:dashboard.settings.sessions-devices />

</div>
