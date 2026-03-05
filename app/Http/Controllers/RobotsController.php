<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate robots.txt for search engines (SEO optimized).
     */
    public function __invoke(): Response
    {
        $baseUrl = rtrim(config('seo.site_url', config('app.url')), '/');
        $sitemapUrl = $baseUrl . '/sitemap.xml';
        $host = parse_url($baseUrl, PHP_URL_HOST);

        $content = "# Servx Motors - robots.txt\n";
        $content .= "# https://www.robotstxt.org/robotstxt.html\n\n";

        // All crawlers
        $content .= "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Allow: /set-locale\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /company/\n";
        $content .= "Disallow: /tech/\n";
        $content .= "Disallow: /driver/\n";
        $content .= "Disallow: /maintenance-center/\n";
        $content .= "Disallow: /dashboard\n";
        $content .= "Disallow: /dashboard/\n";
        $content .= "Disallow: /sign-in/\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Disallow: /forgot-password\n";
        $content .= "Disallow: /reset-password\n";
        $content .= "Disallow: /verify-email\n";
        $content .= "Disallow: /confirm-password\n";
        $content .= "Disallow: /profile\n";
        $content .= "Disallow: /payments/tap/webhook\n";
        $content .= "Disallow: /payments/tap/redirect\n\n";

        // Google-specific (optional - inherits * rules if omitted)
        $content .= "User-agent: Googlebot\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /company/\n";
        $content .= "Disallow: /tech/\n";
        $content .= "Disallow: /driver/\n";
        $content .= "Disallow: /maintenance-center/\n";
        $content .= "Disallow: /dashboard\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Disallow: /profile\n\n";

        $content .= "User-agent: Googlebot-Image\n";
        $content .= "Allow: /\n\n";

        if ($host) {
            $content .= "Host: {$host}\n\n";
        }
        $content .= "Sitemap: {$sitemapUrl}\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
