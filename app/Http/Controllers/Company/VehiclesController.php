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
        ]);

        // ✅ حماية: لا تختار فرع ليس للشركة
        if (!empty($data['company_branch_id'])) {
            $branchOk = CompanyBranch::query()
                ->where('id', $data['company_branch_id'])
                ->where('company_id', $company->id)
                ->exists();

            abort_unless($branchOk, 403, 'الفرع غير تابع لشركتك');
        }

        $data['company_id'] = $company->id;
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        Vehicle::create($data);

        return redirect()
            ->route('company.vehicles.index')
            ->with('success', 'تم إضافة المركبة بنجاح ✅');
    }

    /**
     * GET /company/vehicles/{vehicle}/edit
     * company.vehicles.edit
     */
    public function edit(Vehicle $vehicle)
    {
        $company = auth('company')->user();
        abort_unless((int)$vehicle->company_id === (int)$company->id, 403);

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
        $company = auth('company')->user();
        abort_unless((int)$vehicle->company_id === (int)$company->id, 403);

        $data = $request->validate([
            'company_branch_id' => ['nullable', 'integer', 'exists:company_branches,id'],

            'plate_number' => ['required', 'string', 'max:50'],
            'brand'        => ['nullable', 'string', 'max:100'],
            'model'        => ['nullable', 'string', 'max:100'],
            'year'         => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'vin'          => ['nullable', 'string', 'max:50'],

            'notes'        => ['nullable', 'string', 'max:1000'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        // ✅ حماية: لا تختار فرع ليس للشركة
        if (!empty($data['company_branch_id'])) {
            $branchOk = CompanyBranch::query()
                ->where('id', $data['company_branch_id'])
                ->where('company_id', $company->id)
                ->exists();

            abort_unless($branchOk, 403, 'الفرع غير تابع لشركتك');
        }

        $data['is_active'] = (bool)($data['is_active'] ?? $vehicle->is_active);

        $vehicle->update($data);

        return redirect()
            ->route('company.vehicles.index')
            ->with('success', 'تم تحديث المركبة بنجاح ✅');
    }
}
