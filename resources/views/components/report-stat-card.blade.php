@props([
    'label' => '',
    'value' => '',
    'icon' => 'fa-chart-line',
    'iconColor' => 'sky', // sky, amber, emerald, red
])

@php
    $iconBgClass = match($iconColor) {
        'sky' => 'bg-sky-500/30',
        'amber' => 'bg-amber-500/30',
        'emerald' => 'bg-emerald-500/30',
        'red' => 'bg-red-500/30',
        default => 'bg-slate-500/30',
    };
    $iconTextClass = match($iconColor) {
        'sky' => 'text-sky-600 dark:text-sky-400',
        'amber' => 'text-amber-600 dark:text-amber-400',
        'emerald' => 'text-emerald-600 dark:text-emerald-400',
        'red' => 'text-red-600 dark:text-red-400',
        default => 'text-slate-600 dark:text-slate-400',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-slate-500/30 p-4 sm:p-5 backdrop-blur-sm transition-colors duration-300']) }}>
    <div class="flex items-center gap-3 mb-2">
        <span class="w-10 h-10 rounded-xl flex items-center justify-center {{ $iconBgClass }}">
            <i class="fa-solid {{ $icon }} {{ $iconTextClass }}"></i>
        </span>
        <p class="text-slate-600 dark:text-slate-400 text-sm font-bold">{{ $label }}</p>
    </div>
    <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white {{ $iconColor !== 'sky' ? $iconTextClass : '' }}">
        {{ $value }}
    </p>
</div>
