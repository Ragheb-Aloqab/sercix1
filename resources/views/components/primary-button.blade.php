<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center w-full sm:w-auto px-4 py-2.5 bg-sky-600 dark:bg-slate-700 text-white text-sm font-medium rounded-lg hover:bg-sky-500 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:focus:ring-slate-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-colors duration-300']) }}>
    {{ $slot }}
</button>
