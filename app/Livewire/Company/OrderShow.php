<?php

namespace App\Livewire\Company;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderCancelRequested;
use App\Support\OrderStatus;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class OrderShow extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load([
            'technician:id,name,phone',
            'attachments',
            'payments',
            'invoice',
            'services',
            'vehicle',
        ]);
    }

    /** Approve driver's service request so admin gets notified and can assign technician */
    public function approveRequest(): void
    {
        $company = auth('company')->user();
        abort_unless((int) $this->order->company_id === (int) $company->id, 403);
        abort_unless($this->order->status === OrderStatus::PENDING_COMPANY, 403, 'هذا الطلب غير قيد الموافقة.');

        $this->order->update(['status' => OrderStatus::APPROVED_BY_COMPANY]);
        $this->order->refresh();
        session()->flash('success', 'تمت الموافقة على الطلب. تم إخطار الإدارة لتعيين فني.');
    }

    public function cancelOrder(): void
    {
        $company = auth('company')->user();
        abort_unless((int) $this->order->company_id === (int) $company->id, 403);

        if ($this->order->technician_id) {
            session()->flash('error', 'الطلب قيد التنفيذ ولا يمكن إلغاؤه مباشرة.');
            return;
        }

        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->notify(new OrderCancelRequested($this->order));
        }

        session()->flash('success', 'تم إرسال طلب الإلغاء للمدير.');
    }

    public function render(): View
    {
        return view('livewire.company.order-show', ['order' => $this->order]);
    }
}
