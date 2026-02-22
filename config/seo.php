<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Central configuration for search engine optimization.
    |
    */

    'site_url' => env('APP_URL', 'https://servxmotors.com'),

    'site_name' => env('APP_NAME', 'SERV.X'),

    'default_title' => env('SEO_DEFAULT_TITLE', 'SERV.X — Corporate Fleet Maintenance Solutions'),

    'default_description' => env('SEO_DEFAULT_DESCRIPTION', 'SERV.X is an integrated platform for fleet management. Oil & filter services, maintenance tracking, and consolidated invoicing for corporate fleets.'),

    'default_image' => env('SEO_DEFAULT_IMAGE', null),

    'twitter_handle' => env('SEO_TWITTER_HANDLE', '@servxmotors'),

    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION', null),

    'bing_site_verification' => env('BING_SITE_VERIFICATION', null),

    /*
    |--------------------------------------------------------------------------
    | Noindex Routes
    |--------------------------------------------------------------------------
    |
    | Route patterns that should have noindex meta tag (admin, dashboard, auth).
    |
    */
    'noindex_patterns' => [
        'admin/*',
        'company/*',
        'tech/*',
        'driver/*',
        'dashboard*',
        'sign-in/*',
        'login',
        'register',
        'forgot-password',
        'reset-password',
        'verify-email',
        'confirm-password',
        'profile',
    ],

];
