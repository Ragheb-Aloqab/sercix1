<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customers\StoreCustomerRequest;
use App\Http\Requests\Admin\Customers\UpdateCustomerRequest;
use App\Models\Company;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
      
        $q = $request->string('q')->toString();

        $customers = Company::query()
            ->when($q, function ($query) use ($q) {
                $query->where('company_name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.customers.index', compact('customers','q'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        unset($data['password'], $data['password_confirmation'], $data['logo']);
        $data['password'] = \Illuminate\Support\Facades\Hash::make($request->input('password') ?: \Illuminate\Support\Str::random(12));
        $data['vehicle_quota'] = $data['vehicle_quota'] ?? config('servx.default_vehicle_quota');

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
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Company $customer)
    {
        $data = $request->validated();
        unset($data['logo'], $data['remove_logo']);

        if ($request->boolean('remove_logo') && $customer->logo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($customer->logo);
            $data['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($customer->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($customer->logo);
            }
            $data['logo'] = $request->file('logo')->store("companies/{$customer->id}/logos", 'public');
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
