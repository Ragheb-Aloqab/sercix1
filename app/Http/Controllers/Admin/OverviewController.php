<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class OverviewController extends Controller
{
    public function __invoke()
    {
        return view('admin.overview.index');
    }
}
