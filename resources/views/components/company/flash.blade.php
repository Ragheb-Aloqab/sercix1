@php
    $flash = session('flash');
    if (is_array($flash) && isset($flash['type'], $flash['message'])) {
        $type = $flash['type'];
        $message = $flash['message'];
    } else {
        $flash = null;
    }
    $legacySuccess = session('success') ?? session('fuel_invoice_success');
    $legacyError = session('error');
    $legacyInfo = session('info');
@endphp

@if($flash)
    <x-company-alert :type="$type" :dismissible="($type ?? '') === 'success'">
        {{ $message }}
    </x-company-alert>
@elseif($legacySuccess)
    <x-company-alert type="success" :dismissible="true">{{ $legacySuccess }}</x-company-alert>
@elseif($legacyError)
    <x-company-alert type="error">{{ $legacyError }}</x-company-alert>
@elseif($legacyInfo)
    <x-company-alert type="info">{{ $legacyInfo }}</x-company-alert>
@endif
