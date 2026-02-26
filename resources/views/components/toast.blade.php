@props(['type' => 'success', 'message' => ''])
@php
    $config = match($type) {
        'success' => ['icon' => 'fa-circle-check', 'border' => 'border-emerald-500/40', 'bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'duration' => 4000],
        'error' => ['icon' => 'fa-circle-exclamation', 'border' => 'border-rose-500/40', 'bg' => 'bg-rose-500/10', 'text' => 'text-rose-400', 'duration' => 5000],
        'info' => ['icon' => 'fa-circle-info', 'border' => 'border-sky-500/40', 'bg' => 'bg-sky-500/10', 'text' => 'text-sky-400', 'duration' => 4000],
        default => ['icon' => 'fa-circle-check', 'border' => 'border-emerald-500/40', 'bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'duration' => 4000],
    };
@endphp
<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, {{ $config['duration'] }})"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed bottom-4 end-4 z-[100] max-w-sm rounded-2xl border {{ $config['border'] }} {{ $config['bg'] }} px-4 py-3 text-sm {{ $config['text'] }} font-medium shadow-lg backdrop-blur-sm"
     role="alert">
    <div class="flex items-center gap-2">
        <i class="fa-solid {{ $config['icon'] }} shrink-0"></i>
        <span>{{ $message ?: ($type === 'success' ? session('success') : ($type === 'error' ? session('error') : session('info'))) }}</span>
    </div>
</div>
