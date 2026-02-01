<?php

namespace App\Http\Controllers\Admin\Maps;

use App\Http\Controllers\Controller;
use App\Models\TechnicianLocation;
use Illuminate\Http\Request;

class TechniciansMapController extends Controller
{
    public function index()
    {
        $locations = TechnicianLocation::query()
            ->with(['technician:id,name,phone,email'])
            ->latest('updated_at')
            ->get();

        return view('admin.maps.technicians', compact('locations'));
    }
}
