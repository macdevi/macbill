@extends('layouts.neo')
@section('title','Nota Pembayaran')
@section('content')
@php
    $settings = $appSettings ?? \App\Services\SettingService::allMerged();
    $businessLogo = !empty($settings['business_logo']) ? asset('storage/'.$settings['business_logo']) : null;

    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $fmtDate = function ($value, $format = 'd/m/Y H:i') {
        try {
            return $value ? \Illuminate\Support\Carbon::parse($value)->format($format) : '-';
        } catch (\Throwable $e) {
            return $value ?: '-';
        }
    };

    $invoice = $payment->invoice;
    $customer = $payment->customer;
    $package = $customer?->package;

    $isCollector = request()->is('collector/*');
    $historyUrl = $isCollector ? url('/collector/history') : url('/admin/reports/finance');
    $tagihanUrl = $isCollector ? url('/collector/invoices') : url('/admin/invoices');

    $receiptNumber = \App\Services\SettingService::receiptNumber($payment->id);
    $invoiceNumber = $invoice ? \App\Services\SettingService::normalizeInvoiceNumber($invoice->invoice_number, $invoice->period, $invoice->id) : '-';

    $phoneDigits = preg_replace('/\D+/', '', (string) $customer?->phone);
    $waNumber = $phoneDigits;
    if (str_starts_with($waNumber, '0')) {
        $waNumber = '62'.substr($waNumber, 1);
    }

    $waText = rawurlencode(
        "Terima kasih, pembayaran internet Anda sudah kami terima.\n\n".
        "Pelanggan: ".($customer?->name ?: '-')."\n".
        "Nota: ".$receiptNumber."\n".
        "Invoice: ".$invoiceNumber."\n".
        "Periode: ".($invoice?->period ?: '-')."\n".
        "Total: ".$money($payment->amount)."\n".
        "Tanggal: ".$fmtDate($payment->paid_at)."\n\n".
        ($settings['business_name'] ?? 'MAC-SERVICE')
    );

    $collectorName = '-';
    try {
        $collectorName = $payment->collector?->username ?? $payment->collector?->name ?? '-';
    } catch (\Throwable $e) {
        $collectorName = '-';
    }

    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'print' => '<svg viewBox="0 0 24 24"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>',
            'wa' => '<svg viewBox="0 0 24 24"><path d="M20 11.5a8 8 0 0 1-11.8 7L4 20l1.5-4.1A8 8 0 1 1 20 11.5z"/><path d="M9 8.5c.5 2 2 3.5 4 4l1.2-1.2 2 1c-.3 1.1-1.2 1.9-2.4 1.9-2.8 0-6-3.2-6-6 0-1.2.8-2.1 1.9-2.4l1 2L9 8.5z"/></svg>',
            'history' => '<svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v6h6"/><path d="M12 7v5l3 2"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
@media print {
    body{background:#fff !important}
    .topbar,.sidebar,.pagehead,.no-print,nav,header,.mobile-bottom{display:none !important}
    .wrap,.content,main{padding:0 !important;margin:0 !important;max-width:none !important;background:#fff !important}
    .receipt-shell{margin:0 auto !important;padding:0 !important}
    .receipt-box{box-shadow:none !important;border:0 !important;border-radius:0 !important;width:80mm !important;max-width:80mm !important;margin:0 auto !important}
    .receipt-inner{padding:10px !important}
}
.receipt-shell{display:grid;place-items:start center}
.receipt-box{width:min(440px,100%);background:#fff;border:1px solid #e4eaf3;border-radius:26px;box-shadow:0 18px 44px rgba(16,24,40,.08);overflow:hidden}
.receipt-top{background:linear-gradient(135deg,#1d4ed8,#0891b2);color:#fff;padding:20px;text-align:center}
.receipt-logo{height:48px;max-width:150px;object-fit:contain;margin-bottom:9px;filter:drop-shadow(0 8px 14px rgba(0,0,0,.18))}
.receipt-top h2{margin:0;font-size:22px;letter-spacing:-.06em}
.receipt-top p{margin:5px 0 0;color:#e0f2fe;font-size:12px;font-weight:800}
.receipt-inner{padding:20px}
.receipt-title{text-align:center;border-bottom:1px dashed #cbd5e1;padding-bottom:14px;margin-bottom:14px}
.receipt-title b{display:block;color:#101828;font-size:15px;letter-spacing:-.03em}
.receipt-title span{display:block;margin-top:4px;color:#667085;font-size:12px}
.receipt-row{display:flex;justify-content:space-between;gap:14px;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:14px}
.receipt-row span:first-child{color:#667085}
.receipt-row span:last-child{text-align:right;color:#101828;font-weight:800}
.receipt-total{margin-top:14px;padding:15px;border-radius:18px;background:#101828;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:12px}
.receipt-total span{color:#d0d5dd;font-size:13px}
.receipt-total b{font-size:22px;letter-spacing:-.04em}
.receipt-foot{text-align:center;margin-top:16px;color:#667085;font-size:12px;line-height:1.45;border-top:1px dashed #cbd5e1;padding-top:12px}
.receipt-status{display:inline-flex;align-items:center;justify-content:center;min-height:28px;padding:0 12px;border-radius:999px;background:#ecfdf3;color:#027a48;font-weight:950;font-size:12px;margin-top:10px}
@media(max-width:760px){
    .receipt-box{border-radius:22px}
    .receipt-top{padding:18px}
    .receipt-inner{padding:16px}
}
</style>

<div class="pagehead no-print">
    <div>
        <h1>Nota Pembayaran</h1>
        <p>Bukti pembayaran menggunakan data Pengaturan Umum.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ $historyUrl }}">{!! $i('history') !!}Riwayat</a>
        <a class="btn light" href="{{ $tagihanUrl }}">{!! $i('back') !!}Tagihan</a>
        @if($waNumber)
            <a class="btn light" target="_blank" href="https://wa.me/{{ $waNumber }}?text={{ $waText }}">{!! $i('wa') !!}Kirim WA</a>
        @endif
        <button class="btn" onclick="window.print()">{!! $i('print') !!}Cetak Nota</button>
    </div>
</div>

@if(session('success'))<div class="alert ok no-print">{{ session('success') }}</div>@endif

<div class="receipt-shell">
    <div class="receipt-box">
        <div class="receipt-top">
            @if($businessLogo)
                <img class="receipt-logo" src="{{ $businessLogo }}" alt="Logo">
            @endif
            <h2>{{ $settings['business_name'] ?? 'MAC-SERVICE' }}</h2>
            <p>{{ $settings['landing_title'] ?? 'Sistem Billing RT/RW.NET' }}</p>
            <div class="receipt-status">PEMBAYARAN DITERIMA</div>
        </div>

        <div class="receipt-inner">
            <div class="receipt-title">
                <b>NOTA PEMBAYARAN</b>
                <span>{{ $fmtDate($payment->paid_at) }}</span>
            </div>

            <div class="receipt-row"><span>No. Nota</span><span>{{ $receiptNumber }}</span></div>
            <div class="receipt-row"><span>Invoice</span><span>{{ $invoiceNumber }}</span></div>
            <div class="receipt-row"><span>Periode</span><span>{{ $invoice?->period ?: '-' }}</span></div>
            <div class="receipt-row"><span>Pelanggan</span><span>{{ $customer?->name ?: '-' }}</span></div>
            <div class="receipt-row"><span>No HP</span><span>{{ $customer?->phone ?: '-' }}</span></div>
            <div class="receipt-row"><span>Paket</span><span>{{ $package?->name ?: '-' }}</span></div>
            <div class="receipt-row">
                <span>ODP / Port</span>
                <span>
                    {{ $customer?->odpMaster?->name ?: ($customer?->odp ?: '-') }}
                    {{ $customer?->port_number ? ' / Port '.$customer?->port_number : '' }}
                </span>
            </div>
            <div class="receipt-row"><span>Metode</span><span>{{ $payment->method ?: ($settings['default_payment_method'] ?? '-') }}</span></div>
            <div class="receipt-row"><span>Kasir</span><span>{{ $collectorName }}</span></div>

            @if($payment->notes)
                <div class="receipt-row"><span>Catatan</span><span>{{ $payment->notes }}</span></div>
            @endif

            <div class="receipt-total">
                <span>Total Bayar</span>
                <b>{{ $money($payment->amount) }}</b>
            </div>

            <div class="receipt-foot">
                {{ $settings['receipt_footer'] ?? 'Terima kasih atas pembayaran Anda.' }}<br>
                @if(!empty($settings['business_phone']))
                    Kontak: {{ $settings['business_phone'] }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
