<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;

class BranchesController extends Controller
{
    /**
     * GET /company/branches
     * company.branches.index
     */
    public function index(Request $request)
    {
        $company = auth('company')->user();
        $q = $request->string('q')->toString();

        $branches = CompanyBranch::query()
            ->where('company_id', $company->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                        ->orWhere('address_line', 'like', "%{$q}%")
                        ->orWhere('district', 'like', "%{$q}%")
                        ->orWhere('city', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('company.branches.index', compact('company', 'branches', 'q'));
    }

    /**
     * GET /company/branches/create
     * company.branches.create
     */
    public function create()
    {
        $company = auth('company')->user();
        return view('company.branches.create', compact('company'));
    }

    /**
     * POST /company/branches
     * company.branches.store
     */
    public function store(Request $request)
    {
        $company = auth('company')->user();

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:190'],
            'contact_person' => ['nullable', 'string', 'max:190'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:190'],

            'city'           => ['nullable', 'string', 'max:190'],
            'district'       => ['nullable', 'string', 'max:190'],
            'address_line'   => ['nullable', 'string', 'max:255'],

            'lat'            => ['nullable', 'numeric'],
            'lng'            => ['nullable', 'numeric'],

            'is_default'     => ['nullable', 'boolean'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $data['company_id']  = $company->id;
        $data['is_active']   = (bool) ($data['is_active'] ?? true);
        $data['is_default']  = (bool) ($data['is_default'] ?? false);

        
        if ($data['is_default']) {
            CompanyBranch::where('company_id', $company->id)->update(['is_default' => false]);
        }

        CompanyBranch::create($data);

        return redirect()
            ->route('company.branches.index')
            ->with('success', __('messages.branch_added'));
    }

    /**
     * GET /company/branches/{branch}/edit
     * company.branches.edit
     */
    public function edit(CompanyBranch $branch)
    {
        $this->authorize('update', $branch);

        $company = auth('company')->user();
        return view('company.branches.edit', compact('company', 'branch'));
    }

    /**
     * PATCH /company/branches/{branch}
     * company.branches.update
     */
    public function update(Request $request, CompanyBranch $branch)
    {
        $this->authorize('update', $branch);

        $company = auth('company')->user();
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:190'],
            'contact_person' => ['nullable', 'string', 'max:190'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:190'],

            'city'           => ['nullable', 'string', 'max:190'],
            'district'       => ['nullable', 'string', 'max:190'],
            'address_line'   => ['nullable', 'string', 'max:255'],

            'lat'            => ['nullable', 'numeric'],
            'lng'            => ['nullable', 'numeric'],

            'is_default'     => ['nullable', 'boolean'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $data['is_active']  = (bool) ($data['is_active'] ?? $branch->is_active);
        $data['is_default'] = (bool) ($data['is_default'] ?? $branch->is_default);

        if ($data['is_default']) {
            CompanyBranch::where('company_id', $company->id)
                ->where('id', '!=', $branch->id)
                ->update(['is_default' => false]);
        }

        $branch->update($data);

        return redirect()
            ->route('company.branches.index')
            ->with('success', __('messages.branch_updated'));
    }
}
