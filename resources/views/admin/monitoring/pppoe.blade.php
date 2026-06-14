@extends('layouts.neo')
@section('title','Monitoring PPPoE')
@section('content')
@php
    $i = function ($name) {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>',
            'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
            'wifi' => '<svg viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>',
            'refresh' => '<svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 1-15.5 6.3L3 16"/><path d="M3 16v5h5"/><path d="M3 12A9 9 0 0 1 18.5 5.7L21 8"/><path d="M21 8V3h-5"/></svg>',
            'eye' => '<svg viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };

    $statusClass = function ($value) {
        return $value === 'Online' ? 'green' : ($value === 'Offline' ? 'red' : 'yellow');
    };
@endphp

<style>
.pppoe-hero{
    background:linear-gradient(135deg,#0f172a,#1d4ed8);
    border-radius:24px;
    padding:22px;
    color:#fff;
    margin-bottom:12px;
    box-shadow:0 16px 36px rgba(16,24,40,.08);
}
.pppoe-hero span{display:block;color:#dbeafe;font-size:13px;font-weight:800}
.pppoe-hero b{display:block;margin-top:6px;font-size:28px;line-height:1;letter-spacing:-.07em}
.pppoe-hero p{margin:10px 0 0;color:#dbeafe;font-size:14px;line-height:1.45;max-width:780px}
.pppoe-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:10px}
.pppoe-card{background:#fff;border:1px solid #e4eaf3;border-radius:22px;padding:15px;box-shadow:0 10px 24px rgba(16,24,40,.055)}
.pppoe-card .label{color:#667085;font-size:12px;font-weight:800}
.pppoe-card .value{margin-top:7px;color:#101828;font-size:24px;font-weight:950;letter-spacing:-.055em}
.pppoe-router-actions{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0}
.pppoe-router-actions form{margin:0}
.pppoe-filter{
    display:grid;
    grid-template-columns:1.6fr .7fr .9fr auto auto;
    gap:8px;
    align-items:center;
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:22px;
    padding:10px;
    margin-bottom:10px;
    box-shadow:0 10px 24px rgba(16,24,40,.045);
}
.pppoe-filter input,
.pppoe-filter select{
    width:100%;
    border:1px solid #d9e3f0;
    border-radius:16px;
    padding:12px 13px;
    font-weight:800;
    color:#101828;
    background:#fff;
}
.pppoe-muted{display:block;color:#667085;font-size:12px;margin-top:3px}
.pppoe-status-pill{display:inline-flex;align-items:center;gap:7px}
.pppoe-dot{width:9px;height:9px;border-radius:999px;background:#98a2b3}
.pppoe-dot.online{background:#12b76a}
.pppoe-dot.offline{background:#f04438}
.pppoe-dot.unknown{background:#f79009}
@media(max-width:980px){
    .pppoe-cards{grid-template-columns:repeat(2,minmax(0,1fr))}
    .pppoe-filter{grid-template-columns:1fr 1fr}
}
@media(max-width:760px){
    .pppoe-hero{border-radius:22px;padding:18px}
    .pppoe-hero b{font-size:25px}
    .pppoe-cards{gap:8px}
    .pppoe-card{border-radius:18px;padding:12px}
    .pppoe-card .value{font-size:20px}
    .pppoe-filter{grid-template-columns:1fr}
    .pppoe-filter .btn{width:100%;justify-content:center}
}
</style>

<div class="pagehead">
    <div>
        <h1>Monitoring PPPoE</h1>
        <p>Pantau status online/offline pelanggan berdasarkan data PPP Active dari Mikrotik.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">{!! $i('home') !!}Dashboard</a>
        <a class="btn light" href="{{ url('/admin/settings/mikrotik') }}">{!! $i('wifi') !!}Mikrotik</a>
        <a class="btn light" href="{{ url('/admin/monitoring/pppoe/reconcile') }}">Rekonsiliasi PPPoE</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="pppoe-hero">
    <span>Network Monitoring</span>
    <b>Status PPPoE Pelanggan</b>
    <p>Halaman ini hanya membaca data active session. Tidak ada isolir otomatis dan tidak mengubah Secret di Mikrotik.</p>
</div>

<div class="pppoe-cards">
    <div class="pppoe-card">
        <div class="label">Total PPPoE</div>
        <div class="value">{{ number_format($totalPppoe,0,',','.') }}</div>
    </div>

    <div class="pppoe-card">
        <div class="label">Online</div>
        <div class="value">{{ number_format($online,0,',','.') }}</div>
    </div>

    <div class="pppoe-card">
        <div class="label">Offline</div>
        <div class="value">{{ number_format($offline,0,',','.') }}</div>
    </div>

    <div class="pppoe-card">
        <div class="label">Unknown</div>
        <div class="value">{{ number_format($unknown,0,',','.') }}</div>
    </div>
</div>

<div class="pppoe-router-actions">
    @forelse($routers as $router)
        <form method="POST" action="{{ url('/admin/settings/mikrotik/'.$router->id.'/sync-active') }}" onsubmit="return confirm('Refresh PPP Active dari {{ $router->name }}?')">
            @csrf
            <button class="btn light" type="submit">{!! $i('refresh') !!}Refresh {{ $router->name }}</button>
        </form>
    @empty
        <a class="btn light" href="{{ url('/admin/settings/mikrotik/create') }}">Tambah Router Mikrotik</a>
    @endforelse
</div>

<form class="pppoe-filter" method="GET" action="{{ url('/admin/monitoring/pppoe') }}">
    <input name="search" value="{{ $search }}" placeholder="Cari nama, username, IP, caller ID">

    <select name="status">
        <option value="">Semua Status</option>
        <option value="Online" @selected($status === 'Online')>Online</option>
        <option value="Offline" @selected($status === 'Offline')>Offline</option>
        <option value="Unknown" @selected($status === 'Unknown')>Unknown</option>
    </select>

    <select name="router_id">
        <option value="">Semua Router</option>
        @foreach($routers as $router)
            <option value="{{ $router->id }}" @selected((string)$routerId === (string)$router->id)>{{ $router->name }}</option>
        @endforeach
    </select>

    <button class="btn" type="submit">{!! $i('search') !!}Cari</button>

    @if($search || $status || $routerId)
        <a class="btn light" href="{{ url('/admin/monitoring/pppoe') }}">Reset</a>
    @endif
</form>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Data tampil: <b>{{ $customers->count() }}</b></span>
        <span>Geser kanan untuk melihat IP, Caller ID, dan aksi</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th class="sticky-left">Status</th>
                    <th>Pelanggan</th>
                    <th>Username PPPoE</th>
                    <th>Router</th>
                    <th>Profile</th>
                    <th>Remote Address</th>
                    <th>Caller ID</th>
                    <th>Uptime</th>
                    <th>Last Seen</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($customers as $customer)
                    @php($onlineStatus = $customer->pppoe_online_status ?: 'Unknown')
                    <tr>
                        <td class="sticky-left">
                            <span class="badge {{ $statusClass($onlineStatus) }}">
                                <span class="pppoe-status-pill">
                                    <span class="pppoe-dot {{ strtolower($onlineStatus) }}"></span>
                                    {{ $onlineStatus }}
                                </span>
                            </span>
                        </td>

                        <td>
                            <span class="neo-strong">{{ $customer->name }}</span>
                            <span class="pppoe-muted">{{ $customer->phone ?: '-' }}</span>
                        </td>

                        <td>{{ $customer->pppoe_username ?: '-' }}</td>
                        <td>{{ $customer->mikrotikRouter?->name ?: '-' }}</td>
                        <td>{{ $customer->mikrotikPppoeProfile?->name ?: ($customer->mikrotikPppoeSecret?->profile ?? '-') }}</td>
                        <td>{{ $customer->pppoe_remote_address ?: '-' }}</td>
                        <td>{{ $customer->pppoe_caller_id ?: '-' }}</td>
                        <td>{{ $customer->pppoe_uptime ?: '-' }}</td>
                        <td>{{ $customer->pppoe_last_seen_at ? $customer->pppoe_last_seen_at->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            <a class="btn light icon" title="Detail" href="{{ url('/admin/customers/'.$customer->id.'/detail') }}">
                                {!! $i('eye') !!}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">Belum ada pelanggan PPPoE sesuai filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">{{ $customers->links() }}</div>
@endsection
