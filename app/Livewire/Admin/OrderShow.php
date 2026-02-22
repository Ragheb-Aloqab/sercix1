<?php

namespace App\Livewire\Admin;

use App\Models\Attachment;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderUpdate;
use App\Services\ActivityLogger;
use App\Support\OrderStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderShow extends Component
{
    use WithFileUploads;

    public Order $order;

    /** @var \Illuminate\Support\Collection<int, \App\Models\User> */
    public $technicians;

    public int $technician_id = 0;
    public string $assign_note = '';
    public string $to_status = '';
    public string $status_note = '';
    public string $attachment_type = 'signature';
    public $attachment_file = null;

    public function mount(Order $order, $technicians = null): void
    {
        $this->order = $order->load([
            'company',
            'vehicle',
            'technician',
            'services',
            'statusLogs',
            'invoice',
            'attachments',
        ]);
        $this->technicians = $technicians ?? collect();
        $this->to_status = $order->status;
    }

    public function assignTechnician(): void
    {
        $this->validate([
            'technician_id' => ['required', 'integer', 'exists:users,id'],
            'assign_note'   => ['nullable', 'string', 'max:500'],
        ]);

        $tech = User::query()->where('id', $this->technician_id)->where('role', 'technician')->firstOrFail();
        $from = $this->order->status;
        $to = OrderStatus::IN_PROGRESS;

        $this->order->update([
            'technician_id' => $tech->id,
            'status'        => $to,
        ]);

        $this->order->statusLogs()->create([
            'from_status' => $from,
            'to_status'   => $to,
            'note'        => $this->assign_note,
            'changed_by'  => auth()->id(),
        ]);

        $this->refreshOrder();
        session()->flash('success', 'تم إسناد الطلب للفني بنجاح.');
    }

    public function changeStatus(): void
    {
        $this->validate([
            'to_status'   => ['required', 'in:'.implode(',', OrderStatus::ALL)],
            'status_note' => ['nullable', 'string', 'max:500'],
        ]);

        $from = (string) $this->order->status;
        $to = $this->to_status;
        $user = auth()->user();
        $isAdmin = $user?->role === 'admin';
        $isAllowed = OrderStatus::canTransition($from, $to);

        if (! $isAdmin && ! $isAllowed) {
            $this->addError('to_status', "انتقال غير مسموح: {$from} → {$to}");
            return;
        }

        $note = $this->status_note;
        if ($isAdmin && ! $isAllowed && ! $note) {
            $this->addError('status_note', 'هذا انتقال غير قياسي. الرجاء كتابة سبب التغيير.');
            return;
        }
        if ($isAdmin && ! $isAllowed) {
            $note = trim(($note ? $note.' ' : '').'(تجاوز أدمن)');
        }

        $this->order->update(['status' => $to]);
        $this->order->statusLogs()->create([
            'from_status' => $from,
            'to_status'   => $to,
            'note'        => $note,
            'changed_by'  => $user->id,
        ]);

        $this->order->company?->notify(new OrderUpdate($this->order));
        ActivityLogger::log(
            action: 'hold_order',
            subjectType: 'order',
            subjectId: $this->order->id,
            description: 'تم تعليق طلب العميل'
        );

        $this->refreshOrder();
        session()->flash('success', 'تم تحديث حالة الطلب بنجاح.');
    }

    public function createInvoice(): void
    {
        $this->order->load(['services']);
        $subtotal = (float) $this->order->total_amount;
        $tax = (float) ($this->order->tax_amount ?? 0);

        $this->order->invoice()->firstOrCreate([], [
            'company_id'     => $this->order->company_id,
            'invoice_number' => 'INV-'.$this->order->id.'-'.now()->format('Ymd'),
            'subtotal'       => $subtotal,
            'tax'            => $tax,
            'paid_amount'    => 0,
        ]);

        $this->refreshOrder();
        session()->flash('success', 'تم إنشاء الفاتورة.');
    }

    public function uploadAttachment(): void
    {
        $this->validate([
            'attachment_type' => ['required', 'in:signature,other'],
            'attachment_file' => ['required', 'file', 'max:5120'],
        ]);

        $path = $this->attachment_file->store('orders/'.$this->order->id, 'public');

        $this->order->attachments()->create([
            'type'          => $this->attachment_type,
            'file_path'     => $path,
            'original_name' => $this->attachment_file->getClientOriginalName(),
            'file_size'     => $this->attachment_file->getSize(),
            'uploaded_by'   => auth()->id(),
        ]);

        $this->attachment_file = null;
        $this->refreshOrder();
        session()->flash('success', 'تم رفع المرفق.');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $att = Attachment::query()->where('order_id', $this->order->id)->findOrFail($attachmentId);
        if ($att->file_path && Storage::disk('public')->exists($att->file_path)) {
            Storage::disk('public')->delete($att->file_path);
        }
        $att->delete();
        $this->refreshOrder();
        session()->flash('success', 'تم حذف المرفق.');
    }

    protected function refreshOrder(): void
    {
        $this->order = $this->order->fresh([
            'company',
            'vehicle',
            'technician',
            'services',
            'statusLogs',
            'invoice',
            'attachments',
        ]);
    }

    public function render(): View
    {
        $attachments = $this->order->attachments ?? collect();
        $others = $attachments->whereIn('type', ['signature', 'other']);

        return view('livewire.admin.order-show', [
            'order'       => $this->order,
            'technicians' => $this->technicians,
            'others'      => $others,
        ]);
    }
}
