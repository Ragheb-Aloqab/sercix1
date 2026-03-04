@props([
    'action' => request()->url(),
    'method' => 'GET',
    'gridCols' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-6',
])

<form method="{{ $method }}" action="{{ $action }}" {{ $attributes->merge(['class' => 'rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4']) }}>
    <div class="grid {{ $gridCols }} gap-3 items-end">
        {{ $slot }}
    </div>
</form>
