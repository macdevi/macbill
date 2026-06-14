@extends('layouts.neo')
@section('title','Cetak Invoice')
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

    $businessName = $settings['business_name'] ?? 'MAC-SERVICE';
    $businessLogo = !empty($settings['business_logo']) ? asset('storage/'.$settings['business_logo']) : null;
    $appName = $settings['app_name'] ?? 'MAC-SERVICE';
@endphp

<style>
@media print {
    body{background:#fff !important}
    .topbar,.sidebar,.pagehead,.no-print,nav,header,.mobile-bottom{display:none !important}
    .wrap,.content,main{padding:0 !important;margin:0 !important;max-width:none !important;background:#fff !important}
    .invoice-print-shell{padding:0 !important;margin:0 !important}
    .invoice-print-box{box-shadow:none !important;border-radius:0 !important;border:0 !important;margin:0 auto !important}
}
.invoice-print-shell{display:grid;place-items:start center}
.invoice-print-box{width:min(820px,100%);background:#fff;border:1px solid #e4eaf3;border-radius:26px;box-shadow:0 18px 44px rgba(16,24,40,.08);overflow:hidden}
.invoice-print-head{padding:24px;background:linear-gradient(135deg,#1d4ed8,#0891b2);color:#fff;display:flex;justify-content:space-between;gap:18px;align-items:flex-start}
.invoice-print-logo{height:54px;max-width:160px;object-fit:contain;margin-bottom:10px;filter:drop-shadow(0 8px 14px rgba(0,0,0,.18))}
.invoice-print-head h2{margin:0;font-size:26px;letter-spacing:-.06em}
.invoice-print-head p{margin:6px 0 0;color:#e0f2fe;font-size:13px;line-height:1.45}
.invoice-print-title{text-align:right}
.invoice-print-title b{display:block;font-size:24px;letter-spacing:-.05em}
.invoice-print-title span{display:block;margin-top:5px;color:#e0f2fe;font-size:13px}
.invoice-print-body{padding:24px}
.invoice-print-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px}
.invoice-print-card{border:1px solid #e4eaf3;border-radius:18px;padding:14px}
.invoice-print-card b{display:block;color:#101828;font-size:14px;margin-bottom:8px}
.invoice-print-card p{margin:4px 0;color:#667085;font-size:13px;line-height:1.45}
.invoice-print-table{width:100%;border-collapse:collapse;margin-top:16px;border:1px solid #e4eaf3;border-radius:14px;overflow:hidden}
.invoice-print-table th{background:#f8fbff;color:#667085;text-align:left;font-size:12px;padding:12px;border-bottom:1px solid #e4eaf3}
.invoice-print-table td{padding:12px;border-bottom:1px solid #f1f5f9;color:#101828;font-size:14px}
.invoice-print-table tr:last-child td{border-bottom:0}
.invoice-print-total{display:flex;justify-content:flex-end;margin-top:18px}
.invoice-print-total-box{min-width:280px;background:#101828;color:#fff;border-radius:18px;padding:16px;display:flex;justify-content:space-between;gap:14px;align-items:center}
.invoice-print-total-box span{color:#d0d5dd;font-size:13px}
.invoice-print-total-box b{font-size:24px;letter-spacing:-.04em}
.invoice-print-note{margin-top:18px;border:1px solid #fedf89;background:#fffaeb;color:#b54708;border-radius:16px;padding:12px;font-size:13px;line-height:1.45}
.invoice-print-foot{text-align:center;margin-top:18px;color:#667085;font-size:12px;line-height:1.45;border-top:1px dashed #cbd5e1;padding-top:14px}
@media(max-width:760px){
    .invoice-print-head{flex-direction:column}
    .invoice-print-title{text-align:left}
    .invoice-print-body{padding:16px}
    .invoice-print-grid{grid-template-columns:1fr}
    .invoice-print-total-box{min-width:100%}
}
</style>

<div class="pagehead no-print">
    <div>
        <h1>Cetak Invoice</h1>
        <p>Invoice menggunakan identitas dari Pengaturan Umum.</p>
    </div>
    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/invoices/'.$invoice->id.'/detail') }}">Detail</a>
        <button class="btn" onclick="window.print()">Cetak</button>
    </div>
</div>

<div class="invoice-print-shell">
    <div class="invoice-print-box">
        <div class="invoice-print-head">
            <div>
                @if($businessLogo)
                    <img class="invoice-print-logo" src="{{ $businessLogo }}" alt="Logo">
                @endif
                <h2>{{ $businessName }}</h2>
                <p>{{ $settings['business_address'] ?: 'Alamat usaha belum diisi.' }}</p>
                <p>
                    {{ $settings['business_phone'] ?: '-' }}
                    @if(!empty($settings['business_email'])) · {{ $settings['business_email'] }} @endif
                </p>
            </div>

            <div class="invoice-print-title">
                <b>INVOICE</b>
                <span>{{ $invoiceNumber }}</span>
            </div>
        </div>

        <div class="invoice-print-body">
            <div class="invoice-print-grid">
                <div class="invoice-print-card">
                    <b>Ditagihkan Kepada</b>
                    <p><strong>{{ $customer?->name ?: '-' }}</strong></p>
                    <p>{{ $customer?->phone ?: '-' }}</p>
                    <p>{{ $customer?->address ?: '-' }}</p>
                    <p>{{ $customer?->odpMaster?->name ?: ($customer?->odp ?: '-') }}{{ $customer?->port_number ? ' / Port '.$customer->port_number : '' }}</p>
                </div>

                <div class="invoice-print-card">
                    <b>Informasi Tagihan</b>
                    <p>Periode: <strong>{{ $invoice->period ?: '-' }}</strong></p>
                    <p>Jatuh Tempo: <strong>{{ $fmtDate($invoice->due_date) }}</strong></p>
                    <p>Status: <strong>{{ $invoice->status }}</strong></p>
                    <p>Tanggal Cetak: <strong>{{ now()->format('d/m/Y H:i') }}</strong></p>
                </div>
            </div>

            <table class="invoice-print-table">
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th>Periode</th>
                        <th style="text-align:right">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            Layanan Internet
                            @if($package)
                                <br><small>{{ $package->name }}{{ $package->speed ? ' · '.$package->speed : '' }}</small>
                            @endif
                        </td>
                        <td>{{ $invoice->period ?: '-' }}</td>
                        <td style="text-align:right">{{ $money($invoice->amount) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="invoice-print-total">
                <div class="invoice-print-total-box">
                    <span>Total Tagihan</span>
                    <b>{{ $money($invoice->amount) }}</b>
                </div>
            </div>

            @if(!empty($settings['invoice_note']))
                <div class="invoice-print-note">{{ $settings['invoice_note'] }}</div>
            @endif

            <div class="invoice-print-foot">
                {{ $settings['landing_title'] ?? $appName }}<br>
                Dokumen ini dicetak otomatis dari sistem {{ $appName }}.
            </div>
        </div>
    </div>
</div>
@endsection
