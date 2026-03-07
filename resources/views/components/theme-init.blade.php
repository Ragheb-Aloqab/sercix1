{{-- Server values via meta tags (no inline script - avoids ModSecurity/CSP block on Hostinger) --}}
<meta name="sercix-initial-theme" content="{{ $initialTheme ?? '' }}">
<meta name="sercix-initial-preference" content="{{ $initialPreference ?? 'system' }}">
@if(!empty($forceLightTheme))
<meta name="sercix-force-theme" content="light">
@endif
<script src="{{ asset('js/theme-init.js') }}"></script>
