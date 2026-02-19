<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    /**
     * Reports hub – links to Fuel, Service, and Other reports.
     */
    public function index()
    {
        return view('company.reports.index');
    }
}
