@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-s-4 border-indigo-400 dark:border-sky-400 text-start text-base font-medium text-indigo-700 dark:text-sky-300 bg-indigo-50 dark:bg-sky-500/10 focus:outline-none focus:text-indigo-800 dark:focus:text-sky-200 focus:bg-indigo-100 dark:focus:bg-sky-500/20 focus:border-indigo-700 dark:focus:border-sky-500 transition-colors duration-300 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-s-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-slate-700/50 hover:border-gray-300 dark:hover:border-slate-500 focus:outline-none focus:text-gray-900 dark:focus:text-white focus:bg-gray-50 dark:focus:bg-slate-700/50 focus:border-gray-300 dark:focus:border-slate-500 transition-colors duration-300 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
