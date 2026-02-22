<?php

namespace App\Livewire\Company;

use App\Models\Order;
use App\Support\OrderStatus;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Attributes\On;

class OrderShow extends Component
{
    public Order $order;

    public string $rejection_reason = '';

    public bool $showRejectModal = false;

    public function mount(Order $order): void
    {
        $this->order = $order->load([
            'technician:id,name,phone',
            'attachments',
            'invoice',
            'services',
            'orderServices',
            'vehicle',
        ]);
    }

    /** Approve driver's service request */
    public function approveRequest(): void
    {
        $this->order->refresh();
        $company = auth('company')->user();

        if ((int) $this->order->company_id !== (int) $company->id) {
            abort(403);
        }
        if ($this->order->status !== OrderStatus::PENDING_APPROVAL) {
            session()->flash('error', __('orders.approve_only_pending'));
            return;
        }
        if (!$this->order->hasQuotationInvoice()) {
            session()->flash('error', __('orders.quotation_required_for_approval'));
            return;
        }

        $from = $this->order->status;
        $this->order->update(['status' => OrderStatus::APPROVED, 'rejection_reason' => null]);
        $this->order->statusLogs()->create([
            'from_status' => $from,
            'to_status' => OrderStatus::APPROVED,
            'note' => 'Company approved',
        ]);
        $this->order->refresh();
        session()->flash('success', 'تمت الموافقة على الطلب. يمكن للسائق تنفيذ الخدمة الآن.');
    }

    #[On('open-reject-modal')]
    public function openRejectModal(): void
    {
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejection_reason = '';
    }

    /** Reject driver's service request */
    public function rejectRequest(): void
    {
        $this->validate(['rejection_reason' => ['nullable', 'string', 'max:500']]);

        $company = auth('company')->user();
        abort_unless((int) $this->order->company_id === (int) $company->id, 403);
        abort_unless($this->order->status === OrderStatus::PENDING_APPROVAL, 403, 'هذا الطلب غير قيد الموافقة.');

        $from = $this->order->status;
        $this->order->update([
            'status' => OrderStatus::REJECTED,
            'rejection_reason' => $this->rejection_reason ?: null,
        ]);
        $this->order->statusLogs()->create([
            'from_status' => $from,
            'to_status' => OrderStatus::REJECTED,
            'note' => $this->rejection_reason ?: 'Company rejected',
        ]);
        $this->order->refresh();
        $this->showRejectModal = false;
        $this->rejection_reason = '';
        session()->flash('success', 'تم رفض الطلب.');
    }

    /** Confirm completion after driver uploaded invoice */
    public function confirmCompletion(): void
    {
        $this->order->refresh();
        $company = auth('company')->user();
        abort_unless((int) $this->order->company_id === (int) $company->id, 403);
        abort_unless($this->order->status === OrderStatus::PENDING_CONFIRMATION, 403, 'الطلب غير قيد التأكيد.');

        $from = $this->order->status;
        $this->order->update(['status' => OrderStatus::COMPLETED]);
        $this->order->statusLogs()->create([
            'from_status' => $from,
            'to_status' => OrderStatus::COMPLETED,
            'note' => 'Company confirmed completion',
        ]);
        $this->order->refresh();
        session()->flash('success', 'تم تأكيد إكمال الطلب.');
    }

    public function cancelOrder(): void
    {
        $company = auth('company')->user();
        abort_unless((int) $this->order->company_id === (int) $company->id, 403);

        if ($this->order->technician_id && !in_array($this->order->status, [OrderStatus::PENDING_APPROVAL, OrderStatus::APPROVED], true)) {
            session()->flash('error', 'الطلب قيد التنفيذ ولا يمكن إلغاؤه مباشرة.');
            return;
        }

        // Admin notifications removed - only Company ↔ Driver

        session()->flash('success', 'تم إرسال طلب الإلغاء للمدير.');
    }

    public function render(): View
    {
        $attachments = $this->order->attachments ?? collect();
        $quotationInvoice = $attachments->where('type', 'quotation_invoice')->first();
        $hasQuotation = $quotationInvoice !== null;

        $amount = (float) ($this->order->total_amount ?? 0);
        $firstService = $this->order->orderServices->first() ?? $this->order->services->first();
        $serviceName = $firstService?->display_name ?? $firstService?->name ?? '-';
        $driverInvoice = $attachments->where('type', 'driver_invoice')->first();

        $isQuotationImage = $quotationInvoice && in_array(
            strtolower(pathinfo($quotationInvoice->file_path ?? '', PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png', 'gif', 'webp']
        );
        $isDriverInvoiceImage = $driverInvoice && in_array(
            strtolower(pathinfo($driverInvoice->file_path ?? '', PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png', 'gif', 'webp']
        );

        return view('livewire.company.order-show', [
            'order' => $this->order,
            'quotationInvoice' => $quotationInvoice,
            'hasQuotation' => $hasQuotation,
            'amount' => $amount,
            'serviceName' => $serviceName,
            'driverInvoice' => $driverInvoice,
            'isQuotationImage' => $isQuotationImage,
            'isDriverInvoiceImage' => $isDriverInvoiceImage,
        ]);
    }
}
