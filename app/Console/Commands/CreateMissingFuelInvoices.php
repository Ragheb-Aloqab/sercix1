<?php

namespace App\Console\Commands;

use App\Models\FuelRefill;
use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Console\Command;

class CreateMissingFuelInvoices extends Command
{
    protected $signature = 'invoices:create-missing-fuel';

    protected $description = 'Create invoices for fuel refills that have receipts but no invoice';

    public function handle(): int
    {
        $refills = FuelRefill::query()
            ->whereNotNull('receipt_path')
            ->whereDoesntHave('invoice')
            ->get();

        if ($refills->isEmpty()) {
            $this->info('No fuel refills need invoices.');
            return 0;
        }

        $created = 0;
        foreach ($refills as $fuelRefill) {
            $invoice = Invoice::create([
                'company_id' => $fuelRefill->company_id,
                'fuel_refill_id' => $fuelRefill->id,
                'invoice_type' => Invoice::TYPE_FUEL,
                'invoice_number' => 'INV-F-' . $fuelRefill->id . '-' . now()->format('Ymd'),
                'subtotal' => (float) $fuelRefill->cost,
                'tax' => 0,
                'paid_amount' => 0,
            ]);

            try {
                app(InvoicePdfService::class)->generate($invoice);
                $created++;
                $this->line("Created invoice #{$invoice->id} for fuel refill #{$fuelRefill->id}");
            } catch (\Throwable $e) {
                $this->error("Failed for refill #{$fuelRefill->id}: " . $e->getMessage());
                report($e);
            }
        }

        $this->info("Created {$created} fuel invoice(s).");
        return 0;
    }
}
