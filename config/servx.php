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

];
