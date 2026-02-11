<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;

class BankTransferReview extends Component
{
    use WithPagination;

    public function confirmPayment(int $paymentId): void
    {
        $payment = Payment::query()
            ->where('method', 'bank')
            ->where('status', 'pending')
            ->findOrFail($paymentId);

        $payment->update([
            'status'      => 'paid',
            'paid_at'     => now(),
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $this->dispatch('notify', type: 'success', message: 'تم تأكيد التحويل بنجاح.');
    }

    public function rejectPayment(int $paymentId): void
    {
        $payment = Payment::query()
            ->where('method', 'bank')
            ->where('status', 'pending')
            ->findOrFail($paymentId);

        $payment->update([
            'status'      => 'failed',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $this->dispatch('notify', type: 'success', message: 'تم رفض التحويل.');
    }

    public function render()
    {
        $payments = Payment::query()
            ->where('method', 'bank')
            ->where('status', 'pending')
            ->with(['order:id,company_id,status', 'company:id,company_name,phone', 'bankAccount'])
            ->latest()
            ->paginate(10);

        return view('livewire.admin.bank-transfer-review', [
            'payments' => $payments,
        ]);
    }
}
