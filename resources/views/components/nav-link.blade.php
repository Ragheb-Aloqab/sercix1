@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-sky-400 dark:border-sky-400 text-sm font-medium leading-5 text-slate-900 dark:text-white focus:outline-none focus:border-sky-600 dark:focus:border-sky-500 transition-colors duration-300 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-500 focus:outline-none focus:text-slate-900 dark:focus:text-white focus:border-slate-300 dark:focus:border-slate-500 transition-colors duration-300 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
