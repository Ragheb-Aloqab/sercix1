@props([
    'type' => 'success', // success, error, warning, info
    'dismissible' => false,
])

@php
    $styles = [
        'success' => 'bg-emerald-500/20 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border-emerald-400/50 dark:border-emerald-400/50',
        'error' => 'bg-red-500/20 dark:bg-red-500/20 text-red-700 dark:text-red-300 border-red-400/50 dark:border-red-400/50',
        'warning' => 'bg-amber-500/20 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border-amber-400/50 dark:border-amber-400/50',
        'info' => 'bg-sky-500/20 dark:bg-sky-500/20 text-sky-700 dark:text-sky-300 border-sky-400/50 dark:border-sky-400/50',
    ];
    $class = $styles[$type] ?? $styles['success'];
@endphp

<div {{ $attributes->merge(['class' => "p-4 rounded-2xl border mb-6 {$class}"]) }}
     @if ($dismissible)
         x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
     @endif>
    {{ $slot }}
</div>
