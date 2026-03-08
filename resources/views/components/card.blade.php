@props([
    'padding' => true,
    'class' => '',
])

@php
    $baseClass = 'rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-slate-500/30 shadow-sm backdrop-blur-sm transition-colors duration-300';
    $paddingClass = $padding ? 'p-4 sm:p-5' : '';
@endphp

<div {{ $attributes->merge(['class' => trim("$baseClass $paddingClass $class")]) }}>
    {{ $slot }}
</div>
