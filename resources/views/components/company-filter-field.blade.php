@props([
    'label',
    'name',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'options' => [],
])

<div>
    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ $label }}</label>
    @if ($type === 'select')
        <select name="{{ $name }}" {{ $attributes->merge(['class' => 'w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-500/50 bg-white dark:bg-slate-800/40 text-slate-900 dark:text-white min-h-[44px] transition-colors duration-300']) }}>
            {{ $slot }}
        </select>
    @else
        <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => 'w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-500/50 bg-white dark:bg-slate-800/40 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 min-h-[44px] transition-colors duration-300']) }}>
    @endif
</div>
