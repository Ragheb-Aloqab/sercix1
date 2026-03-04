@props([
    'label',
    'value',
    'subtext' => null,
    'variant' => 'amber', // amber, emerald, sky, slate
])

@php
    $variants = [
        'amber' => 'rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4',
        'emerald' => 'rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4',
        'sky' => 'rounded-2xl bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 p-4',
        'slate' => 'rounded-2xl bg-slate-50 dark:bg-slate-900/20 border border-slate-200 dark:border-slate-800 p-4',
    ];
    $textVariants = [
        'amber' => 'text-amber-700 dark:text-amber-400',
        'emerald' => 'text-emerald-700 dark:text-emerald-400',
        'sky' => 'text-sky-700 dark:text-sky-400',
        'slate' => 'text-slate-700 dark:text-slate-400',
    ];
    $subtextVariants = [
        'amber' => 'text-amber-600/80 dark:text-amber-400/80',
        'emerald' => 'text-emerald-600/80 dark:text-emerald-400/80',
        'sky' => 'text-sky-600/80 dark:text-sky-400/80',
        'slate' => 'text-slate-600/80 dark:text-slate-400/80',
    ];
    $baseClass = $variants[$variant] ?? $variants['amber'];
    $textClass = $textVariants[$variant] ?? $textVariants['amber'];
    $subtextClass = $subtextVariants[$variant] ?? $subtextVariants['amber'];
@endphp

<div {{ $attributes->merge(['class' => $baseClass]) }}>
    <p class="text-sm font-bold {{ $textClass }}">{{ $label }}</p>
    <p class="text-2xl font-black mt-1 {{ $textClass }}">{{ $value }}</p>
    @if ($subtext)
        <p class="text-xs mt-0.5 {{ $subtextClass }}">{{ $subtext }}</p>
    @endif
</div>
