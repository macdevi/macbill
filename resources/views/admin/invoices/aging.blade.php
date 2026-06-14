@extends('layouts.neo')
@section('title','Aging Tagihan')
@section('content')
@php
    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $bucketLabels = [
        '' => 'Semua',
        'not_due' => 'Belum Jatuh Tempo',
        'today' => 'Jatuh Tempo Hari Ini',
        '1_30' => 'Lewat 1–30 Hari',
        '31_60' => 'Lewat 31–60 Hari',
        'over_60' => 'Lewat >60 Hari',
    ];
@endphp

<style>
.aging-hero{
    background:linear-gradient(135deg,#0f172a,#1d4ed8);
    color:#fff;
    border-radius:24px;
    padding:20px;
    margin-bottom:12px;
    box-shadow:0 16px 36px rgba(16,24,40,.08);
}
.aging-hero span{display:block;color:#dbeafe;font-size:13px;font-weight:850}
.aging-hero b{display:block;margin-top:6px;font-size:28px;line-height:1;letter-spacing:-.07em}
.aging-hero p{margin:10px 0 0;color:#dbeafe;font-size:14px;line-height:1.45;max-width:920px}
.aging-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin-bottom:12px}
.aging-card{background:#fff;border:1px solid #e4eaf3;border-radius:22px;padding:15px;box-shadow:0 10px 24px rgba(16,24,40,.05)}
.aging-card .label{color:#667085;font-size:12px;font-weight:850}
.aging-card .value{margin-top:7px;color:#101828;font-size:22px;font-weight:950;letter-spacing:-.055em}
.aging-card .sub{margin-top:4px;color:#667085;font-size:12px;font-weight:750}
.aging-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.aging-tabs a{display:inline-flex;text-decoration:none;border:1px solid #e4eaf3;background:#fff;color:#344054;border-radius:999px;padding:9px 12px;font-size:12px;font-weight:850}
.aging-tabs a.active{background:#0f172a;color:#fff;border-color:#0f172a}
@media(max-width:1180px){.aging-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:760px){.aging-grid{grid-template-columns:1fr}}
</style>

<div class="pagehead">
    <div>
        <h1>Aging Tagihan</h1>
        <p>Daftar tagihan terbuka berdasarkan umur jatuh tempo.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/invoices') }}">Daftar Tagihan</a>
        <a class="btn light" href="{{ url('/admin/invoices/schedule') }}">Jadwal Auto Billing</a>
        <a class="btn light" href="{{ url('/admin/invoices/grouped-unpaid') }}">Group Belum Bayar</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="aging-hero">
    <span>Kontrol Piutang</span>
    <b>Aging Tagihan Terbuka</b>
    <p>Halaman ini membantu melihat umur tagihan yang belum dibayar. Data disinkronkan dulu sebelum tampil agar status Belum Bayar dan Nunggak lebih konsisten.</p>
</div>

<div class="aging-grid">
    <div class="aging-card">
        <div class="label">Total Terbuka</div>
        <div class="value">{{ number_format($stats['total_count'],0,',','.') }}</div>
        <div class="sub">{{ $money($stats['total_amount']) }}</div>
    </div>

    <div class="aging-card">
        <div class="label">Belum Jatuh Tempo</div>
        <div class="value">{{ number_format($stats['not_due_count'],0,',','.') }}</div>
        <div class="sub">{{ $money($stats['not_due_amount']) }}</div>
    </div>

    <div class="aging-card">
        <div class="label">Hari Ini</div>
        <div class="value">{{ number_format($stats['today_count'],0,',','.') }}</div>
        <div class="sub">{{ $money($stats['today_amount']) }}</div>
    </div>

    <div class="aging-card">
        <div class="label">Lewat 1–30 Hari</div>
        <div class="value">{{ number_format($stats['1_30_count'],0,',','.') }}</div>
        <div class="sub">{{ $money($stats['1_30_amount']) }}</div>
    </div>

    <div class="aging-card">
        <div class="label">Lewat 31–60 Hari</div>
        <div class="value">{{ number_format($stats['31_60_count'],0,',','.') }}</div>
        <div class="sub">{{ $money($stats['31_60_amount']) }}</div>
    </div>

    <div class="aging-card">
        <div class="label">Lewat >60 Hari</div>
        <div class="value">{{ number_format($stats['over_60_count'],0,',','.') }}</div>
        <div class="sub">{{ $money($stats['over_60_amount']) }}</div>
    </div>

    <div class="aging-card">
        <div class="label">Status Belum Bayar</div>
        <div class="value">{{ number_format($stats['belum_bayar_count'],0,',','.') }}</div>
        <div class="sub">Invoice terbuka</div>
    </div>

    <div class="aging-card">
        <div class="label">Status Nunggak</div>
        <div class="value">{{ number_format($stats['nunggak_count'],0,',','.') }}</div>
        <div class="sub">Invoice terlambat</div>
    </div>
</div>

<form class="neo-search" method="GET" action="{{ url('/admin/invoices/aging') }}">
    <input class="input" type="text" name="q" value="{{ $q }}" placeholder="Cari pelanggan, invoice, telepon, ODP, alamat...">

    <select class="select" name="period">
        <option value="">Semua Periode</option>
        @foreach($periods as $p)
            <option value="{{ $p }}" @selected($period === $p)>{{ $p }}</option>
        @endforeach
    </select>

    <select class="select" name="status">
        <option value="">Semua Status</option>
        <option value="Belum Bayar" @selected($status === 'Belum Bayar')>Belum Bayar</option>
        <option value="Nunggak" @selected($status === 'Nunggak')>Nunggak</option>
    </select>

    <select class="select" name="bucket">
        @foreach($bucketLabels as $key => $label)
            <option value="{{ $key }}" @selected($bucket === $key)>{{ $label }}</option>
        @endforeach
    </select>

    <button class="btn" type="submit">Filter</button>
</form>

<div class="aging-tabs">
    @foreach($bucketLabels as $key => $label)
        <a class="{{ $bucket === $key ? 'active' : '' }}" href="{{ url('/admin/invoices/aging?bucket='.$key.'&period='.$period.'&status='.$status.'&q='.urlencode($q)) }}">{{ $label }}</a>
    @endforeach
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Data tampil: <b>{{ $rows->count() }}</b></span>
        <span>Total: <b>{{ $money($rows->sum('remaining_amount')) }}</b></span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th class="sticky-left">Invoice</th>
                    <th>Pelanggan</th>
                    <th>Periode</th>
                    <th>Due Date</th>
                    <th>Umur</th>
                    <th>Status</th>
                    <th>Paket</th>
                    <th>ODP / Port</th>
                    <th>Nominal</th>
                    <th>Terbayar</th>
                    <th>Sisa</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $invoice)
                    <tr>
                        <td class="neo-id sticky-left">{{ $invoice->invoice_number }}</td>
                        <td class="neo-strong">
                            {{ $invoice->customer?->name ?: '-' }}
                            @if($invoice->customer?->phone)
                                <div class="small">{{ $invoice->customer->phone }}</div>
                            @endif
                        </td>
                        <td>{{ $invoice->period }}</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge {{ $invoice->aging_class }}">{{ $invoice->aging_label }}</span>
                            <div class="small">
                                @if($invoice->aging_days < 0)
                                    {{ abs((int)$invoice->aging_days) }} hari lagi
                                @elseif($invoice->aging_days == 0)
                                    Hari ini
                                @else
                                    Lewat {{ (int)$invoice->aging_days }} hari
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $invoice->status === 'Nunggak' ? 'red' : 'yellow' }}">{{ $invoice->status }}</span>
                        </td>
                        <td>{{ $invoice->package?->name ?: ($invoice->customer?->package?->name ?: '-') }}</td>
                        <td>
                            {{ $invoice->customer?->odp ?: ($invoice->customer?->odpMaster?->name ?: '-') }}
                            {{ $invoice->customer?->port_number ? ' · Port '.$invoice->customer->port_number : '' }}
                        </td>
                        <td class="neo-money">{{ $money($invoice->amount) }}</td>
                        <td class="neo-money">{{ $money($invoice->paid_amount) }}</td>
                        <td class="neo-money">{{ $money($invoice->remaining_amount) }}</td>
                        <td>
                            <div class="neo-row-actions">
                                <a class="btn light" href="{{ url('/admin/invoices/'.$invoice->id.'/detail') }}">Detail</a>
                                @if($invoice->customer)
                                    <a class="btn light" href="{{ url('/admin/customers/'.$invoice->customer->id.'/detail') }}">Pelanggan</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12">Tidak ada tagihan terbuka pada filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
