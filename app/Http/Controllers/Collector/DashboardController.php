<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return redirect('/kasir');
    }

    public function __call($method, $parameters)
    {
        return redirect('/kasir');
    }
}
