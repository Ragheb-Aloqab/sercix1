<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * GET /company/dashboard
     * company.dashboard
     */
    public function index()
    {
        $company = Auth::guard('company')->user();

        // لاحقًا تقدر تضيف إحصائيات:
        // $ordersCount = $company->orders()->count();

        return view('company.dashboard.index', compact('company'));
    }
}
