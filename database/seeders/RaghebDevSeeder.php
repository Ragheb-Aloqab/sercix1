<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RaghebDevSeeder extends Seeder
{
    public function run(): void
    {
        // $company = Company::updateOrCreate(
        //     ['phone' => '+966563223963'],
        //     [
        //         'company_name' => 'RaghebDev',
        //         'email' => 'ragheb@raghebdev.com',
        //         'password' => Hash::make('password'),
        //         'status' => 'active',
        //         'vehicle_quota' => 150,
        //         'city' => 'Riyadh',
        //     ]
        // );

        // $existingCount = $company->vehicles()->count();
        // $toCreate = 150 - $existingCount;

        // if ($toCreate <= 0) {
        //     $this->command->info("RaghebDev already has {$existingCount} vehicles.");
        //     return;
        // }

        // $makes = ['Toyota', 'Nissan', 'Honda', 'Hyundai', 'Kia', 'Ford', 'Chevrolet', 'GMC', 'Mitsubishi', 'Mazda'];
        // $models = [
        //     'Toyota' => ['Camry', 'Corolla', 'Land Cruiser', 'Hilux', 'Prado', 'Yaris', 'Avalon'],
        //     'Nissan' => ['Patrol', 'Altima', 'Sunny', 'X-Trail', 'Navara', 'Kicks', 'Maxima'],
        //     'Honda' => ['Accord', 'Civic', 'CR-V', 'Pilot', 'City', 'HR-V'],
        //     'Hyundai' => ['Sonata', 'Elantra', 'Santa Fe', 'Tucson', 'Accent', 'Kona'],
        //     'Kia' => ['Optima', 'Cerato', 'Sportage', 'Sorento', 'Picanto', 'Stonic'],
        //     'Ford' => ['Explorer', 'F-150', 'Ranger', 'Edge', 'Escape', 'Mustang'],
        //     'Chevrolet' => ['Tahoe', 'Silverado', 'Malibu', 'Traverse', 'Equinox', 'Camaro'],
        //     'GMC' => ['Yukon', 'Sierra', 'Acadia', 'Terrain', 'Canyon'],
        //     'Mitsubishi' => ['Pajero', 'Outlander', 'L200', 'ASX', 'Eclipse Cross'],
        //     'Mazda' => ['6', '3', 'CX-5', 'CX-9', '2', 'CX-3'],
        // ];
        // $types = ['sedan', 'suv', 'truck', 'van', 'pickup'];

        // for ($i = 0; $i < $toCreate; $i++) {
        //     $make = $makes[array_rand($makes)];
        //     $modelList = $models[$make] ?? ['Model'];
        //     $model = $modelList[array_rand($modelList)];

        //     Vehicle::create([
        //         'company_id' => $company->id,
        //         'type' => $types[array_rand($types)],
        //         'name' => "{$make} {$model} #" . ($existingCount + $i + 1),
        //         'make' => $make,
        //         'model' => $model,
        //         'year' => rand(2018, 2025),
        //         'plate_number' => 'RAG' . str_pad($existingCount + $i + 1, 4, '0', STR_PAD_LEFT),
        //         'color' => ['White', 'Black', 'Silver', 'Gray', 'Blue', 'Red'][array_rand(['White', 'Black', 'Silver', 'Gray', 'Blue', 'Red'])],
        //         'is_active' => true,
        //     ]);
        // }

        // $this->command->info("Created {$toCreate} vehicles for RaghebDev. Total: {$company->vehicles()->count()} vehicles.");

    }
}
