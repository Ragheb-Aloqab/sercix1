<?php

namespace App\Http\Controllers\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\AdminAuditNotification;

class DashboardController extends Controller
{
    public function index()
    {
        
        auth()->user()->notify(new AdminAuditNotification());
        return view('admin.index');
    }
}
