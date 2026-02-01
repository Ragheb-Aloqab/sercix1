<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'زيت محرك 5W-30',
            'زيت محرك 10W-40',
            'فلتر زيت',
            'فلتر هواء',
            'فلتر مكيف',
            'زيت قير',
            'زيت فرامل DOT4',
            'بواجي',
            'بطارية',
            'كفر',
        ]);

        $category = match (true) {
            str_contains($name, 'زيت') => 'زيوت',
            str_contains($name, 'فلتر') => 'فلاتر',
            str_contains($name, 'بواجي') => 'قطع',
            str_contains($name, 'بطارية') => 'قطع',
            str_contains($name, 'كفر') => 'إطارات',
            default => 'أخرى',
        };

        return [
            'name' => $name,
            'sku' => 'SKU-' . Str::upper(Str::random(8)),
            'category' => $category,
            'unit_price' => $this->faker->randomFloat(2, 10, 500),
            'qty' => $this->faker->numberBetween(0, 200),
            'min_qty' => $this->faker->numberBetween(0, 30),
            'is_active' => true,
            //'service_id' => Service::factory(),
            'service_id' => Service::query()->inRandomOrder()->value('id')
    ?? Service::factory(),
            ];
    }

    // حالات جاهزة (اختياري)
    public function oil(): static
    {
        return $this->state(fn() => ['category' => 'زيوت']);
    }

    public function filter(): static
    {
        return $this->state(fn() => ['category' => 'فلاتر']);
    }
}
