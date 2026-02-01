<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/InvoiceFactory.php
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 800);
        $tax = round($subtotal * 0.15, 2);
        $total = $subtotal + $tax;

        return [
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => 'paid',      // إذا عليه CHECK وطلع خطأ، نخليه من القيم المسموحة
            'pdf_path' => null,
            // order_id + company_id نمررهم من Seeder
        ];
    }
}
