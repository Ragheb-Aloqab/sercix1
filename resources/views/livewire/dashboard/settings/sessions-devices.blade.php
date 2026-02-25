<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black">{{ __('settings.sessions_devices') }}</h2>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('settings.sessions_devices_desc') }}</p>

    @if (session('success'))
        <div class="mt-4 p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-4 space-y-3">
        @forelse ($this->sessions as $session)
            <div class="flex items-center justify-between gap-4 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 {{ $session['is_current'] ? 'bg-sky-50 dark:bg-sky-900/20 border-sky-200 dark:border-sky-800' : '' }}">
                <div class="min-w-0">
                    <p class="font-semibold truncate">
                        {{ $session['user_agent'] }}
                        @if ($session['is_current'])
                            <span class="text-sky-600 dark:text-sky-400 text-sm">({{ __('settings.this_device') }})</span>
                        @endif
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ $session['ip_address'] }} · {{ \Carbon\Carbon::createFromTimestamp($session['last_activity'])->diffForHumans() }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-slate-400 py-4">{{ __('settings.no_sessions') }}</p>
        @endforelse
    </div>

    @if (count($this->sessions) > 1)
        <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-800">
            <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">{{ __('settings.logout_other_devices_desc') }}</p>
            <button wire:click="logoutOtherDevices" wire:loading.attr="disabled"
                class="px-4 py-3 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-bold disabled:opacity-50">
                {{ __('settings.logout_other_devices') }}
            </button>
        </div>
    @endif
</div>
