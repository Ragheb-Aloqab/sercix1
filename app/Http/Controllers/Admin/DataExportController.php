<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ActivitiesExport;
use App\Exports\CompaniesExport;
use App\Exports\OrdersExport;
use App\Exports\ServicesExport;
use App\Exports\VehiclesExport;
use App\Models\Company;
use App\Models\Order;
use App\Models\Vehicle;
use App\Models\Service;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataExportController extends Controller
{
    public function orders(Request $request): StreamedResponse
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $companyId = $request->integer('company_id', 0);
        $vehicleId = $request->integer('vehicle_id', 0);

        $query = Order::query()
            ->with(['company:id,company_name,phone,email', 'vehicle:id,plate_number,make,model', 'orderServices'])
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($companyId > 0, fn ($q) => $q->where('company_id', $companyId))
            ->when($vehicleId > 0, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->orderBy('id');

        $filename = 'orders_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID', 'Company', 'Phone', 'Email', 'Vehicle', 'Plate', 'Status', 'Total Amount', 'City', 'Address',
                'Requested By', 'Driver Phone', 'Scheduled At', 'Created At', 'Updated At',
            ]);

            $query->chunk(500, function ($orders) use ($handle) {
                foreach ($orders as $o) {
                    $totalAmount = (float) ($o->total_amount ?? 0);
                    fputcsv($handle, [
                        $o->id,
                        $o->company?->company_name ?? '',
                        $o->company?->phone ?? '',
                        $o->company?->email ?? '',
                        trim(($o->vehicle?->make ?? '') . ' ' . ($o->vehicle?->model ?? '')),
                        $o->vehicle?->plate_number ?? '',
                        $o->status ?? '',
                        number_format($totalAmount, 2, '.', ''),
                        $o->city ?? '',
                        $o->address ?? '',
                        $o->requested_by_name ?? '',
                        $o->driver_phone ?? '',
                        $o->scheduled_at?->format('Y-m-d H:i') ?? '',
                        $o->created_at?->format('Y-m-d H:i:s') ?? '',
                        $o->updated_at?->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function companies(Request $request): StreamedResponse
    {
        $query = Company::query()
            ->withCount(['vehicles', 'orders'])
            ->orderBy('id');

        $filename = 'companies_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Company Name', 'Phone', 'Email', 'Status', 'Vehicles Count', 'Orders Count', 'Created At', 'Updated At']);

            $query->chunk(500, function ($companies) use ($handle) {
                foreach ($companies as $c) {
                    fputcsv($handle, [
                        $c->id,
                        $c->company_name ?? '',
                        $c->phone ?? '',
                        $c->email ?? '',
                        $c->status ?? '',
                        $c->vehicles_count ?? 0,
                        $c->orders_count ?? 0,
                        $c->created_at?->format('Y-m-d H:i:s') ?? '',
                        $c->updated_at?->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function vehicles(Request $request): StreamedResponse
    {
        $companyId = $request->integer('company_id', 0);
        $branchId = $request->integer('branch_id', 0);

        $query = Vehicle::query()
            ->with('company:id,company_name')
            ->when($companyId > 0, fn ($q) => $q->where('company_id', $companyId))
            ->when($branchId > 0, fn ($q) => $q->where('company_branch_id', $branchId))
            ->orderBy('id');

        $filename = 'vehicles_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID', 'Company', 'Plate Number', 'Make', 'Model', 'Year', 'Type', 'Color',
                'Driver Name', 'Driver Phone', 'Is Active', 'Created At', 'Updated At',
            ]);

            $query->chunk(500, function ($vehicles) use ($handle) {
                foreach ($vehicles as $v) {
                    fputcsv($handle, [
                        $v->id,
                        $v->company?->company_name ?? '',
                        $v->plate_number ?? '',
                        $v->make ?? '',
                        $v->model ?? '',
                        $v->year ?? '',
                        $v->type ?? '',
                        $v->color ?? '',
                        $v->driver_name ?? '',
                        $v->driver_phone ?? '',
                        $v->is_active ? 'Yes' : 'No',
                        $v->created_at?->format('Y-m-d H:i:s') ?? '',
                        $v->updated_at?->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function services(Request $request): StreamedResponse
    {
        $query = Service::query()->orderBy('id');

        $filename = 'services_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Description', 'Base Price', 'Duration Minutes', 'Is Active', 'Created At', 'Updated At']);

            foreach ($query->cursor() as $s) {
                fputcsv($handle, [
                    $s->id,
                    $s->name ?? '',
                    $s->description ?? '',
                    $s->base_price ?? 0,
                    $s->duration_minutes ?? 0,
                    $s->is_active ? 'Yes' : 'No',
                    $s->created_at?->format('Y-m-d H:i:s') ?? '',
                    $s->updated_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function activities(Request $request): StreamedResponse
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $query = Activity::query()
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->orderBy('id');

        $filename = 'activity_logs_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Actor Type', 'Actor ID', 'Action', 'Subject Type', 'Subject ID', 'Description', 'Created At']);

            foreach ($query->cursor() as $a) {
                fputcsv($handle, [
                    $a->id,
                    $a->actor_type ?? '',
                    $a->actor_id ?? '',
                    $a->action ?? '',
                    $a->subject_type ?? '',
                    $a->subject_id ?? '',
                    $a->description ?? '',
                    $a->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function ordersExcel(Request $request)
    {
        $from = $request->get('from') ?: $request->get('dateFrom');
        $to = $request->get('to') ?: $request->get('dateTo');
        $companyId = $request->integer('company_id', 0) ?: null;
        $vehicleId = $request->integer('vehicle_id', 0) ?: null;
        $filename = 'orders_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new OrdersExport($from, $to, $companyId, $vehicleId), $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    public function companiesExcel(Request $request)
    {
        $filename = 'companies_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new CompaniesExport, $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    public function vehiclesExcel(Request $request)
    {
        $companyId = $request->integer('company_id', 0) ?: null;
        $branchId = $request->integer('branch_id', 0) ?: null;
        $filename = 'vehicles_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new VehiclesExport($companyId, $branchId), $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    public function servicesExcel(Request $request)
    {
        $filename = 'services_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new ServicesExport, $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    public function activitiesExcel(Request $request)
    {
        $from = $request->get('from') ?: $request->get('dateFrom');
        $to = $request->get('to') ?: $request->get('dateTo');
        $filename = 'activity_logs_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new ActivitiesExport($from, $to), $filename, \Maatwebsite\Excel\Excel::XLSX);
    }
}
