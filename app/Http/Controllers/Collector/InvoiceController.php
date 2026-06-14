<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
    public function index()
    {
        return redirect('/kasir/tagihan');
    }

    public function history()
    {
        return redirect('/kasir/riwayat');
    }

    public function collect($invoice = null)
    {
        return redirect('/kasir/tagihan');
    }

    public function paySelected()
    {
        return redirect('/kasir/tagihan');
    }

    public function payAll($customer = null)
    {
        return redirect('/kasir/tagihan');
    }

    public function customerInvoices($customer = null)
    {
        return redirect('/kasir/status-pelanggan');
    }

    public function __call($method, $parameters)
    {
        return redirect('/kasir/tagihan');
    }
}
