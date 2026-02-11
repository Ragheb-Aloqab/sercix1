<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryMovementExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $search = $request->string('search')->toString();
        $type = $request->string('type')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $query = InventoryTransaction::query()
            ->with(['item', 'creator'])
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('item', fn ($qq) => $qq->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%'));
            })
            ->when($type !== '', fn ($q) => $q->where('transaction_type', $type))
            ->when($dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest('created_at');

        $filename = 'inventory-movements-' . now()->format('Y-m-d-His') . '.csv';

        return new StreamedResponse(function () use ($query) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, [
                'التاريخ', 'الصنف', 'النوع', 'التغير', 'الكمية بعد', 'السعر', 'الطلب', 'المستخدم',
            ]);

            $query->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $inv) {
                    fputcsv($out, [
                        $inv->created_at->format('Y-m-d H:i'),
                        $inv->item?->name ?? '—',
                        $inv->transaction_type,
                        $inv->quantity_change,
                        $inv->new_quantity,
                        $inv->unit_price ?? '—',
                        $inv->related_order_type ?? '—',
                        $inv->creator?->name ?? $inv->created_by,
                    ]);
                }
            });
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
