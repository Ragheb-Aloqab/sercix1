{{-- resources/views/livewire/dashboard/settings.blade.php --}}
<div class="space-y-4">

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-2">
        {{-- Admin/User --}}
        @if ($actorType === 'user')
            <button wire:click="setTab('profile')"
                class="{{ $btn }} {{ $tab === 'profile' ? $active : $normal }}">{{ __('settings.my_account') }}</button>
            <button wire:click="setTab('password')"
                class="{{ $btn }} {{ $tab === 'password' ? $active : $normal }}">{{ __('settings.password') }}</button>

            @if ($role === 'admin')
                <button wire:click="setTab('branding')"
                    class="{{ $btn }} {{ $tab === 'branding' ? $active : $normal }}">{{ __('settings.site_branding') }}</button>
                <button wire:click="setTab('invoice')"
                    class="{{ $btn }} {{ $tab === 'invoice' ? $active : $normal }}">{{ __('settings.invoice_data') }}</button>
                <button wire:click="setTab('otp')" class="{{ $btn }} {{ $tab === 'otp' ? $active : $normal }}">{{ __('settings.otp_provider') }}</button>
                <button wire:click="setTab('tap')"
                    class="{{ $btn }} {{ $tab === 'tap' ? $active : $normal }}">{{ __('settings.tap_payments') }}</button>
            @endif
        @endif

        {{-- Company --}}
        @if ($actorType === 'company')
            <button wire:click="setTab('company_profile')"
                class="{{ $btn }} {{ $tab === 'company_profile' ? $active : $normal }}">{{ __('settings.company_data') }}</button>
            <button wire:click="setTab('company_password')"
                class="{{ $btn }} {{ $tab === 'company_password' ? $active : $normal }}">{{ __('settings.company_password') }}</button>
        @endif
    </div>

    {{-- Content --}}
    <div>
        @if ($actorType === 'user' && $tab === 'profile')
            <livewire:dashboard.settings.user-profile />
        @elseif($actorType === 'user' && $tab === 'password')
            <livewire:dashboard.settings.user-password />
        @elseif($actorType === 'user' && $role === 'admin' && $tab === 'branding')
            <livewire:dashboard.settings.system-branding />
        @elseif($actorType === 'user' && $role === 'admin' && $tab === 'invoice')
            <livewire:dashboard.settings.invoice-settings />
        @elseif($actorType === 'user' && $role === 'admin' && $tab === 'otp')
            <livewire:dashboard.settings.otp-provider />
        @elseif($actorType === 'user' && $role === 'admin' && $tab === 'tap')
            <livewire:dashboard.settings.tap-payments />
        @elseif($actorType === 'company' && $tab === 'company_profile')
            <livewire:dashboard.settings.company-profile />
        @elseif($actorType === 'company' && $tab === 'company_password')
            <livewire:dashboard.settings.company-password />
        @endif
    </div>

</div>
