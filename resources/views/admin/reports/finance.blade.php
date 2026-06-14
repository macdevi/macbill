@extends('layouts.neo')
@section('title','Laporan Keuangan')
@section('content')
@php
    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $statusCount = fn($name) => (int) optional($invoiceStats->get($name))->count_data;
    $statusAmount = fn($name) => (float) optional($invoiceStats->get($name))->total_amount;

    $exportUrl = url('/admin/reports/finance/export') . '?' . http_build_query([
        'from' => $fromValue,
        'to' => $toValue,
    ]);
@endphp

<div class="pagehead">
    <div>
        <h1>Laporan Keuangan</h1>
        <p>Rekap pembayaran, invoice terbuka, dan status tagihan.</p>
    </div>
    <div class="actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">⌂</a>
        <a class="btn light" href="{{ url('/admin/invoices') }}">Invoice</a>
        <a class="btn" href="{{ $exportUrl }}">Export XLSX</a>
    </div>
</div>

<form class="searchbar" method="GET" action="{{ url('/admin/reports/finance') }}">
    <input class="input" type="date" name="from" value="{{ $fromValue }}">
    <input class="input" type="date" name="to" value="{{ $toValue }}">
    <button class="btn" type="submit">Tampilkan</button>
</form>

<div class="grid">
    <div class="card">
        <div class="label">Pembayaran Masuk</div>
        <div class="val">{{ $money($totalPayment) }}</div>
        <div class="muted">{{ $fromValue }} sampai {{ $toValue }}</div>
    </div>

    <div class="card">
        <div class="label">Jumlah Transaksi</div>
        <div class="val">{{ $paymentCount }}</div>
        <div class="muted">Transaksi pembayaran</div>
    </div>

    <div class="card">
        <div class="label">Tagihan Terbuka</div>
        <div class="val">{{ $money($openAmount) }}</div>
        <div class="muted">{{ $openCount }} invoice belum selesai</div>
    </div>

    <div class="card">
        <div class="label">Nunggak</div>
        <div class="val">{{ $money($statusAmount('Nunggak')) }}</div>
        <div class="muted">{{ $statusCount('Nunggak') }} invoice</div>
    </div>
</div>

<div class="grid" style="margin-top:10px">
    <div class="card">
        <div class="label">Belum Bayar</div>
        <div class="val"><span class="badge yellow">{{ $statusCount('Belum Bayar') }}</span></div>
        <div class="muted">{{ $money($statusAmount('Belum Bayar')) }}</div>
    </div>

    <div class="card">
        <div class="label">Bayar Awal</div>
        <div class="val"><span class="badge blue">{{ $statusCount('Bayar Awal') }}</span></div>
        <div class="muted">{{ $money($statusAmount('Bayar Awal')) }}</div>
    </div>

    <div class="card">
        <div class="label">Lunas</div>
        <div class="val"><span class="badge green">{{ $statusCount('Lunas') }}</span></div>
        <div class="muted">{{ $money($statusAmount('Lunas')) }}</div>
    </div>

    <div class="card">
        <div class="label">Nunggak</div>
        <div class="val"><span class="badge red">{{ $statusCount('Nunggak') }}</span></div>
        <div class="muted">{{ $money($statusAmount('Nunggak')) }}</div>
    </div>
</div>

<div class="pagehead" style="margin-top:16px">
    <div>
        <h1>Rekap Metode Pembayaran</h1>
        <p>Total pembayaran berdasarkan metode.</p>
    </div>
</div>

<div class="tablewrap">
    <div class="scroll">
        <div class="table">
            <div class="tr head">
                <div class="td">Metode</div>
                <div class="td">Jumlah Transaksi</div>
                <div class="td">Total</div>
            </div>

            @forelse($methodTotals as $method)
                <div class="tr">
                    <div class="td"><div class="main">{{ $method->method_name }}</div></div>
                    <div class="td">{{ $method->count_data }}</div>
                    <div class="td">{{ $money($method->total_amount) }}</div>
                </div>
            @empty
                <div class="card" style="margin:10px">Belum ada pembayaran pada periode ini.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="pagehead" style="margin-top:16px">
    <div>
        <h1>Riwayat Pembayaran</h1>
        <p>Data pembayaran dalam rentang tanggal yang dipilih.</p>
    </div>
</div>

<div class="tablewrap">
    <div class="scroll">
        <div class="table">
            <div class="tr head">
                <div class="td">Tanggal</div>
                <div class="td">Pelanggan</div>
                <div class="td">Invoice</div>
                <div class="td">ODP / Port</div>
                <div class="td">Metode</div>
                <div class="td">Kasir</div>
                <div class="td">Nominal</div>
                <div class="td">Aksi</div>
            </div>

            @forelse($payments as $payment)
                <div class="tr">
                    <div class="td">{{ optional($payment->paid_at)->format('d/m/Y H:i') }}</div>

                    <div class="td">
                        <div class="main">{{ $payment->customer?->name ?: '-' }}</div>
                        <div class="muted">{{ $payment->customer?->phone ?: '-' }}</div>
                    </div>

                    <div class="td">
                        <div>{{ $payment->invoice?->invoice_number ?: '-' }}</div>
                        <div class="muted">{{ $payment->invoice?->period ?: '-' }}</div>
                    </div>

                    <div class="td">
                        {{ $payment->customer?->odp ?: '-' }}
                        {{ $payment->customer?->port_number ? 'Port '.$payment->customer?->port_number : '' }}
                    </div>

                    <div class="td">{{ $payment->method ?: '-' }}</div>
                    <div class="td">{{ $payment->collector?->username ?? $payment->collector?->name ?? '-' }}</div>
                    <div class="td">{{ $money($payment->amount) }}</div>

                    <div class="td">
                        <a class="btn light" href="{{ url('/admin/payments/'.$payment->id.'/receipt') }}">Nota</a>
                    </div>
                </div>
            @empty
                <div class="card" style="margin:10px">Belum ada pembayaran.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="pagination">{{ $payments->links() }}</div>
@endsection
