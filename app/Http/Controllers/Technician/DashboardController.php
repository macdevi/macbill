<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('technician.dashboard');
    }
}
