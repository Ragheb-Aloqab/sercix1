@props(['title' => __('dashboard.subtitle_default')])

@push('styles')
@include('company.partials.glass-styles')
@endpush

<div class="company-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="company-glass-content max-w-7xl mx-auto">
        <header class="mb-8 sm:mb-10">
            <div class="inline-block px-6 py-3 rounded-xl bg-white dark:bg-servx-black-card/90 border border-slate-200 dark:border-servx-red/50 shadow-sm dark:shadow-none transition-colors duration-300">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white text-start">
                    {{ $title }}
                </h1>
            </div>
        </header>
        {{ $slot }}
    </div>
</div>
