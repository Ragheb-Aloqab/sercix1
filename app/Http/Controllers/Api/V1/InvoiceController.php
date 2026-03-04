<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * GET /api/v1/invoices
     * List company invoices with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $company = $request->user();

        $query = Invoice::query()
            ->where('company_id', $company->id)
            ->with(['order:id,status,vehicle_id', 'order.vehicle:id,plate_number,name', 'fuelRefill:id,vehicle_id']);

        if ($request->filled('search')) {
            $q = $request->string('search')->toString();
            $query->where(function ($qq) use ($q) {
                $qq->where('id', $q)
                    ->orWhere('invoice_number', 'like', "%{$q}%");
            });
        }

        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->string('invoice_type')->toString());
        }

        if ($request->filled('vehicle_id')) {
            $query->where(function ($qq) use ($request) {
                $vid = (int) $request->vehicle_id;
                $qq->whereHas('order', fn ($o) => $o->where('vehicle_id', $vid))
                    ->orWhereHas('fuelRefill', fn ($f) => $f->where('vehicle_id', $vid));
            });
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', Carbon::parse($request->from)->startOfDay());
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', Carbon::parse($request->to)->endOfDay());
        }

        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);
        $paginator = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
