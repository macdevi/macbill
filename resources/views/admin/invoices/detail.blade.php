@extends('layouts.neo')
@section('title','Detail Invoice')
@section('content')
@php
    $settings = $appSettings ?? \App\Services\SettingService::allMerged();

    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $fmtDate = function ($value, $format = 'd/m/Y') {
        try {
            return $value ? \Illuminate\Support\Carbon::parse($value)->format($format) : '-';
        } catch (\Throwable $e) {
            return $value ?: '-';
        }
    };

    $customer = $invoice->customer;
    $package = $invoice->package ?: ($customer?->package ?? null);

    $invoiceNumber = \App\Services\SettingService::normalizeInvoiceNumber($invoice->invoice_number, $invoice->period, $invoice->id);

    $badge = function ($status) {
        return match ($status) {
            'Lunas' => 'green',
            'Bayar Awal' => 'blue',
            'Nunggak' => 'red',
            'Belum Bayar' => 'yellow',
            default => 'blue',
        };
    };

    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'print' => '<svg viewBox="0 0 24 24"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>',
            'user' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
            'invoice' => '<svg viewBox="0 0 24 24"><path d="M7 3h10l4 4v14H7z"/><path d="M17 3v5h5"/><path d="M10 13h8"/><path d="M10 17h8"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.invoice-detail-hero{
    background:linear-gradient(135deg,#1d4ed8,#0891b2);
    border-radius:28px;
    padding:22px;
    color:#fff;
    margin-bottom:12px;
    box-shadow:0 18px 44px rgba(16,24,40,.08);
}
.invoice-detail-hero span{display:block;color:#e0f2fe;font-size:13px;font-weight:800}
.invoice-detail-hero b{display:block;margin-top:6px;font-size:30px;line-height:1;letter-spacing:-.07em}
.invoice-detail-hero p{margin:10px 0 0;color:#e0f2fe;font-size:14px;line-height:1.45}
.invoice-detail-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:10px}
.invoice-detail-card{background:#fff;border:1px solid #e4eaf3;border-radius:22px;padding:15px;box-shadow:0 10px 24px rgba(16,24,40,.055)}
.invoice-detail-card .label{color:#667085;font-size:12px;font-weight:800}
.invoice-detail-card .value{margin-top:7px;color:#101828;font-size:18px;font-weight:950;letter-spacing:-.04em}
.invoice-detail-box{background:#fff;border:1px solid #e4eaf3;border-radius:24px;padding:16px;box-shadow:0 10px 24px rgba(16,24,40,.055);margin-top:10px}
.invoice-detail-box h3{margin:0 0 12px;color:#101828;font-size:16px;letter-spacing:-.04em}
.invoice-row{display:flex;justify-content:space-between;gap:14px;border-bottom:1px solid #f1f5f9;padding:10px 0;font-size:14px}
.invoice-row span:first-child{color:#667085}
.invoice-row span:last-child{text-align:right;color:#101828;font-weight:850}
.invoice-note{margin-top:14px;border:1px solid #fedf89;background:#fffaeb;color:#b54708;border-radius:16px;padding:12px;font-size:13px;line-height:1.45}
@media(max-width:980px){.invoice-detail-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:760px){
    .invoice-detail-hero{border-radius:22px;padding:18px}
    .invoice-detail-hero b{font-size:25px}
    .invoice-detail-card{border-radius:18px;padding:12px}
    .invoice-detail-card .value{font-size:16px}
    .invoice-detail-grid{gap:8px}
}
</style>

<div class="pagehead">
    <div>
        <h1>Detail Invoice</h1>
        <p>Detail tagihan pelanggan berdasarkan data Pengaturan Umum.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/invoices') }}">{!! $i('back') !!}Tagihan</a>
        <a class="btn" target="_blank" href="{{ url('/admin/invoices/'.$invoice->id.'/print') }}">{!! $i('print') !!}Cetak</a>
    </div>
</div>

<div class="invoice-detail-hero">
    <span>{{ $settings['business_name'] ?? 'MAC-SERVICE' }}</span>
    <b>{{ $invoiceNumber }}</b>
    <p>{{ $customer?->name ?: 'Pelanggan tidak ditemukan' }} · Periode {{ $invoice->period ?: '-' }}</p>
</div>

<div class="invoice-detail-grid">
    <div class="invoice-detail-card">
        <div class="label">Status</div>
        <div class="value"><span class="badge {{ $badge($invoice->status) }}">{{ $invoice->status }}</span></div>
    </div>

    <div class="invoice-detail-card">
        <div class="label">Nominal</div>
        <div class="value">{{ $money($invoice->amount) }}</div>
    </div>

    <div class="invoice-detail-card">
        <div class="label">Jatuh Tempo</div>
        <div class="value">{{ $fmtDate($invoice->due_date) }}</div>
    </div>

    <div class="invoice-detail-card">
        <div class="label">Dibayar</div>
        <div class="value">{{ $invoice->paid_at ? $fmtDate($invoice->paid_at, 'd/m/Y H:i') : '-' }}</div>
    </div>
</div>

<div class="invoice-detail-box">
    <h3>{!! $i('user') !!}Data Pelanggan</h3>

    <div class="invoice-row"><span>Nama</span><span>{{ $customer?->name ?: '-' }}</span></div>
    <div class="invoice-row"><span>No HP</span><span>{{ $customer?->phone ?: '-' }}</span></div>
    <div class="invoice-row"><span>Alamat</span><span>{{ $customer?->address ?: '-' }}</span></div>
    <div class="invoice-row"><span>ODP / Port</span><span>{{ $customer?->odpMaster?->name ?: ($customer?->odp ?: '-') }}{{ $customer?->port_number ? ' / Port '.$customer->port_number : '' }}</span></div>
</div>

<div class="invoice-detail-box">
    <h3>{!! $i('invoice') !!}Rincian Tagihan</h3>

    <div class="invoice-row"><span>Invoice</span><span>{{ $invoiceNumber }}</span></div>
    <div class="invoice-row"><span>Periode</span><span>{{ $invoice->period ?: '-' }}</span></div>
    <div class="invoice-row"><span>Paket</span><span>{{ $package?->name ?: '-' }}{{ $package?->speed ? ' · '.$package->speed : '' }}</span></div>
    <div class="invoice-row"><span>Metode Pembayaran</span><span>{{ $invoice->payment_method ?: '-' }}</span></div>
    <div class="invoice-row"><span>Total Tagihan</span><span>{{ $money($invoice->amount) }}</span></div>
    <div class="invoice-row"><span>Terbayar</span><span>{{ $money($invoice->paid_amount ?: 0) }}</span></div>
</div>

@if(!empty($settings['invoice_note']))
    <div class="invoice-note">{{ $settings['invoice_note'] }}</div>
@endif
@endsection
