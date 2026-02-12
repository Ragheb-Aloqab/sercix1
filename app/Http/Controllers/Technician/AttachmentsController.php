<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentsController extends Controller
{
    public function storeBefore(Request $request, Order $order)
    {
        return $this->store($request, $order, 'before_photo');
    }

    public function storeAfter(Request $request, Order $order)
    {
        return $this->store($request, $order, 'after_photo');
    }

    private function store(Request $request, Order $order, string $type)
    {
        $this->authorize('manageAttachments', $order);

        $technician = Auth::guard('web')->user();
        $validated = $request->validate([
            // ✅ خليه images مثل ما انت تستخدمه في الفورم
            'images'   => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5MB
        ]);

        foreach ($validated['images'] as $file) {
            // ✅ مسار مرتب (قبل/بعد)
            $folder = $type === 'before_photo' ? 'before' : 'after';

            $path = $file->store("orders/{$order->id}/{$folder}", 'public');

            Attachment::create([
                'order_id'      => $order->id,
                'type'          => $type,
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size'     => $file->getSize(),
                'uploaded_by'   => $technician->id,
            ]);
        }

        // ✅ الأفضل: ارجع لصفحة المهمة نفسها
        return redirect()
            ->route('tech.tasks.show', $order->id)
            ->with('success', $type === 'before_photo' ? 'تم رفع صور (قبل) بنجاح ✅' : 'تم رفع صور (بعد) بنجاح ✅');
    }

    public function destroy(Order $order, Attachment $attachment)
    {
        $this->authorize('manageAttachments', $order);

        abort_unless(
            (int) $attachment->order_id === (int) $order->id,
            404,
            'Attachment does not belong to this order.'
        );

        // حذف الملف من التخزين
        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()
            ->route('tech.tasks.show', $order->id)
            ->with('success', 'تم حذف المرفق ✅');
    }
}
