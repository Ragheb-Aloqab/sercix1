<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;

class VehiclesController extends Controller
{
    /**
     * GET /company/vehicles
     * company.vehicles.index
     */
    public function index(Request $request)
    {
        $company = auth('company')->user();
        $q = $request->string('q')->toString();

        $vehicles = Vehicle::query()
            ->where('company_id', $company->id)
            ->with(['branch:id,name']) // إذا عندك علاقة branch()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('plate_number', 'like', "%{$q}%")
                        ->orWhere('brand', 'like', "%{$q}%")
                        ->orWhere('model', 'like', "%{$q}%")
                        ->orWhere('vin', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('company.vehicles.index', compact('company', 'vehicles', 'q'));
    }

    /**
     * GET /company/vehicles/create
     * company.vehicles.create
     */
    public function create()
    {
        $company = auth('company')->user();

        $branches = CompanyBranch::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('company.vehicles.create', compact('company', 'branches'));
    }

    /**
     * POST /company/vehicles
     * company.vehicles.store
     */
    public function store(Request $request)
    {
        $company = auth('company')->user();

        $data = $request->validate([
            'company_branch_id' => ['nullable', 'integer', 'exists:company_branches,id'],

            'plate_number' => ['required', 'string', 'max:50'],
            'brand'        => ['nullable', 'string', 'max:100'],
            'model'        => ['nullable', 'string', 'max:100'],
            'year'         => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'vin'          => ['nullable', 'string', 'max:50'],

            'notes'        => ['nullable', 'string', 'max:1000'],
            'is_active'    => ['nullable', 'boolean'],
            'driver_name'  => ['nullable', 'string', 'max:100'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
        ]);

        // ✅ حماية: لا تختار فرع ليس للشركة
        if (!empty($data['company_branch_id'])) {
            $branch = CompanyBranch::findOrFail($data['company_branch_id']);
            $this->authorize('view', $branch);
        }

        $data['company_id'] = $company->id;
        $data['is_active'] = (bool)($data['is_active'] ?? true);
        if (!empty($data['driver_phone'])) {
            $data['driver_phone'] = $this->normalizePhone($data['driver_phone']);
        }

        Vehicle::create($data);

        return redirect()
            ->route('company.vehicles.index')
            ->with('success', 'تم إضافة المركبة بنجاح ✅');
    }

    /**
     * GET /company/vehicles/{vehicle}
     * company.vehicles.show — تفاصيل المركبة + كل الطلبات والخدمات والمدفوعات
     */
    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $company = auth('company')->user();
        $vehicle->load([
            'branch:id,name',
            'orders' => fn ($q) => $q->with(['services', 'payments', 'technician:id,name,phone'])
                ->latest(),
        ]);

        return view('company.vehicles.show', compact('company', 'vehicle'));
    }

    /**
     * GET /company/vehicles/{vehicle}/edit
     * company.vehicles.edit
     */
    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $company = auth('company')->user();
        $branches = CompanyBranch::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('company.vehicles.edit', compact('company', 'vehicle', 'branches'));
    }

    /**
     * PATCH /company/vehicles/{vehicle}
     * company.vehicles.update
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $company = auth('company')->user();
        $data = $request->validate([
            'company_branch_id' => ['nullable', 'integer', 'exists:company_branches,id'],

            'plate_number' => ['required', 'string', 'max:50'],
            'brand'        => ['nullable', 'string', 'max:100'],
            'model'        => ['nullable', 'string', 'max:100'],
            'year'         => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'vin'          => ['nullable', 'string', 'max:50'],

            'notes'        => ['nullable', 'string', 'max:1000'],
            'is_active'    => ['nullable', 'boolean'],
            'driver_name'  => ['nullable', 'string', 'max:100'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
        ]);

        // ✅ حماية: لا تختار فرع ليس للشركة
        if (!empty($data['company_branch_id'])) {
            $branch = CompanyBranch::findOrFail($data['company_branch_id']);
            $this->authorize('view', $branch);
        }

        $data['is_active'] = (bool)($data['is_active'] ?? $vehicle->is_active);
        if (array_key_exists('driver_phone', $data) && $data['driver_phone'] !== null) {
            $data['driver_phone'] = $data['driver_phone'] === '' ? null : $this->normalizePhone($data['driver_phone']);
        }

        $vehicle->update($data);

        return redirect()
            ->route('company.vehicles.index')
            ->with('success', 'تم تحديث المركبة بنجاح ✅');
    }

    /** Normalize Saudi phone to +966XXXXXXXXX so driver login matches. */
    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($digits, '966') && strlen($digits) >= 12) {
            return '+' . substr($digits, 0, 12);
        }
        if (str_starts_with($digits, '0') && strlen($digits) >= 10) {
            return '+966' . substr($digits, 1, 9);
        }
        if (strlen($digits) === 9 && str_starts_with($digits, '5')) {
            return '+966' . $digits;
        }
        return $phone;
    }
}
