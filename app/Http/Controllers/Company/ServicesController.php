<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index(Request $request)
    {
        $company = auth('company')->user();
        $q = $request->string('q')->toString();

        $services = Service::query()
            ->select('services.*')
            ->leftJoin('company_services as cs', function ($join) use ($company) {
                $join->on('cs.service_id', '=', 'services.id')
                    ->where('cs.company_id', '=', $company->id);
            })
            ->addSelect([
                'cs.base_price as pivot_base_price',
                'cs.estimated_minutes as pivot_estimated_minutes',
                'cs.is_enabled as pivot_is_enabled',
            ])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('services.name', 'like', "%{$q}%")
                        ->orWhere('services.description', 'like', "%{$q}%");
                });
            })
            ->orderBy('services.name')
            ->paginate(12)
            ->withQueryString();

        return view('company.services.index', compact('company', 'services', 'q'));
    }
}
