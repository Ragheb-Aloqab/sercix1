<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customers\StoreCustomerRequest;
use App\Http\Requests\Admin\Customers\UpdateCustomerRequest;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
      
        $q = $request->string('q')->toString();

        $customers = Company::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name','like',"%{$q}%")
                      ->orWhere('phone','like',"%{$q}%")
                      ->orWhere('email','like',"%{$q}%");
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
        Company::create($request->validated());

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
        
        $customer->update($request->validated());
        
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
