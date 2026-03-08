@props([
    'pdfUrl',
    'excelUrl',
    'label' => null,
    'pdfQueueUrl' => null,
    'excelQueueUrl' => null,
])
@php
    $label = $label ?? __('reports.export');
@endphp
<div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false">
    <button type="button"
            @click="open = ! open"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:bg-slate-700/50 transition-colors">
        <i class="fa-solid fa-download"></i>
        {{ $label }}
        <i class="fa-solid fa-chevron-{{ app()->getLocale() === 'ar' ? 'up' : 'down' }} text-sm"></i>
    </button>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 mt-2 min-w-[200px] ltr:left-0 rtl:right-0 rounded-xl shadow-xl bg-white border border-slate-200 py-1 ring-1 ring-slate-200/80"
         style="display: none;">
        <a href="{{ $pdfUrl }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-100 hover:text-rose-600 transition-colors">
            <i class="fa-solid fa-file-pdf text-rose-500 w-5"></i>
            {{ __('reports.export_pdf') }}
        </a>
        <a href="{{ $excelUrl }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-100 hover:text-emerald-600 transition-colors">
            <i class="fa-solid fa-file-excel text-emerald-600 w-5"></i>
            {{ __('reports.export_excel') }}
        </a>
        @if($pdfQueueUrl || $excelQueueUrl)
            <div class="border-t border-slate-200 my-1"></div>
            @if($pdfQueueUrl)
                <a href="{{ $pdfQueueUrl }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors" title="{{ __('reports.queued_for_generation') }}">
                    <i class="fa-solid fa-clock w-5"></i>
                    {{ __('reports.generate_in_background') }}
                </a>
            @endif
            @if($excelQueueUrl)
                <a href="{{ $excelQueueUrl }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors" title="{{ __('reports.queued_for_generation') }}">
                    <i class="fa-solid fa-clock w-5"></i>
                    {{ __('reports.generate_excel_in_background') }}
                </a>
            @endif
        @endif
    </div>
</div>
