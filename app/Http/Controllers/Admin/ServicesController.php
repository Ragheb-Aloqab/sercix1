<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Services\StoreServiceRequest;
use App\Http\Requests\Dashboard\Services\UpdateServiceRequest;
use App\Models\Company;
use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index(Request $request)
    {
      
        $companyId = session('admin_company_id');
        $company = null;

        // فلاتر
        $q = trim((string) $request->get('q'));
        $status = $request->get('status', 'all'); // active | inactive | all

        // Query أساسي
        $servicesQuery = Service::query();

        // لو في شركة مختارة: فلتر خدمات الشركة عبر pivot company_services
        if ($companyId) {
            $company = Company::query()
                ->select('id', 'company_name')
                ->find($companyId);

            // إذا الشركة غير موجودة (مثلاً انحذفت) نظّف السيشن
            if (!$company) {
                session()->forget('admin_company_id');
            } else {
                $servicesQuery->whereHas('companies', function ($q) use ($companyId) {
                    $q->where('companies.id', $companyId);
                });
            }
        }

        // بحث بالاسم
        if ($q !== '') {
            $servicesQuery->where('name', 'like', "%{$q}%");
        }

        // فلتر الحالة
        if ($status === 'active') {
            $servicesQuery->where('is_active', true);
        } elseif ($status === 'inactive') {
            $servicesQuery->where('is_active', false);
        }

        $services = $servicesQuery
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.services.index', compact('services', 'company', 'q', 'status'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(StoreServiceRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        Service::create($data);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'تم إنشاء الخدمة بنجاح');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $service->update($data);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'تم تحديث الخدمة بنجاح');
    }

    public function destroy(Service $service)
    {
        // لو عندك علاقة شركات
        if (method_exists($service, 'companies')) {
            $service->companies()->detach();
        }

        // لو عندك علاقة طلبات (مثال: orders)
        if (method_exists($service, 'orders')) {
            $service->orders()->detach();
        }

        $service->delete();

        return back()->with('success', 'تم حذف الخدمة نهائيًا.');
    }

    public function toggle(Service $service)
    {
        $service->update([
            'is_active' => ! $service->is_active,
        ]);

        return back()->with('success', $service->is_active ? 'تم تفعيل الخدمة.' : 'تم تعطيل الخدمة.');
    }
}
