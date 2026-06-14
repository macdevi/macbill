@extends('layouts.neo')
@section('title','Dashboard Admin')
@section('content')

<!-- MACSERVICE DASHBOARD ONLY POLISH V1 STYLE START -->
<style>
.dashboard-polish-wrap{
    display:grid;
    gap:14px;
    margin-top:14px;
}

.dash-monitor-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:24px;
    padding:18px;
    box-shadow:0 10px 26px rgba(16,24,40,.055);
    margin-bottom:12px;
}

.dash-monitor-head{
    display:flex;
    justify-content:space-between;
    gap:12px;
    align-items:flex-start;
    margin-bottom:12px;
}

.dash-monitor-head h3{
    margin:0;
    color:#101828;
    font-size:20px;
    font-weight:950;
    letter-spacing:-.055em;
}

.dash-monitor-head p{
    margin:4px 0 0;
    color:#667085;
    font-size:13px;
    font-weight:750;
    line-height:1.45;
}

.dash-monitor-icon{
    width:46px;
    height:46px;
    border-radius:17px;
    display:grid;
    place-items:center;
    color:#1d4ed8;
    background:#eff6ff;
    flex:0 0 auto;
}

.dash-monitor-icon svg{
    width:24px;
    height:24px;
    fill:none;
    stroke:currentColor;
    stroke-width:2.2;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.dash-monitor-box{
    border:1px dashed #cbd5e1;
    background:#f8fbff;
    border-radius:20px;
    padding:14px;
}

.dash-monitor-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:9px;
}

.dash-monitor-stat{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:16px;
    padding:11px;
}

.dash-monitor-stat span{
    display:block;
    color:#667085;
    font-size:12px;
    font-weight:850;
}

.dash-monitor-stat b{
    display:block;
    margin-top:6px;
    color:#101828;
    font-size:21px;
    font-weight:950;
    letter-spacing:-.055em;
}

.dash-monitor-stat.online b{color:#027a48}
.dash-monitor-stat.offline b{color:#b42318}
.dash-monitor-stat.unknown b{color:#b54708}

.dash-monitor-note{
    margin-top:11px;
    color:#667085;
    font-size:13px;
    font-weight:750;
    line-height:1.45;
}

.dash-monitor-actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-top:12px;
}

.dash-traffic-row{
    margin-top:13px;
}

.dash-traffic-line{
    display:flex;
    justify-content:space-between;
    gap:10px;
    align-items:center;
    margin-bottom:7px;
}

.dash-traffic-line span{
    color:#667085;
    font-size:13px;
    font-weight:900;
}

.dash-traffic-line b{
    color:#101828;
    font-size:13px;
    font-weight:950;
}

.dash-traffic-bar{
    height:11px;
    border-radius:999px;
    background:#eef5ff;
    overflow:hidden;
}

.dash-traffic-fill{
    height:100%;
    border-radius:999px;
    background:linear-gradient(90deg,#2563eb,#06b6d4);
}

@media(max-width:980px){
    .dash-monitor-grid{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
}

@media(max-width:760px){
    .dash-monitor-card{
        border-radius:20px;
        padding:15px;
    }

    .dash-monitor-grid{
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:8px;
    }

    .dash-monitor-stat{
        padding:10px;
    }

    .dash-monitor-stat b{
        font-size:18px;
    }

    .dash-monitor-actions .btn{
        width:100%;
        justify-content:center;
    }
}
</style>
<!-- MACSERVICE DASHBOARD ONLY POLISH V1 STYLE END -->


@php
    $dashPppoeTotal = class_exists(\App\Models\Customer::class)
        ? \App\Models\Customer::query()->whereNotNull('pppoe_username')->where('pppoe_username', '!=', '')->count()
        : 0;

    $dashPppoeOnline = class_exists(\App\Models\Customer::class)
        ? \App\Models\Customer::query()->whereNotNull('pppoe_username')->where('pppoe_username', '!=', '')->where('pppoe_online_status', 'Online')->count()
        : 0;

    $dashPppoeOffline = class_exists(\App\Models\Customer::class)
        ? \App\Models\Customer::query()->whereNotNull('pppoe_username')->where('pppoe_username', '!=', '')->where('pppoe_online_status', 'Offline')->count()
        : 0;

    $dashPppoeUnknown = class_exists(\App\Models\Customer::class)
        ? \App\Models\Customer::query()
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->where(function ($q) {
                $q->whereNull('pppoe_online_status')
                    ->orWhere('pppoe_online_status', '')
                    ->orWhere('pppoe_online_status', 'Unknown');
            })
            ->count()
        : 0;

    $dashPppoeActiveSessions = class_exists(\App\Models\MikrotikPppoeActiveSession::class)
        ? \App\Models\MikrotikPppoeActiveSession::query()->count()
        : 0;

    $dashRouterCount = class_exists(\App\Models\MikrotikRouter::class)
        ? \App\Models\MikrotikRouter::query()->count()
        : 0;

    $dashRouterActive = class_exists(\App\Models\MikrotikRouter::class)
        ? \App\Models\MikrotikRouter::query()->where('status', 'active')->count()
        : 0;

    $dashLastActiveSync = class_exists(\App\Models\MikrotikPppoeActiveSession::class)
        ? \App\Models\MikrotikPppoeActiveSession::query()->max('last_seen_at')
        : null;
@endphp

@php
    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $i = function ($name) {
        $icons = [
            'users' => '<svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M17 11a4 4 0 0 1 0 8"/></svg>',
            'file' => '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>',
            'money' => '<svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>',
            'check' => '<svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>',
            'wifi' => '<svg viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>',
            'server' => '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="6" rx="2"/><rect x="3" y="14" width="18" height="6" rx="2"/><path d="M7 7h.01"/><path d="M7 17h.01"/></svg>',
            'activity' => '<svg viewBox="0 0 24 24"><path d="M3 12h4l3-8 4 16 3-8h4"/></svg>',
            'chart' => '<svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-7"/></svg>',
        ];

        return '<span class="dash-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.admin-dash-hero{
    background:linear-gradient(135deg,#1d4ed8,#0891b2);
    border:1px solid rgba(255,255,255,.24);
    border-radius:28px;
    padding:22px;
    color:#fff;
    box-shadow:0 18px 44px rgba(16,24,40,.08);
    position:relative;
    overflow:hidden;
    margin-bottom:12px;
}
.admin-dash-hero:after{
    content:"";
    position:absolute;
    right:-60px;
    top:-80px;
    width:210px;
    height:210px;
    border-radius:999px;
    background:rgba(255,255,255,.16);
}
.admin-dash-hero span{
    display:block;
    color:#e0f2fe;
    font-size:13px;
    font-weight:800;
}
.admin-dash-hero b{
    display:block;
    margin-top:6px;
    font-size:31px;
    line-height:1;
    letter-spacing:-.07em;
}
.admin-dash-hero p{
    margin:10px 0 0;
    color:#e0f2fe;
    max-width:620px;
    line-height:1.45;
    font-size:14px;
}
.admin-dash-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:10px;
    margin-bottom:12px;
}
.admin-dash-stat{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:22px;
    padding:15px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
    text-decoration:none;
    color:#101828;
}
.admin-dash-stat-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;
}
.admin-dash-stat .label{
    color:#667085;
    font-size:12px;
    font-weight:800;
}
.admin-dash-stat .value{
    display:block;
    margin-top:7px;
    color:#101828;
    font-size:22px;
    font-weight:950;
    letter-spacing:-.055em;
}
.dash-ico{
    width:32px;
    height:32px;
    border-radius:13px;
    display:inline-grid;
    place-items:center;
    color:#175cd3;
    background:#eff6ff;
    flex:none;
}
.dash-ico svg{
    width:17px;
    height:17px;
    stroke:currentColor;
    fill:none;
    stroke-width:2.2;
    stroke-linecap:round;
    stroke-linejoin:round;
}
.admin-dash-section{
    margin:14px 2px 9px;
}
.admin-dash-section b{
    font-size:16px;
    letter-spacing:-.04em;
}
.admin-dash-section span{
    display:block;
    color:#667085;
    font-size:12px;
    margin-top:2px;
}
.admin-dash-status{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:10px;
    margin-bottom:12px;
}
.admin-monitor-grid{
    display:grid;
    grid-template-columns:1.15fr .85fr;
    gap:10px;
}
.admin-monitor-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:22px;
    padding:16px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.admin-monitor-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:12px;
    margin-bottom:12px;
}
.admin-monitor-head b{
    display:block;
    color:#101828;
    font-size:15px;
    letter-spacing:-.03em;
}
.admin-monitor-head span{
    display:block;
    margin-top:3px;
    color:#667085;
    font-size:12px;
}
.admin-monitor-placeholder{
    border:1px dashed #cbd5e1;
    background:#f8fafc;
    color:#667085;
    border-radius:18px;
    padding:16px;
    font-size:13px;
    line-height:1.5;
}
.admin-monitor-bars{
    display:grid;
    gap:9px;
}
.admin-monitor-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    font-size:13px;
    color:#667085;
}
.admin-monitor-row b{
    color:#101828;
}
.admin-bar{
    height:8px;
    border-radius:999px;
    background:#eff6ff;
    overflow:hidden;
    margin-top:6px;
}
.admin-bar i{
    display:block;
    height:100%;
    width:35%;
    border-radius:999px;
    background:linear-gradient(135deg,#2563eb,#06b6d4);
}
.admin-bar.mid i{width:62%}
.admin-bar.low i{width:18%}

@media(max-width:920px){
    .admin-dash-grid,
    .admin-dash-status{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
    .admin-monitor-grid{
        grid-template-columns:1fr;
    }
}
@media(max-width:620px){
    .admin-dash-hero{
        border-radius:22px;
        padding:18px;
    }
    .admin-dash-hero b{
        font-size:25px;
    }
    .admin-dash-grid,
    .admin-dash-status{
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:8px;
    }
    .admin-dash-stat{
        border-radius:18px;
        padding:12px;
    }
    .admin-dash-stat .value{
        font-size:19px;
    }
    .dash-ico{
        width:29px;
        height:29px;
        border-radius:11px;
    }
    .admin-monitor-card{
        border-radius:18px;
        padding:13px;
    }
}

<style>
.dash-pppoe-live{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:10px;
    margin-top:12px;
}
.dash-pppoe-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:18px;
    padding:13px;
}
.dash-pppoe-card span{
    display:block;
    color:#667085;
    font-size:12px;
    font-weight:800;
}
.dash-pppoe-card b{
    display:block;
    margin-top:6px;
    color:#101828;
    font-size:22px;
    letter-spacing:-.055em;
}
.dash-pppoe-card.online b{color:#027a48}
.dash-pppoe-card.offline b{color:#b42318}
.dash-pppoe-card.unknown b{color:#b54708}
.dash-pppoe-actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-top:12px;
}
.dash-pppoe-note{
    margin-top:10px;
    border:1px dashed #b2ddff;
    background:#eff8ff;
    color:#175cd3;
    border-radius:16px;
    padding:11px;
    font-size:13px;
    line-height:1.45;
}
@media(max-width:760px){
    .dash-pppoe-live{
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:8px;
    }
    .dash-pppoe-card{
        border-radius:16px;
        padding:11px;
    }
    .dash-pppoe-card b{
        font-size:18px;
    }
    .dash-pppoe-actions .btn{
        width:100%;
        justify-content:center;
    }
}
</style>

</style>

<div class="admin-dash-hero">
    <span>Dashboard Admin</span>
    <b>Monitoring Billing RT/RW.NET</b>
    <p>Ringkasan pelanggan, tagihan, pembayaran, dan ruang monitor jaringan. Menu operasional tetap di sidebar agar dashboard tidak terlalu penuh.</p>
</div>

<div class="admin-dash-grid">
    <div class="admin-dash-stat">
        <div class="admin-dash-stat-top">
            <div>
                <div class="label">Pelanggan Aktif</div>
                <div class="value">{{ $stats['active_customers'] ?? 0 }}</div>
            </div>
            {!! $i('users') !!}
        </div>
    </div>

    <div class="admin-dash-stat">
        <div class="admin-dash-stat-top">
            <div>
                <div class="label">Total Tagihan</div>
                <div class="value">{{ $stats['invoices'] ?? 0 }}</div>
            </div>
            {!! $i('file') !!}
        </div>
    </div>

    <div class="admin-dash-stat">
        <div class="admin-dash-stat-top">
            <div>
                <div class="label">Tagihan Terbuka</div>
                <div class="value">{{ $money($stats['open_amount'] ?? 0) }}</div>
            </div>
            {!! $i('money') !!}
        </div>
    </div>

    <div class="admin-dash-stat">
        <div class="admin-dash-stat-top">
            <div>
                <div class="label">Terima Hari Ini</div>
                <div class="value">{{ $money($stats['paid_today_amount'] ?? 0) }}</div>
            </div>
            {!! $i('check') !!}
        </div>
    </div>
</div>

<div class="admin-dash-section">
    <b>Status Tagihan</b>
    <span>Ringkasan kondisi tagihan saat ini.</span>
</div>

<div class="admin-dash-status">
    <a class="admin-dash-stat" href="{{ url('/admin/invoices?status=Belum Bayar') }}">
        <div class="label">Belum Bayar</div>
        <div class="value"><span class="badge yellow">{{ $stats['belum_bayar'] ?? 0 }}</span></div>
    </a>

    <a class="admin-dash-stat" href="{{ url('/admin/invoices?status=Nunggak') }}">
        <div class="label">Nunggak</div>
        <div class="value"><span class="badge red">{{ $stats['nunggak'] ?? 0 }}</span></div>
    </a>

    <a class="admin-dash-stat" href="{{ url('/admin/invoices?status=Bayar Awal') }}">
        <div class="label">Bayar Awal</div>
        <div class="value"><span class="badge blue">{{ $stats['bayar_awal'] ?? 0 }}</span></div>
    </a>

    <a class="admin-dash-stat" href="{{ url('/admin/invoices?status=Lunas') }}">
        <div class="label">Lunas</div>
        <div class="value"><span class="badge green">{{ $stats['lunas'] ?? 0 }}</span></div>
    </a>
</div>

<div class="admin-dash-section">
    <b>Monitor Jaringan</b>
    <span>Status router, pelanggan PPPoE online/offline, dan active session Mikrotik.</span>
</div>

<div class="admin-monitor-grid">
    <div class="admin-monitor-card">
        <div class="admin-monitor-head">
            <div>
                <b>Monitor Mikrotik</b>
                <span>Status router, pelanggan PPPoE online/offline, dan active session.</span>
            </div>
            {!! $i('server') !!}
        </div>

        <div class="admin-monitor-placeholder">
            
<div class="dash-pppoe-live">
    <div class="dash-pppoe-card">
        <span>Total PPPoE</span>
        <b>{{ number_format($dashPppoeTotal,0,',','.') }}</b>
    </div>

    <div class="dash-pppoe-card online">
        <span>Online</span>
        <b>{{ number_format($dashPppoeOnline,0,',','.') }}</b>
    </div>

    <div class="dash-pppoe-card offline">
        <span>Offline</span>
        <b>{{ number_format($dashPppoeOffline,0,',','.') }}</b>
    </div>

    <div class="dash-pppoe-card unknown">
        <span>Unknown</span>
        <b>{{ number_format($dashPppoeUnknown,0,',','.') }}</b>
    </div>
</div>

<div class="dash-pppoe-note">
    Router aktif: <b>{{ $dashRouterActive }}</b> dari <b>{{ $dashRouterCount }}</b>.
    Active session terbaca: <b>{{ $dashPppoeActiveSessions }}</b>.
    Update terakhir: <b>{{ $dashLastActiveSync ? \Carbon\Carbon::parse($dashLastActiveSync)->format('d/m/Y H:i') : 'Belum ada refresh Active' }}</b>.
</div>

<div class="dash-pppoe-actions">
    <a class="btn" href="{{ url('/admin/monitoring/pppoe') }}">Buka Monitoring PPPoE</a>
    <a class="btn light" href="{{ url('/admin/settings/mikrotik') }}">Refresh dari Mikrotik</a>
</div>

        </div>
    </div>

    <div class="admin-monitor-card">
        <div class="admin-monitor-head">
            <div>
                <b>Ringkasan Trafik</b>
                <span>Ringkasan awal dari data PPPoE Active yang sudah terbaca.</span>
            </div>
            {!! $i('activity') !!}
        </div>

        <div class="admin-monitor-bars">
            <div>
                <div class="admin-monitor-row"><span>Active Session</span><b>Belum aktif</b></div>
                <div class="admin-bar mid"><i></i></div>
            </div>

            <div>
                <div class="admin-monitor-row"><span>Pelanggan Online</span><b>Belum aktif</b></div>
                <div class="admin-bar"><i></i></div>
            </div>

            <div>
                <div class="admin-monitor-row"><span>Pelanggan Offline</span><b>Belum aktif</b></div>
                <div class="admin-bar low"><i></i></div>
            </div>
        </div>
    </div>
</div>
@endsection
