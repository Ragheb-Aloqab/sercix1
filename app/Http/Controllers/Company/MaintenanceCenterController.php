<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCenter;
use App\Services\SubscriptionService;

class MaintenanceCenterController extends Controller
{
    /**
     * List all active maintenance centers (read-only).
     * Companies cannot create or manage centers — only Super Admin can.
     * This page shows centers available for RFQ selection.
     */
    public function index()
    {
        $company = auth('company')->user();
        SubscriptionService::authorize($company, 'request_maintenance_offers');

        $centers = MaintenanceCenter::active()
            ->orderBy('name')
            ->get();

        return view('company.maintenance-centers.index', [
            'centers' => $centers,
        ]);
    }
}
