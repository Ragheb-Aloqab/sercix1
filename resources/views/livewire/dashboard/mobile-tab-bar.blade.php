<div>
@if ($hasNav)
{{-- Mobile bottom tab bar - visible only on lg breakpoint and below --}}
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-t border-slate-200 dark:border-slate-800"
     style="padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px);">
    <div class="flex items-center justify-around h-[72px] min-h-touch px-2">
        @foreach ($visibleTabs as $item)
            <a href="{{ $item['href'] }}"
               class="flex flex-col items-center justify-center flex-1 min-w-0 min-h-[44px] py-2 px-1 rounded-xl transition-colors duration-200 active:scale-[0.98]
                      {{ $item['active'] ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fa-solid {{ $item['icon'] }} text-lg mb-0.5"></i>
                <span class="text-[10px] sm:text-xs font-semibold truncate max-w-full">{{ $item['label'] }}</span>
            </a>
        @endforeach

        @if ($hasMore)
            <button type="button"
                    wire:click="toggleMoreModal"
                    class="flex flex-col items-center justify-center flex-1 min-w-0 min-h-[44px] py-2 px-1 rounded-xl transition-colors duration-200 active:scale-[0.98]
                           text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800">
                <i class="fa-solid fa-ellipsis text-lg mb-0.5"></i>
                <span class="text-[10px] sm:text-xs font-semibold truncate max-w-full">{{ __('dashboard.more') }}</span>
            </button>
        @endif
    </div>
</nav>

{{-- More modal --}}
@if ($hasMore)
    <div x-data="{ open: @entangle('showMoreModal') }"
         x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="lg:hidden fixed inset-0 z-[60] flex items-end justify-center"
         @click.self="$wire.closeMoreModal()">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40" @click="$wire.closeMoreModal()"></div>

        {{-- Modal content --}}
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-t-3xl shadow-2xl max-h-[70vh] overflow-hidden
                    {{ app()->getLocale() === 'ar' ? 'rounded-tl-3xl rounded-tr-3xl' : 'rounded-tl-3xl rounded-tr-3xl' }}"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-lg font-bold">{{ __('dashboard.more') }}</h3>
                <button type="button" wire:click="closeMoreModal" class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 active:scale-95">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="overflow-y-auto max-h-[60vh] py-2">
                @foreach ($moreItems as $item)
                    <a href="{{ $item['href'] }}"
                       wire:click="closeMoreModal"
                       class="flex items-center gap-4 px-4 py-3 min-h-[48px] mx-2 rounded-xl transition-colors active:scale-[0.99]
                              {{ $item['active'] ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center
                                    {{ $item['active'] ? 'bg-emerald-100 dark:bg-emerald-900/40' : 'bg-slate-100 dark:bg-slate-800' }}">
                            <i class="fa-solid {{ $item['icon'] }}"></i>
                        </span>
                        <span class="font-semibold">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif
@endif
</div>
