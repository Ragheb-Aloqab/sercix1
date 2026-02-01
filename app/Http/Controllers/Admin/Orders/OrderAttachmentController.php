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
        $path = $request->file('file')->store('orders/'.$order->id, 'public');

        $order->attachments()->create([
            'type' => $request->type, // before|after|signature|other
            'path' => $path,
            'uploaded_by_admin_id' => auth()->id(),
        ]);

        return back()->with('success', 'تم رفع المرفق.');
    }

    public function destroy(Attachment $attachment)
    {
        // يمكنك لاحقًا حذف الملف من storage
        $attachment->delete();

        return back()->with('success', 'تم حذف المرفق.');
    }
}
