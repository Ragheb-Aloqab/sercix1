<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate robots.txt for search engines.
     */
    public function __invoke(): Response
    {
        $baseUrl = rtrim(config('seo.site_url', config('app.url')), '/');
        $sitemapUrl = $baseUrl . '/sitemap.xml';

        $content = "User-agent: *\n";
        $content .= "Allow: /\n\n";
        $content .= "# Disallow admin, dashboard, auth, and private areas\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /company/\n";
        $content .= "Disallow: /tech/\n";
        $content .= "Disallow: /driver/\n";
        $content .= "Disallow: /dashboard\n";
        $content .= "Disallow: /sign-in/\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Disallow: /forgot-password\n";
        $content .= "Disallow: /reset-password\n";
        $content .= "Disallow: /verify-email\n";
        $content .= "Disallow: /confirm-password\n";
        $content .= "Disallow: /profile\n";
        $content .= "Disallow: /payments/tap/webhook\n\n";
        $content .= "Sitemap: {$sitemapUrl}\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
