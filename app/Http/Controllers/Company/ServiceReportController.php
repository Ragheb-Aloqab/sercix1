<?php

namespace App\Http\Controllers\Company;

use App\Enums\MaintenanceType;
use App\Exports\ServiceReportExport;
use App\Http\Controllers\Controller;
use App\Models\CompanyMaintenanceInvoice;
use App\Models\MaintenanceRequest;
use App\Models\Order;
use App\Models\Service;
use App\Models\Vehicle;
use App\Services\AnalyticsService;
use App\Services\ServiceReportPdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ServiceReportController extends Controller
{
    public function __construct(
        private AnalyticsService $analytics,
        private ServiceReportPdfService $pdfService
    ) {}

    /**
     * Build report data from request (shared by index and exports).
     * Returns: company, from, to, vehicleId, serviceTypeId, allItems, totals, analytics, byServiceType, vehicles, services.
     */
    private function getReportDataFromRequest(Request $request): array
    {
        $company = auth('company')->user();

        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();
        $vehicleId = $request->integer('vehicle_id', 0);
        $serviceTypeId = $request->integer('service_type_id', 0);

        $orderQuery = Order::query()
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->with(['vehicle:id,plate_number,make,model', 'orderServices.service', 'invoice:id,order_id,invoice_number']);

        if ($vehicleId > 0) {
            $vehicle = $company->vehicles()->find($vehicleId);
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
            ->with(['vehicle:id,plate_number,make,model', 'requestServices.service', 'requestServices.driverProposedService']);

        if ($vehicleId > 0) {
            $mrQuery->where('vehicle_id', $vehicleId);
        }

        if ($serviceTypeId > 0) {
            $mrQuery->whereHas('requestServices', fn ($q) => $q->where('service_id', $serviceTypeId));
        }

        $maintenanceRequests = $mrQuery->latest('created_at')->get();

        $cmiQuery = CompanyMaintenanceInvoice::query()
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->with(['vehicle:id,plate_number,make,model', 'services:id,name']);

        if ($vehicleId > 0) {
            $cmiQuery->where('vehicle_id', $vehicleId);
        }

        if ($serviceTypeId > 0) {
            $cmiQuery->whereHas('services', fn ($q) => $q->where('services.id', $serviceTypeId));
        }

        $companyMaintenanceInvoices = $cmiQuery->latest('created_at')->get();

        $analytics = $this->analytics->getMaintenanceAnalytics($from, $to, $company->id, $vehicleId ?: null, $serviceTypeId ?: null);
        $totalCost = $analytics['total_cost'];
        $orderCount = $analytics['order_count'];
        $totals = ['total_cost' => $totalCost, 'order_count' => $orderCount];
        $byServiceType = $this->analytics->getMaintenanceByServiceType($from, $to, $company->id, $vehicleId ?: null);

        $ordersWithDisplay = $orders->map(function ($order) {
            $statusLabel = Str::startsWith(__('common.status_' . $order->status), 'common.') ? $order->status : __('common.status_' . $order->status);
            $firstService = $order->orderServices->first();
            $serviceName = $firstService?->display_name ?? '-';
            $orderServicesCount = $order->orderServices->count();
            $invoiceDisplay = $order->invoice?->invoice_number ?? '—';
            return (object) [
                'type' => 'order',
                'order' => $order,
                'maintenanceRequest' => null,
                'date' => $order->created_at,
                'statusLabel' => $statusLabel,
                'serviceName' => $serviceName,
                'orderServicesCount' => $orderServicesCount,
                'amount' => (float) $order->total_amount,
                'invoiceDisplay' => $invoiceDisplay,
            ];
        });

        $mrsWithDisplay = $maintenanceRequests->map(function ($mr) {
            $amount = (float) ($mr->final_invoice_amount ?? $mr->approved_quote_amount ?? 0);
            $servicesList = $mr->requestServices->isNotEmpty()
                ? $mr->requestServices->map(fn ($rs) => $rs->display_name)->filter()->values()->join(', ')
                : null;
            $serviceName = $servicesList !== null && $servicesList !== ''
                ? $servicesList
                : (MaintenanceType::tryFrom($mr->maintenance_type)?->label() ?? $mr->maintenance_type ?? __('reports.maintenance_request'));
            $invoiceDisplay = ($mr->final_invoice_amount || $mr->final_invoice_pdf_path) ? __('common.yes') : '—';
            return (object) [
                'type' => 'maintenance_request',
                'order' => null,
                'maintenanceRequest' => $mr,
                'date' => $mr->created_at,
                'statusLabel' => $mr->status_label,
                'serviceName' => $serviceName,
                'orderServicesCount' => $mr->requestServices->count(),
                'amount' => $amount,
                'invoiceDisplay' => $invoiceDisplay,
            ];
        });

        $serviceTypeKey = 'maintenance.service_type_' . str_replace('-', '_', (string) (CompanyMaintenanceInvoice::SERVICE_TYPE_MAINTENANCE ?? 'maintenance'));
        $cmiWithDisplay = $companyMaintenanceInvoices->map(function ($cmi) {
            $serviceName = $cmi->services->isNotEmpty()
                ? $cmi->services->pluck('name')->join(', ')
                : (function () use ($cmi) {
                    $key = 'maintenance.service_type_' . str_replace('-', '_', (string) ($cmi->service_type ?? 'maintenance'));
                    $t = __($key);
                    return $t !== $key ? $t : ($cmi->service_type ?? __('maintenance.invoice'));
                })();
            return (object) [
                'type' => 'company_maintenance_invoice',
                'order' => null,
                'maintenanceRequest' => null,
                'companyMaintenanceInvoice' => $cmi,
                'date' => $cmi->created_at,
                'statusLabel' => __('reports.company_invoice') ?: 'Company invoice',
                'serviceName' => $serviceName,
                'orderServicesCount' => 1,
                'amount' => (float) $cmi->amount,
                'invoiceDisplay' => __('common.yes'),
            ];
        });

        $allItems = $ordersWithDisplay->concat($mrsWithDisplay)->concat($cmiWithDisplay)
            ->sortByDesc(fn ($r) => $r->date?->timestamp ?? 0)
            ->values();

        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        $services = Service::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return [
            'company' => $company,
            'from' => $from,
            'to' => $to,
            'vehicleId' => $vehicleId,
            'serviceTypeId' => $serviceTypeId,
            'allItems' => $allItems,
            'totals' => $totals,
            'analytics' => $analytics,
            'byServiceType' => $byServiceType,
            'vehicles' => $vehicles,
            'services' => $services,
        ];
    }

    /**
     * Company-wide service/maintenance report.
     * Includes both Orders and MaintenanceRequests.
     */
    public function index(Request $request)
    {
        $data = $this->getReportDataFromRequest($request);

        $perPage = 25;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $data['allItems']->forPage($currentPage, $perPage)->values(),
            $data['allItems']->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $paginated->withQueryString();

        return view('company.reports.service', [
            'company' => $data['company'],
            'totals' => $data['totals'],
            'totalCost' => $data['analytics']['total_cost'],
            'orderCount' => $data['analytics']['order_count'],
            'paginated' => $paginated,
            'vehicles' => $data['vehicles'],
            'services' => $data['services'],
            'from' => $data['from'],
            'to' => $data['to'],
            'vehicleId' => $data['vehicleId'],
            'serviceTypeId' => $data['serviceTypeId'],
            'analytics' => $data['analytics'],
            'byServiceType' => $data['byServiceType'],
        ]);
    }

    /**
     * Export service report as Excel.
     */
    public function exportExcel(Request $request)
    {
        $data = $this->getReportDataFromRequest($request);
        $filename = 'service-report-' . $data['from']->format('Y-m-d') . '-to-' . $data['to']->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new ServiceReportExport($data['allItems'], $data['totals'], $data['analytics'], $data['byServiceType'], app()->getLocale()),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Export service report as PDF.
     */
    public function exportPdf(Request $request): Response
    {
        $data = $this->getReportDataFromRequest($request);

        $pdfContent = $this->pdfService->generate(
            $data['company'],
            $data['allItems'],
            $data['totals'],
            $data['analytics'],
            $data['byServiceType'],
            $data['from']->format('Y-m-d'),
            $data['to']->format('Y-m-d'),
            $data['vehicleId'] ? $data['vehicles']->firstWhere('id', $data['vehicleId']) : null
        );

        $filename = 'service-report-' . $data['from']->format('Y-m-d') . '-to-' . $data['to']->format('Y-m-d') . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
