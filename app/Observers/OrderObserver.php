<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\WebhookUrl;
use Illuminate\Support\Facades\Cache;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\InvoiceCreated;
use App\Notifications\DriverServiceRequestNotification;
use App\Services\InvoicePdfService;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    private function invalidateAdminStats(): void
    {
        Cache::put('admin_stats_version', (Cache::get('admin_stats_version', 1) + 1));
    }

    public function created(Order $order): void
    {
        if ($order->company_id) {
            Cache::forget("company_dashboard_{$order->company_id}");
            Cache::forget("market_comparison_{$order->company_id}_6");
            Cache::forget("market_comparison_{$order->company_id}_12");
        }
        $this->invalidateAdminStats();
        if ($order->status === 'pending_approval') {
            $company = $order->company;
            if ($company) {
                $company->notify(new DriverServiceRequestNotification($order));
            }
        } else {
            event(new OrderCreated($order));
            WebhookUrl::dispatch('order_created', [
                'order_id' => $order->id,
                'company_id' => $order->company_id,
                'status' => $order->status,
                'timestamp' => now()->toIso8601String(),
            ], $order->company_id);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->company_id) {
            Cache::forget("company_dashboard_{$order->company_id}");
            Cache::forget("market_comparison_{$order->company_id}_6");
            Cache::forget("market_comparison_{$order->company_id}_12");
        }
        $this->invalidateAdminStats();

        if ($order->wasChanged('status')) {
            event(new OrderStatusChanged($order, (string) $order->getOriginal('status'), $order->status));
        }

        if ($order->wasChanged('status') && $order->status === 'completed') {
            $this->createInvoiceForOrder($order);
        }
    }
    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        $this->invalidateAdminStats();
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }

    private function createInvoiceForOrder(Order $order): void
    {
        if ($order->invoice()->exists()) {
            return;
        }

        $order->load(['services']);
        $subtotal = (float) $order->total_amount;
        $tax = (float) ($order->tax_amount ?? 0);

        $invoice = $order->invoice()->create([
            'company_id' => $order->company_id,
            'invoice_number' => 'INV-' . $order->id . '-' . now()->format('Ymd'),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'paid_amount' => 0,
        ]);

        event(new InvoiceCreated($invoice));

        try {
            app(InvoicePdfService::class)->generate($invoice);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
