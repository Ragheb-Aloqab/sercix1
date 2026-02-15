<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Orders\UploadAttachmentRequest;
use App\Models\Attachment;
use App\Models\Order;

class OrderAttachmentController extends Controller
{
    public function store(UploadAttachmentRequest $request, Order $order)
    {
        $this->authorize('manageAttachments', $order);

        $path = $request->file('file')->store('orders/'.$order->id, 'public');

        $order->attachments()->create([
            'type' => $request->type, // before|after|signature|other
            'path' => $path,
            'uploaded_by_admin_id' => auth()->id(),
        ]);

        return back()->with('success', __('messages.attachment_uploaded'));
    }

    public function destroy(Attachment $attachment)
    {
        $this->authorize('manageAttachments', $attachment->order);

        if ($attachment->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($attachment->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
        }
        $attachment->delete();

        return back()->with('success', __('messages.attachment_deleted'));
    }
}
