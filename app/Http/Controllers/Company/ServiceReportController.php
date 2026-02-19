<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceReportController extends Controller
{
    /**
     * Company-wide service/maintenance report.
     */
    public function index(Request $request)
    {
        $company = auth('company')->user();

        $from = $request->filled('from')
            ? \Carbon\Carbon::parse($request->from)->startOfDay()
            : now()->startOfMonth();
        $to = $request->filled('to')
            ? \Carbon\Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();
        $vehicleId = $request->integer('vehicle_id', 0);

        $query = Order::query()
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->with(['vehicle:id,plate_number,make,model', 'orderServices.service']);

        if ($vehicleId > 0) {
            $vehicle = Vehicle::where('company_id', $company->id)->find($vehicleId);
            if ($vehicle) {
                $query->where('vehicle_id', $vehicleId);
            }
        }

        $orders = $query->latest('created_at')->paginate(25)->withQueryString();

        $totalsQuery = Order::query()
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->when($vehicleId > 0, fn ($q) => $q->where('vehicle_id', $vehicleId));

        $orderIds = (clone $totalsQuery)->pluck('id');
        $totalCost = (float) DB::table('order_services')
            ->whereIn('order_id', $orderIds)
            ->selectRaw('COALESCE(SUM(COALESCE(total_price, qty * unit_price)), 0) as total')
            ->value('total') ?: 0;
        $orderCount = $orderIds->count();

        $totals = ['total_cost' => $totalCost, 'order_count' => $orderCount];

        $vehicles = Vehicle::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        return view('company.reports.service', compact(
            'company',
            'orders',
            'totals',
            'vehicles',
            'from',
            'to',
            'vehicleId'
        ));
    }
}
