<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use App\Models\Vehicle;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceReportController extends Controller
{
    public function __construct(
        private AnalyticsService $analytics
    ) {}

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
        $serviceTypeId = $request->integer('service_type_id', 0);

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

        if ($serviceTypeId > 0) {
            $query->whereHas('orderServices', fn ($q) => $q->where('service_id', $serviceTypeId));
        }

        $orders = $query->latest('created_at')->paginate(25)->withQueryString();

        $totalsQuery = Order::query()
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->when($vehicleId > 0, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($serviceTypeId > 0, fn ($q) => $q->whereHas('orderServices', fn ($qq) => $qq->where('service_id', $serviceTypeId)));

        $orderIds = (clone $totalsQuery)->pluck('id');
        $totalCost = (float) (DB::table('order_services')
            ->whereIn('order_id', $orderIds)
            ->selectRaw('COALESCE(SUM(COALESCE(total_price, qty * unit_price)), 0) as total')
            ->value('total') ?? 0);
        $orderCount = $orderIds->count();

        $totals = ['total_cost' => $totalCost, 'order_count' => $orderCount];

        $analytics = $this->analytics->getMaintenanceAnalytics($from, $to, $company->id, $vehicleId ?: null, $serviceTypeId ?: null);
        $byServiceType = $this->analytics->getMaintenanceByServiceType($from, $to, $company->id, $vehicleId ?: null);

        $ordersWithDisplay = $orders->map(function ($order) {
            $statusLabel = \Illuminate\Support\Str::startsWith(__('common.status_' . $order->status), 'common.') ? $order->status : __('common.status_' . $order->status);
            $firstService = $order->orderServices->first();
            $serviceName = $firstService?->display_name ?? '-';
            $orderServicesCount = $order->orderServices->count();
            return (object) [
                'order' => $order,
                'statusLabel' => $statusLabel,
                'serviceName' => $serviceName,
                'orderServicesCount' => $orderServicesCount,
            ];
        });

        $vehicles = Vehicle::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        $services = Service::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('company.reports.service', compact(
            'company',
            'orders',
            'totals',
            'totalCost',
            'orderCount',
            'ordersWithDisplay',
            'vehicles',
            'services',
            'from',
            'to',
            'vehicleId',
            'serviceTypeId',
            'analytics',
            'byServiceType'
        ));
    }
}
