@extends('layouts.neo')
@section('title','Status Sistem')
@section('content')
@php
    $badge = fn ($ok) => $ok ? 'green' : 'red';

    $dateFmt = function ($value) {
        return $value ? \Carbon\Carbon::parse($value)->format('d/m/Y H:i') : '-';
    };
@endphp

<style>
.health-hero{
    background:linear-gradient(135deg,#0f172a,#1d4ed8);
    border-radius:24px;
    padding:22px;
    color:#fff;
    margin-bottom:12px;
    box-shadow:0 16px 36px rgba(16,24,40,.08);
}
.health-hero span{display:block;color:#dbeafe;font-size:13px;font-weight:800}
.health-hero b{display:block;margin-top:6px;font-size:28px;line-height:1;letter-spacing:-.07em}
.health-hero p{margin:10px 0 0;color:#dbeafe;font-size:14px;line-height:1.45;max-width:880px}
.health-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:12px}
.health-card{background:#fff;border:1px solid #e4eaf3;border-radius:22px;padding:15px;box-shadow:0 10px 24px rgba(16,24,40,.055)}
.health-card .label{color:#667085;font-size:12px;font-weight:850}
.health-card .value{margin-top:7px;color:#101828;font-size:22px;font-weight:950;letter-spacing:-.055em}
.health-section{background:#fff;border:1px solid #e4eaf3;border-radius:24px;padding:16px;margin-bottom:12px;box-shadow:0 10px 24px rgba(16,24,40,.05)}
.health-section-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:12px}
.health-section-head b{display:block;color:#101828;font-size:20px;font-weight:950;letter-spacing:-.055em}
.health-section-head span{display:block;color:#667085;font-size:13px;font-weight:800;margin-top:3px}
.health-row{display:grid;grid-template-columns:230px 1fr;gap:10px;padding:10px 0;border-top:1px solid #eef2f7}
.health-row:first-child{border-top:0}
.health-row .k{color:#667085;font-size:13px;font-weight:850}
.health-row .v{color:#101828;font-size:13px;font-weight:850;word-break:break-word}
.health-log{background:#0f172a;color:#dbeafe;border-radius:18px;padding:12px;max-height:340px;overflow:auto;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;line-height:1.5}
.health-log div{white-space:pre-wrap;border-bottom:1px solid rgba(255,255,255,.08);padding:4px 0}
.health-actions{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.health-actions form{margin:0}
@media(max-width:980px){.health-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:760px){
    .health-grid{grid-template-columns:1fr}
    .health-row{grid-template-columns:1fr;gap:3px}
    .health-actions .btn{width:100%;justify-content:center}
}
</style>

<div class="pagehead">
    <div>
        <h1>Status Sistem</h1>
        <p>Health check aplikasi, database, cron, Mikrotik, invoice, dan log penting.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">Dashboard</a>
        <a class="btn light" href="{{ url('/admin/monitoring/pppoe') }}">Monitoring PPPoE</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

@if(session('command_output'))
    <div class="health-section">
        <div class="health-section-head">
            <div>
                <b>Output Command: {{ session('command_name') }}</b>
                <span>Hasil eksekusi manual dari halaman health.</span>
            </div>
        </div>
        <div class="health-log">
            @foreach(explode("\n", session('command_output')) as $line)
                <div>{{ $line }}</div>
            @endforeach
        </div>
    </div>
@endif

<div class="health-hero">
    <span>System Health</span>
    <b>Status Operasional Sistem</b>
    <p>Halaman ini hanya untuk cek dan menjalankan command manual. Tidak ada backup otomatis dan tidak ada perubahan data kecuali command yang memang dipilih, seperti refresh PPPoE atau auto billing manual.</p>
</div>

<div class="health-actions">
    <form method="POST" action="{{ url('/admin/system/health/refresh-pppoe') }}" onsubmit="return confirm('Jalankan refresh PPPoE Active sekarang?')">
        @csrf
        <button class="btn" type="submit">Refresh PPPoE</button>
    </form>

    <form method="POST" action="{{ url('/admin/system/health/run-billing') }}" onsubmit="return confirm('Jalankan auto billing manual sekarang? Sistem tetap skip invoice yang sudah ada.')">
        @csrf
        <button class="btn light" type="submit">Run Auto Billing</button>
    </form>

    <form method="POST" action="{{ url('/admin/system/health/run-audit') }}">
        @csrf
        <button class="btn light" type="submit">Run Audit Data</button>
    </form>

    <a class="btn light" href="{{ url('/admin/settings/mikrotik') }}">Pengaturan Mikrotik</a>
    <a class="btn light" href="{{ url('/admin/monitoring/pppoe/reconcile') }}">Rekonsiliasi PPPoE</a>
</div>

<div class="health-grid">
    <div class="health-card">
        <div class="label">Database</div>
        <div class="value"><span class="badge {{ $dbStatus['status'] === 'OK' ? 'green' : 'red' }}">{{ $dbStatus['status'] }}</span></div>
    </div>

    <div class="health-card">
        <div class="label">Pelanggan Aktif</div>
        <div class="value">{{ number_format($summary['customers_active'],0,',','.') }}</div>
    </div>

    <div class="health-card">
        <div class="label">Pelanggan Gratis</div>
        <div class="value">{{ number_format($summary['customers_free'],0,',','.') }}</div>
    </div>

    <div class="health-card">
        <div class="label">Tagihan Terbuka</div>
        <div class="value">{{ number_format($summary['invoices_open'],0,',','.') }}</div>
    </div>

    <div class="health-card">
        <div class="label">Bayar Awal</div>
        <div class="value">{{ number_format($summary['invoices_early'],0,',','.') }}</div>
    </div>

    <div class="health-card">
        <div class="label">Lunas</div>
        <div class="value">{{ number_format($summary['invoices_paid'],0,',','.') }}</div>
    </div>

    <div class="health-card">
        <div class="label">Pembayaran Hari Ini</div>
        <div class="value">{{ number_format($summary['payments_today'],0,',','.') }}</div>
    </div>

    <div class="health-card">
        <div class="label">Nominal Hari Ini</div>
        <div class="value" style="font-size:18px">Rp {{ number_format($summary['payments_today_amount'],0,',','.') }}</div>
    </div>

    <div class="health-card">
        <div class="label">Router Aktif</div>
        <div class="value">{{ $summary['routers_active'] }} / {{ $summary['routers_total'] }}</div>
    </div>

    <div class="health-card">
        <div class="label">PPP Active</div>
        <div class="value">{{ number_format($summary['active_sessions'],0,',','.') }}</div>
    </div>

    <div class="health-card">
        <div class="label">Pelanggan Online</div>
        <div class="value">{{ number_format($summary['online_customers'],0,',','.') }}</div>
    </div>

    <div class="health-card">
        <div class="label">Last PPPoE Refresh</div>
        <div class="value" style="font-size:15px">{{ $dateFmt($summary['last_pppoe_seen']) }}</div>
    </div>
</div>

<div class="health-section">
    <div class="health-section-head">
        <div>
            <b>Database & Aplikasi</b>
            <span>Status koneksi database, ukuran database, storage, dan versi sistem.</span>
        </div>
    </div>

    <div class="health-row">
        <div class="k">Database</div>
        <div class="v">{{ $dbStatus['message'] }}</div>
    </div>

    <div class="health-row">
        <div class="k">File Database</div>
        <div class="v">{{ $summary['database_file'] }} · {{ $summary['database_size'] }}</div>
    </div>

    <div class="health-row">
        <div class="k">Storage VPS</div>
        <div class="v">Free {{ $summary['disk_free'] }} dari total {{ $summary['disk_total'] }}</div>
    </div>

    <div class="health-row">
        <div class="k">Aplikasi</div>
        <div class="v">{{ $summary['app_name'] }} · Laravel {{ $summary['laravel'] }} · PHP {{ $summary['php'] }}</div>
    </div>

    <div class="health-row">
        <div class="k">Environment</div>
        <div class="v">{{ $summary['env'] }} · Timezone {{ $summary['timezone'] }} · Now {{ $summary['now'] }}</div>
    </div>
</div>

<div class="health-section">
    <div class="health-section-head">
        <div>
            <b>Cron Job</b>
            <span>Status file cron billing dan PPPoE active refresh.</span>
        </div>
    </div>

    <div class="health-row">
        <div class="k">Cron Auto Billing</div>
        <div class="v">
            <span class="badge {{ $badge($cronBilling['enabled']) }}">{{ $cronBilling['enabled'] ? 'Aktif' : 'Tidak Aktif' }}</span>
            {{ $cronBilling['path'] }}
            @if($cronBilling['modified_at']) · update {{ $cronBilling['modified_at'] }} @endif
        </div>
    </div>

    <div class="health-row">
        <div class="k">Cron PPPoE Active</div>
        <div class="v">
            <span class="badge {{ $badge($cronPppoe['enabled']) }}">{{ $cronPppoe['enabled'] ? 'Aktif' : 'Tidak Aktif' }}</span>
            {{ $cronPppoe['path'] }}
            @if($cronPppoe['modified_at']) · update {{ $cronPppoe['modified_at'] }} @endif
        </div>
    </div>
</div>

<div class="health-section">
    <div class="health-section-head">
        <div>
            <b>Router Mikrotik</b>
            <span>Status test terakhir tiap router.</span>
        </div>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th>Router</th>
                    <th>Host</th>
                    <th>Status</th>
                    <th>Test Terakhir</th>
                    <th>Pesan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routers as $router)
                    <tr>
                        <td class="neo-strong">{{ $router->name }}</td>
                        <td>{{ $router->host }}:{{ $router->api_port }}</td>
                        <td>
                            <span class="badge {{ $router->last_test_status === 'success' ? 'green' : ($router->last_test_status === 'failed' ? 'red' : 'yellow') }}">
                                {{ $router->last_test_status ?: 'Belum Test' }}
                            </span>
                        </td>
                        <td>{{ $router->last_test_at ? $router->last_test_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="neo-clip">{{ $router->last_test_message ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada router Mikrotik.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="health-section">
    <div class="health-section-head">
        <div>
            <b>Log PPPoE Active</b>
            <span>{{ $pppoeLog['exists'] ? 'Update file: '.$pppoeLog['modified_at'] : 'File log belum ada.' }}</span>
        </div>
    </div>

    <div class="health-log">
        @forelse($pppoeLog['lines'] as $line)
            <div>{{ $line }}</div>
        @empty
            <div>Belum ada log PPPoE Active.</div>
        @endforelse
    </div>
</div>

<div class="health-section">
    <div class="health-section-head">
        <div>
            <b>Log Auto Billing</b>
            <span>{{ $billingLog['exists'] ? 'Update file: '.$billingLog['modified_at'] : 'File log belum ada.' }}</span>
        </div>
    </div>

    <div class="health-log">
        @forelse($billingLog['lines'] as $line)
            <div>{{ $line }}</div>
        @empty
            <div>Belum ada log auto billing.</div>
        @endforelse
    </div>
</div>

<div class="health-section">
    <div class="health-section-head">
        <div>
            <b>Error Laravel Terakhir</b>
            <span>{{ $laravelLog['exists'] ? 'Update file: '.$laravelLog['modified_at'] : 'File log belum ada.' }}</span>
        </div>
    </div>

    <div class="health-log">
        @forelse($laravelLog['lines'] as $line)
            <div>{{ $line }}</div>
        @empty
            <div>Tidak ada error penting yang terbaca dari laravel.log.</div>
        @endforelse
    </div>
</div>
@endsection
