<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;

class FinanceSummaryController extends Controller
{
    public function incomeMonth()
    {
        return redirect('/kasir');
    }

    public function profitMonth()
    {
        return redirect('/kasir');
    }

    public function index()
    {
        return redirect('/kasir');
    }

    public function __invoke()
    {
        return redirect('/kasir');
    }

    public function __call($method, $parameters)
    {
        return redirect('/kasir');
    }
}
