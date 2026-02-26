@php
    $isCompany = auth('company')->check();
    $cardClass = $isCompany ? 'dash-card' : 'rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5';
    $borderClass = $isCompany ? 'border-slate-600/50' : 'border-slate-200 dark:border-slate-800';
    $sessionBg = $isCompany ? 'bg-slate-800/60' : '';
    $currentBg = $isCompany ? 'bg-sky-900/30 border-sky-600/50' : 'bg-sky-50 dark:bg-sky-900/20 border-sky-200 dark:border-sky-800';
    $textMuted = $isCompany ? 'text-servx-silver' : 'text-slate-500 dark:text-slate-400';
@endphp

<div class="{{ $cardClass }}">
    <h2 class="{{ $isCompany ? 'dash-section-title' : 'text-lg font-black' }}">{{ __('settings.sessions_devices') }}</h2>
    <p class="mt-1 text-sm {{ $textMuted }}">{{ __('settings.sessions_devices_desc') }}</p>

    @if (session('success'))
        <div class="mt-4 p-3 rounded-2xl {{ $isCompany ? 'bg-emerald-500/10 border border-emerald-500/40 text-emerald-400' : 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200' }} text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-4 space-y-3">
        @forelse ($this->sessions as $session)
            <div class="flex items-center justify-between gap-4 p-3 rounded-2xl border {{ $borderClass }} {{ $session['is_current'] ? $currentBg : $sessionBg }}">
                <div class="min-w-0">
                    <p class="font-semibold truncate {{ $isCompany ? 'text-servx-silver-light' : '' }}">
                        {{ $session['user_agent'] }}
                        @if ($session['is_current'])
                            <span class="text-sky-400 text-sm">({{ __('settings.this_device') }})</span>
                        @endif
                    </p>
                    <p class="text-xs {{ $textMuted }} mt-0.5">
                        {{ $session['ip_address'] }} · {{ \Carbon\Carbon::createFromTimestamp($session['last_activity'])->diffForHumans() }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-sm {{ $textMuted }} py-4">{{ __('settings.no_sessions') }}</p>
        @endforelse
    </div>

    @if (count($this->sessions) > 1)
        <div class="mt-6 pt-4 border-t {{ $borderClass }}">
            <p class="text-sm {{ $isCompany ? 'text-servx-silver' : 'text-slate-600 dark:text-slate-300' }} mb-3">{{ __('settings.logout_other_devices_desc') }}</p>
            <button wire:click="logoutOtherDevices" wire:loading.attr="disabled"
                class="{{ $isCompany ? 'dash-btn px-4 py-3 rounded-2xl border border-rose-500/50 bg-rose-500/20 text-rose-400 hover:bg-rose-500/30 font-bold' : 'px-4 py-3 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-bold' }} disabled:opacity-50">
                {{ __('settings.logout_other_devices') }}
            </button>
        </div>
    @endif
</div>
