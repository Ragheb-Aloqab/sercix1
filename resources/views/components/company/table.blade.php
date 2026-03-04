@props([
    'class' => '',
])

<div {{ $attributes->merge(['class' => "rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-300 dark:hover:border-slate-400/50 transition-all duration-300 overflow-hidden {$class}"]) }}>
    @if(isset($header))
        <div class="mb-6 flex items-center justify-between">
            {{ $header }}
        </div>
    @endif
    {{ $slot }}
</div>
