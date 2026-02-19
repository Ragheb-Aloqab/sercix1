<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\FuelRefill;
use App\Models\Order;
use App\Models\Service;
use App\Models\Vehicle;
use App\Support\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DriverController extends Controller
{
    public function dashboard()
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = $this->driverPhoneVariants($phone);
        $vehicles = Vehicle::whereIn('driver_phone', $phoneVariants)
            ->with('company:id,company_name')
            ->where('is_active', true)
            ->get();

        $requests = Order::whereIn('driver_phone', $phoneVariants)
            ->with(['vehicle', 'company:id,company_name'])
            ->latest()
            ->take(10)
            ->get();

        $requestsWithDisplay = $requests->map(function ($r) {
            $statusLabel = \Illuminate\Support\Str::startsWith(__('common.status_' . $r->status), 'common.') ? $r->status : __('common.status_' . $r->status);
            return (object) ['request' => $r, 'statusLabel' => $statusLabel];
        });

        return view('driver.dashboard', compact('vehicles', 'requests', 'requestsWithDisplay'));
    }

    public function createRequest()
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = $this->driverPhoneVariants($phone);
        $vehicles = Vehicle::whereIn('driver_phone', $phoneVariants)
            ->where('is_active', true)
            ->with([
                'company:id,company_name',
                'company.services' => fn ($q) => $q->where('company_services.is_enabled', true)->orderBy('services.name'),
            ])
            ->get();
        if ($vehicles->isEmpty()) {
            return redirect()->route('driver.dashboard')->with('error', __('messages.driver_no_vehicles'));
        }
        // Fallback: when company has no enabled services, show all active services so driver always sees a list
        $fallbackServices = Service::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $fallbackList = $fallbackServices->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray();

        $vehicleServicesForJs = [];
        foreach ($vehicles as $v) {
            $list = [];
            if ($v->company && $v->company->relationLoaded('services') && $v->company->services->isNotEmpty()) {
                foreach ($v->company->services as $s) {
                    $list[] = ['id' => $s->id, 'name' => $s->name];
                }
            }
            $vehicleServicesForJs[$v->id] = !empty($list) ? $list : $fallbackList;
        }

        $selectedVehicleId = old('vehicle_id') ?: (request('vehicle') ?: ($vehicles->count() === 1 ? $vehicles->first()->id : null));

        return view('driver.request-create', compact('vehicles', 'fallbackServices', 'vehicleServicesForJs', 'selectedVehicleId'));
    }

    public function storeRequest(Request $request)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = $this->driverPhoneVariants($phone);

        $serviceType = $request->input('service_type', 'existing');
        $rules = [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'service_type' => ['required', 'in:existing,custom'],
            'quotation_invoice' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB, images + PDF
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($serviceType === 'existing') {
            $rules['service_id'] = ['required', 'integer', 'exists:services,id'];
            $rules['service_price'] = ['required', 'numeric', 'gt:0'];
        } else {
            $rules['custom_service_name'] = ['required', 'string', 'max:255'];
            $rules['custom_service_description'] = ['required', 'string', 'max:1000'];
            $rules['custom_service_price'] = ['required', 'numeric', 'gt:0'];
        }

        $data = $request->validate($rules);

        $vehicle = Vehicle::where('id', $data['vehicle_id'])->whereIn('driver_phone', $phoneVariants)->first();
        if (!$vehicle) {
            abort(403, __('messages.driver_vehicle_not_linked'));
        }

        if ($serviceType === 'existing') {
            $company = $vehicle->company;
            $validService = Service::query()
                ->select('services.id')
                ->leftJoin('company_services as cs', function ($join) use ($company) {
                    $join->on('cs.service_id', '=', 'services.id')->where('cs.company_id', '=', $company->id);
                })
                ->where('services.id', $data['service_id'])
                ->where('services.is_active', true)
                ->where(function ($q) {
                    $q->where('cs.is_enabled', true)->orWhereNull('cs.is_enabled');
                })
                ->exists();
            if (!$validService) {
                return back()->withErrors(['service_id' => __('messages.driver_invalid_services')])->withInput();
            }
        }

        $order = Order::create([
            'company_id' => $vehicle->company_id,
            'vehicle_id' => $vehicle->id,
            'status' => OrderStatus::PENDING_APPROVAL,
            'requested_by_name' => $vehicle->driver_name ?? __('driver.driver'),
            'driver_phone' => $vehicle->driver_phone,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($serviceType === 'existing') {
            $service = Service::where('id', $data['service_id'])->where('is_active', true)->firstOrFail();
            $price = (float) $data['service_price'];
            $order->orderServices()->create([
                'service_id' => $service->id,
                'qty' => 1,
                'unit_price' => $price,
                'total_price' => $price,
            ]);
        } else {
            $price = (float) $data['custom_service_price'];
            $order->orderServices()->create([
                'service_id' => null,
                'custom_service_name' => $data['custom_service_name'],
                'custom_service_description' => $data['custom_service_description'],
                'qty' => 1,
                'unit_price' => $price,
                'total_price' => $price,
            ]);
        }

        // Store quotation invoice (required for company approval)
        $file = $request->file('quotation_invoice');
        $path = $file->store('quotation-invoices/' . $order->id, 'public');
        $order->attachments()->create([
            'type' => 'quotation_invoice',
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'uploaded_by' => null,
        ]);

        return redirect()->route('driver.dashboard')->with('success', __('messages.driver_request_sent'));
    }

    public function showRequest(Order $order)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = $this->driverPhoneVariants($phone);
        $order->load(['orderServices.service', 'vehicle', 'company:id,company_name', 'attachments']);

        if (!in_array($order->driver_phone, $phoneVariants)) {
            abort(403, __('messages.driver_vehicle_not_linked'));
        }

        $statusLabel = \Illuminate\Support\Str::startsWith(__('common.status_' . $order->status), 'common.') ? $order->status : __('common.status_' . $order->status);
        $firstService = $order->orderServices->first();
        $serviceName = $firstService?->display_name ?? '-';
        $amount = $order->total_amount;
        $driverInvoice = $order->attachments->where('type', 'driver_invoice')->first();

        return view('driver.request-show', compact('order', 'statusLabel', 'serviceName', 'amount', 'driverInvoice'));
    }

    public function startRequest(Request $request, Order $order)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = $this->driverPhoneVariants($phone);
        if (!in_array($order->driver_phone, $phoneVariants)) {
            abort(403, __('messages.driver_vehicle_not_linked'));
        }
        if ($order->status !== OrderStatus::APPROVED) {
            return back()->with('error', __('messages.driver_request_not_approved'));
        }

        $order->update(['status' => OrderStatus::IN_PROGRESS]);
        return redirect()->route('driver.request.show', $order)->with('success', __('messages.driver_request_started'));
    }

    public function uploadInvoice(Request $request, Order $order)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = $this->driverPhoneVariants($phone);
        if (!in_array($order->driver_phone, $phoneVariants)) {
            abort(403, __('messages.driver_vehicle_not_linked'));
        }
        if ($order->status !== OrderStatus::IN_PROGRESS) {
            return back()->with('error', __('messages.driver_invoice_after_approval'));
        }

        $request->validate([
            'invoice' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB max
        ]);

        $path = $request->file('invoice')->store('driver-invoices/' . $order->id, 'public');
        $file = $request->file('invoice');

        $order->attachments()->create([
            'type' => 'driver_invoice',
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'uploaded_by' => null,
        ]);

        $order->update(['status' => OrderStatus::PENDING_CONFIRMATION]);
        return redirect()->route('driver.request.show', $order)->with('success', __('messages.driver_invoice_uploaded'));
    }

    public function createFuelRefill()
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = $this->driverPhoneVariants($phone);
        $vehicles = Vehicle::whereIn('driver_phone', $phoneVariants)
            ->with('company:id,company_name')
            ->where('is_active', true)
            ->get();

        if ($vehicles->isEmpty()) {
            return redirect()->route('driver.dashboard')->with('error', __('messages.driver_no_vehicles'));
        }

        return view('driver.fuel-refill-create', compact('vehicles'));
    }

    public function storeFuelRefill(Request $request)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = $this->driverPhoneVariants($phone);

        $data = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'liters' => ['required', 'numeric', 'min:0.01', 'max:9999'],
            'cost' => ['required', 'numeric', 'min:0', 'max:999999'],
            'refilled_at' => ['required', 'date'],
            'odometer_km' => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'fuel_type' => ['nullable', 'string', 'in:petrol,diesel,premium'],
            'notes' => ['nullable', 'string', 'max:500'],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'], // 5MB max
        ]);

        $vehicle = Vehicle::where('id', $data['vehicle_id'])->whereIn('driver_phone', $phoneVariants)->first();
        if (!$vehicle) {
            abort(403, __('messages.driver_vehicle_not_linked'));
        }

        $pricePerLiter = $data['liters'] > 0 ? round($data['cost'] / $data['liters'], 2) : null;

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('fuel-receipts', 'public');
        }

        FuelRefill::create([
            'vehicle_id' => $vehicle->id,
            'company_id' => $vehicle->company_id,
            'liters' => $data['liters'],
            'cost' => $data['cost'],
            'price_per_liter' => $pricePerLiter,
            'refilled_at' => $data['refilled_at'],
            'odometer_km' => $data['odometer_km'] ?? null,
            'fuel_type' => $data['fuel_type'] ?? 'petrol',
            'notes' => $data['notes'] ?? null,
            'receipt_path' => $receiptPath,
            'provider' => FuelRefill::PROVIDER_MANUAL,
            'logged_by_phone' => $phone,
        ]);

        return redirect()->route('driver.dashboard')->with('success', __('messages.driver_fuel_success'));
    }

    /** Match DB whether company saved +966... or 05... */
    private function driverPhoneVariants(?string $phone): array
    {
        if ($phone === null || $phone === '') {
            return [];
        }
        $variants = [trim($phone)];
        if (str_starts_with($phone, '+966')) {
            $variants[] = '0' . substr($phone, 4);
        }
        if (str_starts_with($phone, '0') && strlen(preg_replace('/[^0-9]/', '', $phone)) >= 10) {
            $digits = preg_replace('/[^0-9]/', '', $phone);
            $variants[] = '+966' . substr($digits, 1, 9);
        }
        return array_unique(array_filter($variants));
    }
}
