<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

class InsurancesController extends Controller
{
    /**
     * Display the My Insurance page (Coming Soon).
     * GET /company/insurances
     */
    public function index()
    {
        return view('company.insurances.index');
    }
}
