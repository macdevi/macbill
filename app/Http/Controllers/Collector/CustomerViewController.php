<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;

class CustomerViewController extends Controller
{
    public function __invoke($customer = null)
    {
        return redirect('/kasir/status-pelanggan');
    }

    public function show($customer = null)
    {
        return redirect('/kasir/status-pelanggan');
    }

    public function index($customer = null)
    {
        return redirect('/kasir/status-pelanggan');
    }

    public function __call($method, $parameters)
    {
        return redirect('/kasir/status-pelanggan');
    }
}
