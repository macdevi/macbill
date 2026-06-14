<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function index()
    {
        return redirect('/kasir/status-pelanggan');
    }

    public function __call($method, $parameters)
    {
        return redirect('/kasir/status-pelanggan');
    }
}
