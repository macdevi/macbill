@extends('layouts.neo')
@section('title','Pengaturan Mikrotik')
@section('content')
@php
    $i = function ($name) {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>',
            'plus' => '<svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
            'router' => '<svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="8" rx="2"/><path d="M7 15h.01"/><path d="M11 15h.01"/><path d="M15 15h2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',
            'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
            'edit' => '<svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>',
            'trash' => '<svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.($icons[$name] ?? '').'</span>';
    };
@endphp

<style>
.mikrotik-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 14px}
.mikrotik-tabs a{display:inline-flex;align-items:center;min-height:40px;padding:0 14px;border-radius:999px;background:#fff;border:1px solid #e5e7eb;color:#475569;font-size:13px;font-weight:900;box-shadow:0 8px 20px rgba(15,23,42,.04)}
.mikrotik-tabs a.active{background:#0f172a;color:#fff;border-color:#0f172a}
.mikrotik-hero{background:linear-gradient(135deg,#0f172a,#1d4ed8);border-radius:24px;padding:22px;color:#fff;margin-bottom:12px;box-shadow:0 18px 44px rgba(15,23,42,.14)}
.mikrotik-hero span{display:block;color:#bfdbfe;font-size:13px;font-weight:850}
.mikrotik-hero b{display:block;margin-top:6px;font-size:28px;line-height:1.08;letter-spacing:-.055em}
.mikrotik-hero p{margin:10px 0 0;color:#dbeafe;font-size:14px;line-height:1.45;max-width:820px}
.mikrotik-note{background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:18px;padding:13px;font-size:13px;font-weight:800;line-height:1.45;margin-bottom:12px}
.mikrotik-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:10px}
.mikrotik-card{background:#fff;border:1px solid #e5e7eb;border-radius:22px;padding:15px;box-shadow:0 10px 24px rgba(15,23,42,.055)}
.mikrotik-card .label{color:#64748b;font-size:12px;font-weight:850}
.mikrotik-card .value{margin-top:7px;color:#0f172a;font-size:22px;font-weight:950;letter-spacing:-.055em}
.mikrotik-actions{display:flex;gap:6px;align-items:center;flex-wrap:wrap;min-width:210px}
.mikrotik-actions form{margin:0}
.mikrotik-actions .btn{min-height:34px;padding:0 10px;border-radius:12px;font-size:12px;font-weight:850}
.neo-xls{border-radius:22px!important;overflow:hidden!important;border:1px solid #e5e7eb!important;background:#fff!important;box-shadow:0 12px 30px rgba(15,23,42,.055)!important}
.neo-xls-info{background:#f8fafc!important;border-bottom:1px solid #e5e7eb!important}
.neo-xls-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}
.neo-xls-table th{background:#f8fafc!important;color:#475569!important;font-size:12px!important;font-weight:900!important;white-space:nowrap!important}
.neo-xls-table td{vertical-align:middle!important}
@media(max-width:980px){.mikrotik-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:760px){
    .mikrotik-tabs{overflow-x:auto;flex-wrap:nowrap;padding-bottom:3px}
    .mikrotik-tabs a{flex:0 0 auto}
    .mikrotik-hero{border-radius:20px;padding:18px}
    .mikrotik-hero b{font-size:23px}
    .mikrotik-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .mikrotik-card{border-radius:17px;padding:13px}
    .mikrotik-card .value{font-size:20px}
    .neo-xls-table{min-width:760px}
}
</style>

<div class="pagehead">
    <div>
        <h1>Pengaturan Mikrotik</h1>
        <p>Kelola router Mikrotik. Data PPPoE Profile, PPPoE Secret, dan Active Session sudah dipindahkan ke sub menu.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">{!! $i('home') !!}Dashboard</a>
        <a class="btn" href="{{ url('/admin/settings/mikrotik/create') }}">{!! $i('plus') !!}Tambah Mikrotik</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="mikrotik-tabs">
    <a class="{{ request()->is('admin/settings/mikrotik') ? 'active' : '' }}" href="{{ url('/admin/settings/mikrotik') }}">Router</a>
    <a class="{{ request()->is('admin/settings/mikrotik/profiles') ? 'active' : '' }}" href="{{ url('/admin/settings/mikrotik/profiles') }}">PPPoE Profile</a>
    <a class="{{ request()->is('admin/settings/mikrotik/secrets') ? 'active' : '' }}" href="{{ url('/admin/settings/mikrotik/secrets') }}">PPPoE Secret</a>
    <a class="{{ request()->is('admin/settings/mikrotik/active-sessions') ? 'active' : '' }}" href="{{ url('/admin/settings/mikrotik/active-sessions') }}">Active Session</a>
</div>

<div class="mikrotik-hero">
    <span>Router Integration</span>
    <b>Konfigurasi Router Mikrotik</b>
    <p>Halaman ini khusus untuk menambah, mengubah, menghapus, dan test koneksi router. Tabel hasil sync dipindahkan ke tab PPPoE agar halaman utama tetap ringan.</p>
</div>

<div class="mikrotik-note">
    Data PPPoE sudah dipisah ke tab: PPPoE Profile, PPPoE Secret, dan Active Session.
</div>

<form class="neo-search" method="GET" action="{{ url('/admin/settings/mikrotik') }}">
    <span>{!! $i('search') !!}</span>
    <input name="search" value="{{ $search }}" placeholder="Cari nama koneksi, host, atau username">
    <button class="btn" type="submit">Cari</button>
    @if($search)
        <a class="btn light" href="{{ url('/admin/settings/mikrotik') }}">Reset</a>
    @endif
</form>

<div class="mikrotik-grid">
    <div class="mikrotik-card">
        <div class="label">Total Router</div>
        <div class="value">{{ $routers->total() }}</div>
    </div>
    <div class="mikrotik-card">
        <div class="label">Aktif</div>
        <div class="value">{{ \App\Models\MikrotikRouter::where('status','active')->count() }}</div>
    </div>
    <div class="mikrotik-card">
        <div class="label">Nonaktif</div>
        <div class="value">{{ \App\Models\MikrotikRouter::where('status','inactive')->count() }}</div>
    </div>
    <div class="mikrotik-card">
        <div class="label">PPPoE Data</div>
        <div class="value">{{ \App\Models\MikrotikPppoeProfile::count() + \App\Models\MikrotikPppoeSecret::count() + \App\Models\MikrotikPppoeActiveSession::count() }}</div>
    </div>
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total halaman ini: <b>{{ $routers->count() }}</b></span>
        <span>Geser kanan untuk aksi</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th class="sticky-left">ID</th>
                    <th>{!! $i('router') !!}Nama</th>
                    <th>Host / IP</th>
                    <th>Port API</th>
                    <th>Username</th>
                    <th>SSL</th>
                    <th>Status</th>
                    <th>Test Terakhir</th>
                    <th>Profile</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($routers as $router)
                    <tr>
                        <td class="neo-id sticky-left">#{{ $router->id }}</td>
                        <td class="neo-strong">{{ $router->name }}</td>
                        <td>{{ $router->host }}</td>
                        <td>{{ $router->api_port }}</td>
                        <td>{{ $router->username }}</td>
                        <td>
                            <span class="badge {{ $router->use_ssl ? 'green' : 'yellow' }}">
                                {{ $router->use_ssl ? 'SSL' : 'Non SSL' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $router->status === 'active' ? 'green' : 'red' }}">
                                {{ $router->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            @if($router->last_test_status)
                                <span class="badge {{ $router->last_test_status === 'success' ? 'green' : 'red' }}">
                                    {{ $router->last_test_status === 'success' ? 'Sukses' : 'Gagal' }}
                                </span>
                                <span class="muted">{{ $router->last_test_at?->format('d/m/Y H:i') }}</span>
                            @else
                                <span class="badge yellow">Belum Test</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge blue">{{ $router->pppoeProfiles()->count() }} profile</span>
                        </td>
                        <td class="neo-clip">{{ $router->notes ?: '-' }}</td>
                        <td>
                            <div class="mikrotik-actions">
                                <form method="POST" action="{{ url('/admin/settings/mikrotik/'.$router->id.'/test') }}" onsubmit="return confirm('Test koneksi ke Mikrotik ini?')">
                                    @csrf
                                    <button class="btn light" type="submit">Test</button>
                                </form>

                                

                                

                                

                                <a class="btn light icon" title="Edit" href="{{ url('/admin/settings/mikrotik/'.$router->id.'/edit') }}">
                                    {!! $i('edit') !!}
                                </a>

                                <form method="POST" action="{{ url('/admin/settings/mikrotik/'.$router->id) }}" onsubmit="return confirm('Hapus konfigurasi Mikrotik ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn red icon" title="Hapus" type="submit">{!! $i('trash') !!}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">Belum ada konfigurasi Mikrotik.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">{{ $routers->links() }}</div>
@endsection
