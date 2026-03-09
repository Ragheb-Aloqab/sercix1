<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'tag' => 'Entry level',
                'description' => 'Manual fuel and maintenance entry, basic reports, dashboard, driver accounts, request maintenance offers, limited vehicle management.',
                'price' => 9,
                'price_unit' => 'per_vehicle_month',
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    'fuel_manual',
                    'maintenance_manual',
                    'basic_reports',
                    'dashboard',
                    'driver_accounts',
                    'request_maintenance_offers',
                    'limited_vehicles',
                    'data_assistant_partial',
                ],
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'tag' => 'Most popular',
                'description' => 'All Basic features plus automatic fuel/maintenance invoice registration, vehicle cost reports, distance reports, tax reports, cost per km, enhanced driver accounts.',
                'price' => 13,
                'price_unit' => 'per_vehicle_month',
                'is_active' => true,
                'sort_order' => 2,
                'features' => [
                    'fuel_manual',
                    'maintenance_manual',
                    'basic_reports',
                    'dashboard',
                    'driver_accounts',
                    'request_maintenance_offers',
                    'limited_vehicles',
                    'auto_fuel_invoice',
                    'auto_maintenance_invoice',
                    'vehicle_cost_reports',
                    'distance_reports',
                    'tax_reports',
                    'cost_per_km',
                    'enhanced_driver_accounts',
                ],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'tag' => 'For large companies',
                'description' => 'All Standard features plus driver alerts, vehicle tracking via app, advanced reports, White Label, API integration.',
                'price' => 18,
                'price_unit' => 'per_vehicle_month',
                'is_active' => true,
                'sort_order' => 3,
                'features' => [
                    'fuel_manual',
                    'maintenance_manual',
                    'basic_reports',
                    'dashboard',
                    'driver_accounts',
                    'request_maintenance_offers',
                    'limited_vehicles',
                    'auto_fuel_invoice',
                    'auto_maintenance_invoice',
                    'vehicle_cost_reports',
                    'distance_reports',
                    'tax_reports',
                    'cost_per_km',
                    'enhanced_driver_accounts',
                    'driver_alerts',
                    'vehicle_tracking',
                    'advanced_reports',
                    'white_label',
                    'api_integration',
                ],
            ],
        ];

        foreach ($plans as $data) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
