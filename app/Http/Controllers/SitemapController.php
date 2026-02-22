<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class SitemapController extends Controller
{
    /**
     * Generate dynamic sitemap.xml for search engines.
     */
    public function __invoke(): Response
    {
        $baseUrl = rtrim(config('seo.site_url', config('app.url')), '/');
        $urls = [];

        // Homepage - highest priority
        $urls[] = [
            'loc' => $baseUrl . '/',
            'lastmod' => now()->toW3cString(),
            'changefreq' => 'weekly',
            'priority' => '1.0',
        ];

        // Note: Login/Register pages are noindex and excluded from sitemap per SEO best practices.

        $xml = $this->buildXml($urls);

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function buildXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $entry) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($entry['loc']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . ($entry['lastmod'] ?? now()->toW3cString()) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . ($entry['changefreq'] ?? 'weekly') . '</changefreq>' . "\n";
            $xml .= '    <priority>' . ($entry['priority'] ?? '0.5') . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
