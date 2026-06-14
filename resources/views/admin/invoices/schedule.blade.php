@extends('layouts.neo')
@section('title','Jadwal Auto Billing')
@section('content')
@php
    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $badge = fn($s) => match($s) {
        'green' => 'green',
        'red' => 'red',
        'yellow' => 'yellow',
        default => 'blue',
    };

    $bucketLabel = [
        '' => 'Semua',
        'today' => 'Jatuh Tempo Hari Ini',
        'upcoming' => 'Akan Datang',
        'overdue' => 'Terlewat / Akan Dikejar Auto Billing',
        'existing' => 'Sudah Ada Invoice',
        'free' => 'Gratis',
        'inactive' => 'Nonaktif',
        'no_price' => 'Nominal Kosong',
    ];
@endphp

<style>
.schedule-hero{
    background:linear-gradient(135deg,#0f172a,#1d4ed8);
    color:#fff;
    border-radius:24px;
    padding:20px;
    margin-bottom:12px;
    box-shadow:0 16px 36px rgba(16,24,40,.08);
}
.schedule-hero span{display:block;color:#dbeafe;font-size:13px;font-weight:850}
.schedule-hero b{display:block;margin-top:6px;font-size:28px;line-height:1;letter-spacing:-.07em}
.schedule-hero p{margin:10px 0 0;color:#dbeafe;font-size:14px;line-height:1.45;max-width:920px}
.schedule-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:12px}
.schedule-card{background:#fff;border:1px solid #e4eaf3;border-radius:22px;padding:15px;box-shadow:0 10px 24px rgba(16,24,40,.05)}
.schedule-card .label{color:#667085;font-size:12px;font-weight:850}
.schedule-card .value{margin-top:7px;color:#101828;font-size:22px;font-weight:950;letter-spacing:-.055em}
.schedule-card .sub{margin-top:4px;color:#667085;font-size:12px;font-weight:750}
.schedule-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.schedule-tabs a{display:inline-flex;align-items:center;gap:6px;text-decoration:none;border:1px solid #e4eaf3;background:#fff;color:#344054;border-radius:999px;padding:9px 12px;font-size:12px;font-weight:850}
.schedule-tabs a.active{background:#0f172a;color:#fff;border-color:#0f172a}
.schedule-note{background:#fffaeb;border:1px solid #fedf89;color:#93370d;border-radius:18px;padding:12px;margin-bottom:12px;font-size:13px;font-weight:800;line-height:1.45}
@media(max-width:980px){.schedule-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.schedule-grid{grid-template-columns:1fr}}
</style>

<div class="pagehead">
    <div>
        <h1>Jadwal Auto Billing</h1>
        <p>Melihat pelanggan yang akan diproses otomatis berdasarkan billing day.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/invoices') }}">Daftar Tagihan</a>
        <a class="btn light" href="{{ url('/admin/invoices/preview?period='.$period) }}">Cek & Generate Manual</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="schedule-hero">
    <span>Auto Billing</span>
    <b>Tagihan dibuat otomatis sesuai billing day</b>
    <p>Halaman ini tidak membuat invoice. Auto billing tetap dijalankan oleh cron. Jika due date pelanggan sudah tiba atau terlewat dan invoice periode ini belum ada, sistem akan membuat invoice otomatis. Jadi jika cron sempat mati, tagihan tetap bisa dikejar pada proses berikutnya.</p>
</div>

<div class="schedule-grid">
    <div class="schedule-card">
        <div class="label">Jatuh Tempo Hari Ini</div>
        <div class="value">{{ number_format($stats['today'],0,',','.') }}</div>
        <div class="sub">{{ $money($stats['today_amount']) }}</div>
    </div>

    <div class="schedule-card">
        <div class="label">Akan Datang</div>
        <div class="value">{{ number_format($stats['upcoming'],0,',','.') }}</div>
        <div class="sub">{{ $money($stats['upcoming_amount']) }}</div>
    </div>

    <div class="schedule-card">
        <div class="label">Sudah Ada Invoice</div>
        <div class="value">{{ number_format($stats['existing'],0,',','.') }}</div>
        <div class="sub">Periode {{ $period }}</div>
    </div>

    <div class="schedule-card">
        <div class="label">Gratis / Tidak Ditagih</div>
        <div class="value">{{ number_format($stats['free'],0,',','.') }}</div>
        <div class="sub">Nominal 0</div>
    </div>

    <div class="schedule-card">
        <div class="label">Terlewat / Akan Dikejar Auto Billing</div>
        <div class="value">{{ number_format($stats['overdue'],0,',','.') }}</div>
        <div class="sub">Perlu dicek manual</div>
    </div>

    <div class="schedule-card">
        <div class="label">Nominal Kosong</div>
        <div class="value">{{ number_format($stats['no_price'],0,',','.') }}</div>
        <div class="sub">Tidak dibuat invoice</div>
    </div>

    <div class="schedule-card">
        <div class="label">Nonaktif</div>
        <div class="value">{{ number_format($stats['inactive'],0,',','.') }}</div>
        <div class="sub">Tidak ikut billing</div>
    </div>

    <div class="schedule-card">
        <div class="label">Total Data</div>
        <div class="value">{{ number_format($stats['total'],0,',','.') }}</div>
        <div class="sub">Pelanggan terbaca</div>
    </div>
</div>

@if($stats['overdue'] > 0)
    <div class="schedule-note">
        Ada pelanggan yang billing day-nya sudah lewat tetapi invoice periode {{ $period }} belum ada. Dengan aturan baru, auto billing berikutnya akan mencoba membuat invoice ini secara otomatis selama pelanggan aktif dan nominalnya valid.
    </div>
@endif

<form class="neo-search" method="GET" action="{{ url('/admin/invoices/schedule') }}">
    <input class="input" type="month" name="period" value="{{ $period }}">
    <input class="input" type="text" name="q" value="{{ $q }}" placeholder="Cari nama, telepon, alamat, ODP, PPPoE...">

    <select class="select" name="bucket">
        @foreach($bucketLabel as $key => $label)
            <option value="{{ $key }}" @selected($bucket === $key)>{{ $label }}</option>
        @endforeach
    </select>

    <button class="btn" type="submit">Filter</button>
</form>

<div class="schedule-tabs">
    @foreach($bucketLabel as $key => $label)
        <a class="{{ $bucket === $key ? 'active' : '' }}" href="{{ url('/admin/invoices/schedule?period='.$period.'&bucket='.$key.'&q='.urlencode($q)) }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Data tampil: <b>{{ $rows->count() }}</b></span>
        <span>Periode: <b>{{ $period }}</b></span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th class="sticky-left">ID</th>
                    <th>Pelanggan</th>
                    <th>Paket</th>
                    <th>ODP / Port</th>
                    <th>Billing Day</th>
                    <th>Due Date</th>
                    <th>Selisih</th>
                    <th>Nominal</th>
                    <th>Status Jadwal</th>
                    <th>Invoice</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="neo-id sticky-left">#{{ $row->customer->id }}</td>
                        <td class="neo-strong">
                            {{ $row->customer->name }}
                            @if($row->customer->phone)
                                <div class="small">{{ $row->customer->phone }}</div>
                            @endif
                        </td>
                        <td>{{ $row->customer->package?->name ?: '-' }}</td>
                        <td>
                            {{ $row->customer->odp ?: ($row->customer->odpMaster?->name ?: '-') }}
                            {{ $row->customer->port_number ? ' · Port '.$row->customer->port_number : '' }}
                        </td>
                        <td>{{ $row->billing_day }}</td>
                        <td>{{ $row->due_date->format('d/m/Y') }}</td>
                        <td>
                            @if($row->days_left == 0)
                                Hari ini
                            @elseif($row->days_left > 0)
                                {{ (int) $row->days_left }} hari lagi
                            @else
                                Lewat {{ abs((int) $row->days_left) }} hari
                            @endif
                        </td>
                        <td class="neo-money">{{ $money($row->amount) }}</td>
                        <td><span class="badge {{ $row->state_class }}">{{ $row->state }}</span></td>
                        <td>
                            @if($row->existing)
                                <a href="{{ url('/admin/invoices/'.$row->existing->id.'/detail') }}">{{ $row->existing->invoice_number }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div class="neo-row-actions">
                                <a class="btn light" href="{{ url('/admin/customers/'.$row->customer->id.'/edit') }}">Edit</a>
                                <a class="btn light" href="{{ url('/admin/customers/'.$row->customer->id.'/detail') }}">Detail</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11">Tidak ada data pada filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
