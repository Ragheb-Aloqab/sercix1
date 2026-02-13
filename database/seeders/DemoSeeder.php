<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Company;
use App\Models\Service;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Rating;
use App\Models\TechnicianLocation;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        /*Start Activity*/
        $this->call(ActivitySeeder::class);
        /*End Activity*/
        // 1) Admin
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@servx.test',
            'password' => bcrypt('password'),
        ]);

        // 2) Technicians
        $techs = User::factory()->count(8)->technician()->create();

        // 3) Services (عامّة) - بدون تكرار
        $servicesData = [
            ['name' => 'تغيير زيت',        'description' => 'خدمة تغيير زيت المحرك', 'base_price' => 120, 'estimated_minutes' => 30],
            ['name' => 'تغيير فلتر زيت',    'description' => 'استبدال فلتر الزيت',    'base_price' => 60,  'estimated_minutes' => 15],
            ['name' => 'تغيير فلتر هواء',   'description' => 'استبدال فلتر الهواء',   'base_price' => 80,  'estimated_minutes' => 20],
            ['name' => 'تغيير بواجي',       'description' => 'استبدال شمعات الاحتراق', 'base_price' => 150, 'estimated_minutes' => 25],
            ['name' => 'فحص عام',           'description' => 'فحص سريع للمركبة',      'base_price' => 50,  'estimated_minutes' => 20],
            ['name' => 'تبديل بطارية',      'description' => 'تبديل بطارية السيارة',  'base_price' => 220, 'estimated_minutes' => 20],
            ['name' => 'تغيير كفرات',       'description' => 'تغيير الإطارات',        'base_price' => 350, 'estimated_minutes' => 40],
            ['name' => 'تغيير زيت قير',     'description' => 'تغيير زيت ناقل الحركة', 'base_price' => 450, 'estimated_minutes' => 60],
            ['name' => 'ميزان وترصيص',      'description' => 'ميزان وترصيص الكفرات',  'base_price' => 120, 'estimated_minutes' => 30],
            ['name' => 'تنظيف ثلاجة مكيف',  'description' => 'تنظيف دورة التكييف',     'base_price' => 180, 'estimated_minutes' => 35],
        ];

        foreach ($servicesData as $s) {
            Service::updateOrCreate(
                ['name' => $s['name']],
                [
                    'description' => $s['description'],
                    'is_active' => true,
                    'base_price' => $s['base_price'],
                    'estimated_minutes' => $s['estimated_minutes'],
                    'duration_minutes' => $s['estimated_minutes'],
                ]
            );
        }

        $services = Service::query()->get();

        /**
         * ✅ 3.5) Inventory items (مخزون) - لازم يكون هنا قبل الطلبات
         */
        $inventorySeed = [
            ['name' => 'زيت محرك 5W-30',      'sku' => 'OIL-5W30',     'category' => 'زيوت',   'unit_price' => 85,  'qty' => 200, 'min_qty' => 20],
            ['name' => 'زيت محرك 10W-40',     'sku' => 'OIL-10W40',    'category' => 'زيوت',   'unit_price' => 75,  'qty' => 180, 'min_qty' => 20],
            ['name' => 'فلتر زيت (عام)',      'sku' => 'FLT-OIL-01',   'category' => 'فلاتر',  'unit_price' => 25,  'qty' => 150, 'min_qty' => 15],
            ['name' => 'فلتر هواء (عام)',     'sku' => 'FLT-AIR-01',   'category' => 'فلاتر',  'unit_price' => 40,  'qty' => 90,  'min_qty' => 10],
            ['name' => 'بواجي (طقم)',         'sku' => 'SPARK-SET',    'category' => 'قطع',    'unit_price' => 150, 'qty' => 60,  'min_qty' => 8],
            ['name' => 'بطارية 70Ah',         'sku' => 'BAT-70',       'category' => 'قطع',    'unit_price' => 220, 'qty' => 40,  'min_qty' => 5],
            ['name' => 'زيت قير (ATF)',       'sku' => 'ATF-OIL',      'category' => 'زيوت',   'unit_price' => 120, 'qty' => 70,  'min_qty' => 8],
        ];

        /*foreach ($inventorySeed as $it) {
            InventoryItem::updateOrCreate(
                ['sku' => $it['sku']],
                array_merge($it, ['is_active' => true])
            );
        }*/
        foreach ($inventorySeed as $index => $it) {

    $serviceId = $services[$index % $services->count()]->id;

    InventoryItem::updateOrCreate(
        ['sku' => $it['sku']],
        array_merge($it, [
            'is_active' => true,
            'service_id' => $serviceId,
        ])
    );
}

        // (اختياري) زيادة عناصر عشوائية
        InventoryItem::factory()->count(20)->create();

        /**
         * ✅ دالة خصم مخزون (تضمن عدم النزول تحت 0)
         */
        $consume = function (string $sku, int $qty = 1) {
            InventoryItem::query()
                ->where('sku', $sku)
                ->update([
                    'qty' => DB::raw("CASE WHEN qty - {$qty} < 0 THEN 0 ELSE qty - {$qty} END")
                ]);
        };

        // 4) Companies + branches + vehicles + company_services
        $companies = Company::factory()
            ->count(12)
            ->create()
            ->each(function (Company $company) use ($services) {

                $branches = \App\Models\CompanyBranch::factory()
                    ->count(2)
                    ->create(['company_id' => $company->id]);

                $branches->first()?->update(['is_default' => true]);

                \App\Models\Vehicle::factory()
                    ->count(rand(3, 10))
                    ->create(['company_id' => $company->id]);

                $attachServices = $services->random(rand(4, 8));

                foreach ($attachServices as $srv) {
                    $company->services()->syncWithoutDetaching([
                        $srv->id => [
                            'base_price' => $srv->base_price,
                            'estimated_minutes' => $srv->estimated_minutes ?? $srv->duration_minutes ?? 30,
                            'is_enabled' => true,
                        ],
                    ]);
                }
            });

        // 5) Orders + order_services + payments + invoices + ratings
        foreach ($companies as $company) {
            $vehicle = $company->vehicles()->inRandomOrder()->first();
            $ordersCount = rand(4, 12);

            for ($i = 0; $i < $ordersCount; $i++) {
                $technician = $techs->random();

                $order = Order::factory()->create([
                    'company_id' => $company->id,
                    'vehicle_id' => $vehicle?->id,
                    'technician_id' => $technician->id,
                ]);

                $companyServices = $company->services()->inRandomOrder()->take(rand(1, 3))->get();
                $subtotal = 0;

                foreach ($companyServices as $srv) {
                    $unit = $srv->pivot->base_price ?? $srv->base_price ?? 0;
                    $qty  = 1;
                    $line = $unit * $qty;

                    $order->services()->syncWithoutDetaching([
                        $srv->id => [
                            'qty' => $qty,
                            'unit_price' => $unit,
                            'total_price' => $line,
                        ],
                    ]);

                    // ✅ خصم المخزون حسب الخدمة
                    switch ($srv->name) {
                        case 'تغيير زيت':
                            $consume('OIL-5W30', 1);
                            break;

                        case 'تغيير فلتر زيت':
                            $consume('FLT-OIL-01', 1);
                            break;

                        case 'تغيير فلتر هواء':
                            $consume('FLT-AIR-01', 1);
                            break;

                        case 'تغيير بواجي':
                            $consume('SPARK-SET', 1);
                            break;

                        case 'تبديل بطارية':
                            $consume('BAT-70', 1);
                            break;

                        case 'تغيير زيت قير':
                            $consume('ATF-OIL', 1);
                            break;
                    }

                    $subtotal += $line;
                }

                // Payment
                Payment::factory()->create([
                    'order_id' => $order->id,
                    'amount' => $subtotal,
                    'paid_at' => now(),
                ]);

                $tax   = round($subtotal * 0.15, 2);
                  $total = $subtotal + $tax;
                  
                  $paidAmount = rand(0, 1)
                      ? $total                      // مدفوعة
                      : round($total * 0.4, 2);     // مدفوعة جزئياً
                  
                  Invoice::updateOrCreate(
                      ['order_id' => $order->id],   // شرط فريد
                      [
                          'company_id' => $company->id,
                          'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                          'subtotal' => $subtotal,
                          'tax' => $tax,
                          'paid_amount' => $paidAmount,
                      ]
                  );
                  
                // Rating (للطلبات المكتملة فقط)
                if ($order->status === 'completed') {
                    Rating::factory()->create([
                        'order_id' => $order->id,
                        'technician_id' => $technician->id,
                        'company_id' => $company->id,
                    ]);
                }
            }
        }
        InventoryTransaction::factory()->count(10)->create();
        // 9) Technician live locations
        foreach ($techs as $tech) {
            TechnicianLocation::factory()->create([
                'technician_id' => $tech->id,
            ]);
        }
    }
}
