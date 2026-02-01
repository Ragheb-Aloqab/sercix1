<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryTransactionFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement([
            'in',
            'out',
            'adjustment',
            'return',
            'transfer'
        ]);

        // توليد كمية منطقية حسب نوع الحركة
        $quantityChange = match ($type) {
            'in', 'return'     => $this->faker->numberBetween(1, 20),
            'out'              => -$this->faker->numberBetween(1, 10),
            'adjustment'       => $this->faker->numberBetween(-5, 5),
            'transfer'         => $this->faker->numberBetween(-10, 10),
        };

        $baseQuantity = $this->faker->numberBetween(5, 50);
        $newQuantity  = max(0, $baseQuantity + $quantityChange);

        $hasOrder = $this->faker->boolean(40); // 40% احتمال وجود طلب

        return [
            'inventory_item_id' => InventoryItem::query()->inRandomOrder()->value('id')
                                    ?? InventoryItem::factory(),

            'transaction_type'  => $type,

            'quantity_change'   => $quantityChange,

            'new_quantity'      => $newQuantity,

            'unit_price'        => $this->faker->randomFloat(2, 5, 500),

            'related_order_type'=> $hasOrder
                                    ? $this->faker->randomElement([
                                        'service_order',
                                        'purchase_order',
                                        'maintenance_order'
                                      ])
                                    : null,

            'reference_number'  => strtoupper($this->faker->bothify('REF-####')),

            'notes'             => $this->faker->sentence(),

            'related_order_id'  => $hasOrder
                                    ? (Order::query()->inRandomOrder()->value('id')
                                        ?? Order::factory())
                                    : null,

            'created_by'        => User::query()->inRandomOrder()->value('id')
                                    ?? User::factory(),
        ];
    }
}