<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customers\StoreCustomerRequest;
use App\Http\Requests\Admin\Customers\UpdateCustomerRequest;
use App\Models\Company;
use App\Services\SubdomainService;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $planId = $request->has('plan_id') ? $request->integer('plan_id') : null;

        $customers = Company::query()
            ->with('subscriptionPlan')
            ->when($q, function ($query) use ($q) {
                $query->where('company_name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->when($planId !== null, function ($query) use ($planId) {
                if ($planId === 0) {
                    $query->whereNull('plan_id');
                } else {
                    $query->where('plan_id', $planId);
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $plans = \App\Models\SubscriptionPlan::ordered()->get();

        return view('admin.customers.index', compact('customers', 'q', 'planId', 'plans'));
    }

    public function create()
    {
        $plans = \App\Models\SubscriptionPlan::active()->ordered()->get();
        return view('admin.customers.create', compact('plans'));
    }

    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        unset($data['password'], $data['password_confirmation'], $data['logo']);
        $data['password'] = \Illuminate\Support\Facades\Hash::make($request->input('password') ?: \Illuminate\Support\Str::random(12));
        $data['vehicle_quota'] = $data['vehicle_quota'] ?? config('servx.default_vehicle_quota');
        if (array_key_exists('plan_id', $data) && $data['plan_id'] === '') {
            $data['plan_id'] = null;
        }
        if (empty($data['plan_id']) && config('servx.default_plan_id')) {
            $data['plan_id'] = (int) config('servx.default_plan_id');
        }

        // Auto-generate unique subdomain from company name (SaaS requirement)
        if (empty($data['subdomain'] ?? null)) {
            $data['subdomain'] = SubdomainService::generateFromName($data['company_name']);
        }

        $company = Company::create($data);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store("companies/{$company->id}/logos", 'public');
            $company->update(['logo' => $path]);
        }

        return redirect()
            ->route('admin.customers.index')
            ->with('success', __('messages.customer_added'));
    }

    public function edit(Company $customer)
    {
        $plans = \App\Models\SubscriptionPlan::active()->ordered()->get();
        return view('admin.customers.edit', compact('customer', 'plans'));
    }

    public function update(UpdateCustomerRequest $request, Company $customer)
    {
        $data = $request->validated();
        unset($data['logo'], $data['remove_logo']);
        if (array_key_exists('plan_id', $data) && $data['plan_id'] === '') {
            $data['plan_id'] = null;
        }

        if ($request->boolean('remove_logo') && $customer->logo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($customer->logo);
            $data['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($customer->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($customer->logo);
            }
            $data['logo'] = $request->file('logo')->store("companies/{$customer->id}/logos", 'public');
        }

        // Auto-generate subdomain on edit if missing (SaaS requirement)
        if (empty($customer->subdomain) && !empty($data['company_name'] ?? null)) {
            $data['subdomain'] = SubdomainService::generateFromName($data['company_name']);
        }

        $customer->update($data);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', __('messages.customer_updated'));
    }

    public function destroy(Company $customer)
    {
        $customer->delete();

        return back()->with('success', __('messages.customer_deleted'));
    }
}
