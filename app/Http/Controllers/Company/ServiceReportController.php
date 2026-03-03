<?php

namespace App\Http\Controllers\Company;

use App\Enums\MaintenanceType;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
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
     * Includes both Orders and MaintenanceRequests.
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

        $orderQuery = Order::query()
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->with(['vehicle:id,plate_number,make,model', 'orderServices.service']);

        if ($vehicleId > 0) {
            $vehicle = Vehicle::where('company_id', $company->id)->find($vehicleId);
            if ($vehicle) {
                $orderQuery->where('vehicle_id', $vehicleId);
            }
        }

        if ($serviceTypeId > 0) {
            $orderQuery->whereHas('orderServices', fn ($q) => $q->where('service_id', $serviceTypeId));
        }

        $orders = $orderQuery->latest('created_at')->get();

        $mrQuery = MaintenanceRequest::query()
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->whereRaw('(COALESCE(final_invoice_amount, 0) > 0 OR COALESCE(approved_quote_amount, 0) > 0)')
            ->with(['vehicle:id,plate_number,make,model']);

        if ($vehicleId > 0) {
            $mrQuery->where('vehicle_id', $vehicleId);
        }

        $maintenanceRequests = $mrQuery->latest('created_at')->get();

        $analytics = $this->analytics->getMaintenanceAnalytics($from, $to, $company->id, $vehicleId ?: null, $serviceTypeId ?: null);
        $totalCost = $analytics['total_cost'];
        $orderCount = $analytics['order_count'];
        $totals = ['total_cost' => $totalCost, 'order_count' => $orderCount];

        $byServiceType = $this->analytics->getMaintenanceByServiceType($from, $to, $company->id, $vehicleId ?: null);

        $ordersWithDisplay = $orders->map(function ($order) {
            $statusLabel = \Illuminate\Support\Str::startsWith(__('common.status_' . $order->status), 'common.') ? $order->status : __('common.status_' . $order->status);
            $firstService = $order->orderServices->first();
            $serviceName = $firstService?->display_name ?? '-';
            $orderServicesCount = $order->orderServices->count();
            return (object) [
                'type' => 'order',
                'order' => $order,
                'maintenanceRequest' => null,
                'date' => $order->created_at,
                'statusLabel' => $statusLabel,
                'serviceName' => $serviceName,
                'orderServicesCount' => $orderServicesCount,
                'amount' => (float) $order->total_amount,
            ];
        });

        $mrsWithDisplay = $maintenanceRequests->map(function ($mr) {
            $amount = (float) ($mr->final_invoice_amount ?? $mr->approved_quote_amount ?? 0);
            $serviceName = MaintenanceType::tryFrom($mr->maintenance_type)?->label() ?? $mr->maintenance_type ?? __('reports.maintenance_request');
            return (object) [
                'type' => 'maintenance_request',
                'order' => null,
                'maintenanceRequest' => $mr,
                'date' => $mr->created_at,
                'statusLabel' => $mr->status_label,
                'serviceName' => $serviceName,
                'orderServicesCount' => 1,
                'amount' => $amount,
            ];
        });

        $allItems = $ordersWithDisplay->concat($mrsWithDisplay)
            ->sortByDesc(fn ($r) => $r->date?->timestamp ?? 0)
            ->values();

        $perPage = 25;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $allItems->forPage($currentPage, $perPage)->values(),
            $allItems->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $vehicles = Vehicle::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        $services = Service::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('company.reports.service', compact(
            'company',
            'totals',
            'totalCost',
            'orderCount',
            'paginated',
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
