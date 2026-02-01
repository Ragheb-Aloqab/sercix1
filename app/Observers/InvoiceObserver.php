<?php

namespace App\Observers;
use App\Models\Invoice;
class InvoiceObserver
{
  
  /*
  قبل الحفظ create or update
  */
    public function saving(Invoice $invoice): void
      {
        $total = $invoice->subtotal + $invoice->tax;
        if ($invoice->status === 'void') {
            return;
        }
        if ($invoice->paid_amount <= 0) {
            $invoice->status = 'unpaid';
        } elseif ($invoice->paid_amount < $total) {
            $invoice->status = 'partial';
        } else {
            $invoice->status = 'paid';
        }
    }    
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        //
    }
}
