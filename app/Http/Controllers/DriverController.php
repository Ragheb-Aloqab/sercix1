<?php

namespace App\Http\Controllers;

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

        return view('driver.dashboard', compact('vehicles', 'requests'));
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
        return view('driver.request-create', compact('vehicles', 'fallbackServices'));
    }

    public function storeRequest(Request $request)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = $this->driverPhoneVariants($phone);
        $data = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $vehicle = Vehicle::where('id', $data['vehicle_id'])->whereIn('driver_phone', $phoneVariants)->first();
        if (!$vehicle) {
            abort(403, __('messages.driver_vehicle_not_linked'));
        }

        $company = $vehicle->company;
        // Allow company-enabled services (with pivot price) or any active global service (use base_price)
        $services = Service::query()
            ->select('services.*')
            ->leftJoin('company_services as cs', function ($join) use ($company) {
                $join->on('cs.service_id', '=', 'services.id')->where('cs.company_id', '=', $company->id);
            })
            ->addSelect(['cs.base_price as pivot_base_price', 'cs.is_enabled as pivot_is_enabled'])
            ->whereIn('services.id', $data['service_ids'])
            ->where('services.is_active', true)
            ->get();

        if ($services->count() !== count(array_unique($data['service_ids']))) {
            return back()->withErrors(['service_ids' => __('messages.driver_invalid_services')])->withInput();
        }

        $order = Order::create([
            'company_id' => $vehicle->company_id,
            'vehicle_id' => $vehicle->id,
            'status' => OrderStatus::PENDING_COMPANY,
            'requested_by_name' => $vehicle->driver_name ?? __('driver.driver'),
            'driver_phone' => $vehicle->driver_phone,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $syncData = [];
        foreach ($services as $s) {
            $qty = 1;
            $unitPrice = (float) ($s->pivot_base_price ?? $s->base_price ?? 0);
            $syncData[$s->id] = [
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $qty * $unitPrice,
            ];
        }
        $order->services()->sync($syncData);

        return redirect()->route('driver.dashboard')->with('success', __('messages.driver_request_sent'));
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
            'receipt' => ['nullable', 'image', 'max:5120'], // 5MB max
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
