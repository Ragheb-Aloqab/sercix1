{{--
    SEO Meta Tags Component
    Usage: @include('components.seo-meta', [
        'title' => 'Page Title',
        'description' => 'Meta description',
        'image' => 'https://...',
        'canonical' => 'https://...',
        'noindex' => false,
        'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => 'Page', 'url' => '/page']],
    ])
--}}
@php
    $siteUrl = rtrim(config('seo.site_url', config('app.url')), '/');
    $siteName = config('seo.site_name', $siteName ?? config('app.name', 'SERV.X'));
    $title = $title ?? ($pageTitle ?? config('seo.default_title'));
    $description = $description ?? ($pageDescription ?? config('seo.default_description'));
    $image = $image ?? config('seo.default_image') ?? ($siteLogoUrl ?? null);
    if ($image && !str_starts_with($image, 'http')) {
        $image = $siteUrl . '/' . ltrim($image, '/');
    }
    $image = $image ?: $siteUrl . '/images/og-default.png';
    $canonical = $canonical ?? url()->current();
    $canonical = str_starts_with($canonical, 'http') ? $canonical : $siteUrl . parse_url($canonical, PHP_URL_PATH);
    $noindex = $noindex ?? false;
    $breadcrumbs = $breadcrumbs ?? [];
@endphp
{{-- Primary Meta --}}
<title>{{ $title }}</title>
<meta name="description" content="{{ Str::limit(strip_tags($description), 160) }}">
@if($keywords ?? null)
<meta name="keywords" content="{{ is_array($keywords) ? implode(', ', $keywords) : $keywords }}">
@endif
@if($noindex)
<meta name="robots" content="noindex, nofollow">
@endif
<link rel="canonical" href="{{ $canonical }}">
{{-- Google / Bing Verification --}}
@if(config('seo.google_site_verification'))
<meta name="google-site-verification" content="{{ config('seo.google_site_verification') }}">
@endif
@if(config('seo.bing_site_verification'))
<meta name="msvalidate.01" content="{{ config('seo.bing_site_verification') }}">
@endif
{{-- Open Graph --}}
<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($description), 200) }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($description), 200) }}">
<meta name="twitter:image" content="{{ $image }}">
@if(config('seo.twitter_handle'))
<meta name="twitter:site" content="{{ config('seo.twitter_handle') }}">
@endif
