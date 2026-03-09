<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payments System
    |--------------------------------------------------------------------------
    |
    | When false, all payment features are hidden and deactivated.
    | Can be reactivated in future development phase.
    |
    */
    'payments_enabled' => env('PAYMENTS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard Alerts
    |--------------------------------------------------------------------------
    */
    'stuck_order_days' => env('STUCK_ORDER_DAYS', 7),
    'inactive_company_days' => env('INACTIVE_COMPANY_DAYS', 90),
    'low_fleet_utilization_threshold' => env('LOW_FLEET_UTILIZATION_THRESHOLD', 30),

    /*
    |--------------------------------------------------------------------------
    | Vehicle Quota
    |--------------------------------------------------------------------------
    | Default max vehicles per company when creating new company. Null = unlimited.
    */
    'default_vehicle_quota' => env('DEFAULT_VEHICLE_QUOTA', 10),

    /*
    |--------------------------------------------------------------------------
    | Default Subscription Plan
    |--------------------------------------------------------------------------
    | Plan ID assigned to new companies. Null = no plan (allow all features for backward compat).
    */
    'default_plan_id' => env('DEFAULT_PLAN_ID'),

    /*
    |--------------------------------------------------------------------------
    | Map Style
    |--------------------------------------------------------------------------
    | Default map tile style: carto_dark, osm_humanitarian, stadia_alidade, esri_imagery.
    | Null = auto (dark mode -> carto_dark, light mode -> osm_humanitarian).
    */
    'default_map_style' => env('DEFAULT_MAP_STYLE'),

    /*
    |--------------------------------------------------------------------------
    | Market Comparison – Benchmark Rates
    |--------------------------------------------------------------------------
    | market_avg_per_km: Fallback total cost per km (SAR/km) when no quotation data.
    | market_fuel_per_km: Fuel cost per km (SAR/km) for market fuel estimate.
    | Main comparison uses: quotation-based maintenance + (km × market_fuel_per_km).
    */
    'market_avg_per_km' => (float) (env('MARKET_AVG_PER_KM', 0.37)),
    'market_fuel_per_km' => (float) (env('MARKET_FUEL_PER_KM', 0.15)),

    /*
    |--------------------------------------------------------------------------
    | Maintenance Invoice Upload
    |--------------------------------------------------------------------------
    | Max file size in MB for invoice uploads (images: JPG, JPEG, PNG, WEBP; PDF).
    */
    'invoice_max_size_mb' => (int) (env('INVOICE_MAX_SIZE_MB', 5)),

    /*
    |--------------------------------------------------------------------------
    | White-Label Subdomain
    |--------------------------------------------------------------------------
    | Base domain for company subdomains (e.g. servx.sa).
    | Companies with white_label_enabled can access their dashboard at:
    | {subdomain}.{white_label_domain}
    */
    'white_label_domain' => env('WHITE_LABEL_DOMAIN', 'servxmotors.com'),

    'subscription_plans' => [
        'features' => [
            'fuel_manual' => 'Manual fuel entry',
            'maintenance_manual' => 'Manual maintenance entry',
            'basic_reports' => 'Basic reports',
            'dashboard' => 'Dashboard access',
            'driver_accounts' => 'Driver accounts',
            'request_maintenance_offers' => 'Request maintenance offers from service centers',
            'limited_vehicles' => 'Limited vehicle management',
            'data_assistant_partial' => 'Data assistant (partial)',
            'auto_fuel_invoice' => 'Automatic fuel invoice registration via fuel providers',
            'auto_maintenance_invoice' => 'Automatic maintenance invoice registration via service centers',
            'vehicle_cost_reports' => 'Vehicle cost reports',
            'distance_reports' => 'Distance reports',
            'tax_reports' => 'Tax reports',
            'cost_per_km' => 'Cost per kilometer analysis',
            'enhanced_driver_accounts' => 'Enhanced driver accounts',
            'driver_alerts' => 'Driver alerts',
            'vehicle_tracking' => 'Vehicle tracking via application',
            'advanced_reports' => 'Advanced and comprehensive reports',
            'white_label' => 'White Label support for large companies',
            'api_integration' => 'API integration with external systems',
        ],
    ],

];
