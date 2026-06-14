@extends('layouts.neo')
@section('title','Import Pelanggan XLSX')
@section('content')
<div class="pagehead">
    <div>
        <h1>Import Pelanggan XLSX</h1>
        <p>Upload file Excel pelanggan. Paket otomatis dibuat jika belum ada.</p>
    </div>
    <div class="actions">
        <a class="btn light" href="{{ url('/admin/customers') }}">Kembali</a>
        <a class="btn light" href="{{ url('/admin/customers/template') }}">Download Template XLSX</a>
    </div>
</div>

@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="card">
    <div class="main">Format Kolom Excel</div>
    <div class="muted" style="margin-top:6px">
        name, phone, address, odp, port_number, package_name, package_speed, billing_day, monthly_price, status
    </div>
</div>

<form class="card" method="POST" action="{{ url('/admin/customers/import') }}" enctype="multipart/form-data">
    @csrf

    <div class="field">
        <label>File Excel</label>
        <input class="input" type="file" name="file" accept=".xlsx,.xls">
    </div>

    <button class="btn" type="submit">Import XLSX</button>
</form>

<div class="card">
    <div class="main">Catatan Import</div>
    <div class="muted" style="margin-top:6px;line-height:1.5">
        Gunakan template XLSX agar kolom sesuai. Jika nomor HP sudah ada, data pelanggan akan diupdate. Jika nomor HP kosong, sistem mencocokkan berdasarkan nama. Status gunakan active atau inactive. Nominal boleh ditulis 100000 atau Rp100.000.
    </div>
</div>
@endsection
