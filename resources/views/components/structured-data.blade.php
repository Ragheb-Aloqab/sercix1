{{--
    JSON-LD Structured Data Component
    Usage: @include('components.structured-data', [
        'type' => 'organization|website|breadcrumb|article',
        'data' => [...],
    ])
--}}
@php
    $siteUrl = rtrim(config('seo.site_url', config('app.url')), '/');
    $siteName = config('seo.site_name', $siteName ?? config('app.name', 'SERV.X'));
@endphp
@if($type === 'organization' || $type === 'all')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "{{ $siteName }}",
    "url": "{{ $siteUrl }}",
    "logo": "{{ $siteLogoUrl ?? $siteUrl . '/images/logo.png' }}",
    "description": "{{ config('seo.default_description') }}"
}
</script>
@endif
@if($type === 'website' || $type === 'all')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "{{ $siteName }}",
    "url": "{{ $siteUrl }}",
    "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ $siteUrl }}/?s={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>
@endif
@if(($type === 'breadcrumb' || $type === 'all') && !empty($breadcrumbs))
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        @foreach($breadcrumbs as $i => $item)
        {
            "@type": "ListItem",
            "position": {{ $i + 1 }},
            "name": "{{ $item['name'] ?? $item['title'] ?? 'Item' }}",
            "item": "{{ str_starts_with($item['url'] ?? '', 'http') ? $item['url'] : $siteUrl . ($item['url'] ?? '/') }}"
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endif
@if($type === 'article' && !empty($article))
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $article['title'] ?? $article['headline'] ?? '' }}",
    "description": "{{ $article['description'] ?? '' }}",
    "datePublished": "{{ $article['datePublished'] ?? now()->toIso8601String() }}",
    "dateModified": "{{ $article['dateModified'] ?? ($article['datePublished'] ?? now()->toIso8601String()) }}",
    "author": {
        "@type": "Organization",
        "name": "{{ $siteName }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "{{ $siteName }}",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ $article['image'] ?? $siteUrl . '/images/logo.png' }}"
        }
    }
}
</script>
@endif
