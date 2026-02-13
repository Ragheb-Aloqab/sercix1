<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        
        $activities = Activity::latest()->paginate(20);

        return view('admin.activities.index', compact('activities'));
    }
}