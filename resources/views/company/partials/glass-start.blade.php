@push('styles')
@include('company.partials.glass-styles')
@endpush

<div class="company-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="company-glass-content max-w-7xl mx-auto">
        <header class="mb-8 sm:mb-10">
            <div class="inline-block px-6 py-3 rounded-xl bg-slate-900/80 border border-sky-500/60">
                <h1 class="text-2xl sm:text-3xl font-black text-white" dir="rtl">
                    {{ $title ?? __('dashboard.subtitle_default') }}
                </h1>
            </div>
        </header>
