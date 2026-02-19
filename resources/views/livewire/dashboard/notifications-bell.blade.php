<div class="relative" x-data="{ open: @entangle('open').live }" @click.away="$wire.close(); open=false" wire:poll.visible.20s="refreshUnread">

    <button type="button"
        class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] w-11 h-11 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0"
        wire:click="toggle">
        <i class="fa-regular fa-bell"></i>

        @if ($unreadCount > 0)
            <span
                class="absolute -top-1 -end-1 min-w-[20px] h-5 px-1 rounded-full bg-rose-600 text-white text-[11px] font-bold grid place-items-center">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-transition
        class="absolute end-0 mt-3 w-[360px] max-w-[92vw] rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden z-50">

        <div class="px-5 py-4 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
            <div class="font-black">{{ __('dashboard.notifications') }}</div>

            <button type="button" wire:click="markAllAsRead"
                class="text-xs font-bold px-3 py-2 min-h-[40px] rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800">
                {{ __('dashboard.mark_all_read') }}
            </button>
        </div>

        <div class="max-h-[420px] overflow-auto">
            @forelse($notifications as $n)
                <button type="button" wire:click="openNotification('{{ $n['id'] }}')"
                    class="w-full text-start px-5 py-4 min-h-[56px] border-b border-slate-200/60 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 active:bg-slate-100 dark:active:bg-slate-700">

                    <div class="flex items-start gap-3">
                        <div
                            class="mt-1 w-2.5 h-2.5 rounded-full {{ $n['isUnread'] ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700' }}">
                        </div>

                        <div class="flex-1">
                            <div class="font-bold text-sm">{{ $n['title'] }}</div>

                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400 space-y-1">
                                @if ($n['message'])
                                    <div class="text-slate-700 dark:text-slate-300">{{ $n['message'] }}</div>
                                @else
                                    @if ($n['companyName'])
                                        <div>{{ $n['companyName'] }}</div>
                                    @endif
                                    @if ($n['orderId'])
                                        <div>{{ __('dashboard.order') }} #{{ $n['orderId'] }}</div>
                                    @endif
                                @endif
                                @if ($n['methodLabel'] && $n['amount'] !== null)
                                    <div>{{ $n['methodLabel'] }} — {{ number_format((float)$n['amount'], 2) }} ر.س</div>
                                @endif
                                <div>{{ data_get($n, 'created_human') }}</div>
                            </div>
                        </div>

                        @if ($n['isUnread'])
                            <div class="text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2 py-1 rounded-xl">
                                {{ __('dashboard.new') }}
                            </div>
                        @endif
                    </div>
                </button>
            @empty
                <div class="px-5 py-8 text-center text-sm text-slate-500">
                    {{ __('dashboard.no_notifications') }}
                </div>
            @endforelse
        </div>
    </div>
</div>
