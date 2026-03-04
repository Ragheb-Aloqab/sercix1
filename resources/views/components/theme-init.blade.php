{{-- Server values via meta tags (no inline script - avoids ModSecurity/CSP block on Hostinger) --}}
<meta name="sercix-initial-theme" content="{{ $initialTheme ?? '' }}">
<meta name="sercix-initial-preference" content="{{ $initialPreference ?? 'system' }}">
<script src="{{ asset('js/theme-init.js') }}"></script>
