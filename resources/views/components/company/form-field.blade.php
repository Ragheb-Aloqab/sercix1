@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'id' => null,
    'value' => null,
    'required' => false,
    'placeholder' => '',
])

@php
    $id = $id ?? $name;
    $inputClass = 'mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500 transition-colors duration-300';
    $inputValue = old($name, $value);
@endphp

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    @if($label !== '')
        <label for="{{ $id }}" class="text-sm font-bold text-slate-400">{{ $label }}@if($required) * @endif</label>
    @endif
    @if($type === 'textarea')
        <textarea name="{{ $name }}" id="{{ $id }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}
            {{ $attributes->except('class', 'value')->merge(['class' => $inputClass, 'rows' => 3]) }}>{{ $slot->isNotEmpty() ? $slot : $inputValue }}</textarea>
    @elseif($type === 'select')
        <select name="{{ $name }}" id="{{ $id }}" {{ $required ? 'required' : '' }}
            {{ $attributes->except('class', 'value')->merge(['class' => $inputClass]) }}>
            {{ $slot }}
        </select>
    @else
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ $inputValue }}" placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->except('class', 'value')->merge(['class' => $inputClass]) }}>
    @endif
    @error($name)
        <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
    @enderror
</div>
