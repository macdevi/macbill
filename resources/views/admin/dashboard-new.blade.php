@extends('layouts.neo')
@section('title','Dashboard Admin')

@section('content')
@php
    $profitTone = $profitBulanIni >= 0 ? 'tone-profit' : 'tone-danger';
@endphp

<style>
.dash-wrap{
    display:flex;
    flex-direction:column;
    gap:12px;
    padding-bottom:92px;
}

.dash-hero{
    position:relative;
    overflow:hidden;
    border-radius:26px;
    padding:18px;
    color:#fff;
    background:
        radial-gradient(circle at 85% 15%, rgba(255,255,255,.28), transparent 30%),
        linear-gradient(135deg,#1d4ed8 0%,#0f766e 100%);
    box-shadow:0 18px 36px rgba(29,78,216,.20);
}

.dash-hero:after{
    content:"";
    position:absolute;
    right:-46px;
    bottom:-56px;
    width:170px;
    height:170px;
    border-radius:999px;
    background:rgba(255,255,255,.12);
}

.dash-kicker{
    position:relative;
    z-index:1;
    font-size:12px;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
    opacity:.82;
}

.dash-title{
    position:relative;
    z-index:1;
    margin:7px 0 0;
    font-size:25px;
    line-height:1.05;
    font-weight:950;
}

.dash-desc{
    position:relative;
    z-index:1;
    margin:8px 0 0;
    max-width:520px;
    font-size:13px;
    line-height:1.45;
    opacity:.86;
}

.metric-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:10px;
}

.metric-card{
    position:relative;
    min-height:112px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    gap:8px;
    padding:14px;
    border-radius:22px;
    background:#fff;
    border:1px solid rgba(226,232,240,.92);
    box-shadow:0 10px 26px rgba(15,23,42,.055);
    text-decoration:none;
    color:#0f172a;
    overflow:hidden;
}

.metric-card:before{
    content:"";
    position:absolute;
    inset:0;
    background:linear-gradient(135deg,rgba(255,255,255,.8),rgba(248,250,252,.35));
    pointer-events:none;
}

.metric-top,
.metric-bottom{
    position:relative;
    z-index:1;
}

.metric-label{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    font-size:12px;
    line-height:1.25;
    font-weight:950;
    color:#64748b;
}

.metric-icon{
    flex:0 0 auto;
    width:30px;
    height:30px;
    border-radius:13px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

.metric-icon svg{
    width:17px;
    height:17px;
    stroke:currentColor;
}

.metric-value{
    margin-top:8px;
    font-size:25px;
    line-height:1;
    font-weight:950;
    letter-spacing:-.04em;
    color:#0f172a;
    word-break:break-word;
}

.metric-sub{
    font-size:11px;
    font-weight:850;
    line-height:1.25;
    color:#94a3b8;
}

.metric-card.tone-blue .metric-icon{background:#eff6ff;color:#2563eb;}
.metric-card.tone-green .metric-icon{background:#ecfdf3;color:#059669;}
.metric-card.tone-orange .metric-icon{background:#fff7ed;color:#ea580c;}
.metric-card.tone-danger .metric-icon{background:#fef2f2;color:#dc2626;}
.metric-card.tone-purple .metric-icon{background:#f5f3ff;color:#7c3aed;}
.metric-card.tone-profit .metric-icon{background:#ecfdf3;color:#047857;}

.metric-card.tone-green .metric-value{color:#047857;}
.metric-card.tone-danger .metric-value{color:#dc2626;}
.metric-card.tone-orange .metric-value{color:#c2410c;}
.metric-card.tone-profit .metric-value{color:#047857;}
.metric-card.tone-blue .metric-value{color:#1d4ed8;}

.metric-card.tone-danger{
    border-color:#fecaca;
    background:#fff;
}

.metric-card.tone-danger:before{
    background:linear-gradient(135deg,#fff,#fff5f5);
}

.metric-card.tone-orange{
    border-color:#fed7aa;
}

.metric-card.tone-orange:before{
    background:linear-gradient(135deg,#fff,#fff7ed);
}

.status-card{
    background:#fff;
    border:1px solid rgba(226,232,240,.92);
    border-radius:24px;
    padding:15px;
    box-shadow:0 10px 26px rgba(15,23,42,.055);
}

.status-head{
    display:flex;
    justify-content:space-between;
    gap:12px;
    align-items:flex-start;
    margin-bottom:12px;
}

.status-head h2{
    margin:0;
    font-size:20px;
    line-height:1.1;
    font-weight:950;
    color:#0f172a;
}

.status-head p{
    margin:5px 0 0;
    font-size:12px;
    line-height:1.35;
    font-weight:750;
    color:#64748b;
}

.status-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:9px;
}

.status-item{
    min-height:82px;
    border-radius:18px;
    padding:11px;
    background:#f8fafc;
    border:1px solid #eef2f7;
}

.status-label{
    font-size:11px;
    line-height:1.2;
    font-weight:950;
    color:#64748b;
}

.status-value{
    margin-top:8px;
    font-size:22px;
    line-height:1;
    font-weight:950;
    letter-spacing:-.03em;
}

.status-total .status-value{color:#2563eb;}
.status-paid .status-value{color:#059669;}
.status-unpaid .status-value{color:#d97706;}
.status-overdue .status-value{color:#dc2626;}

.status-sub{
    margin-top:6px;
    font-size:10px;
    font-weight:800;
    color:#94a3b8;
    line-height:1.2;
}

@media(max-width:760px){
    .dash-wrap{
        gap:11px;
        padding-bottom:96px;
    }

    .dash-hero{
        border-radius:24px;
        padding:16px;
    }

    .dash-title{
        font-size:23px;
    }

    .dash-desc{
        font-size:12px;
    }

    .metric-grid{
        gap:9px;
    }

    .metric-card{
        min-height:104px;
        border-radius:20px;
        padding:12px;
    }

    .metric-label{
        font-size:11px;
    }

    .metric-icon{
        width:28px;
        height:28px;
        border-radius:12px;
    }

    .metric-value{
        font-size:22px;
    }

    .metric-sub{
        font-size:10px;
    }

    .status-card{
        border-radius:22px;
        padding:13px;
    }

    .status-grid{
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:9px;
    }

    .status-item{
        min-height:76px;
        padding:10px;
    }

    .status-value{
        font-size:21px;
    }
}
</style>

<div class="dash-wrap">
    <section class="dash-hero">
        <div class="dash-kicker">Dashboard Admin</div>
        <h1 class="dash-title">Dashboard Operasional</h1>
        <p class="dash-desc">
            Monitoring PPPoE, pelanggan online/offline, pendapatan, pengeluaran, profit, dan status tagihan.
        </p>
    </section>

    <section class="metric-grid">
        <a class="metric-card tone-blue" href="{{ url('/admin/mikrotik/pppoe-secret') }}">
            <div class="metric-top">
                <div class="metric-label">
                    <span>Total PPPoE</span>
                    <span class="metric-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 7h16"></path><path d="M4 12h16"></path><path d="M4 17h16"></path>
                        </svg>
                    </span>
                </div>
                <div class="metric-value">{{ number_format($totalPppoe) }}</div>
            </div>
            <div class="metric-bottom metric-sub">Buka PPPoE Secret</div>
        </a>
<a class="metric-card tone-green" href="{{ url('/admin/mikrotik/pppoe-active') }}">
            <div class="metric-top">
                <div class="metric-label">
                    <span>Pelanggan Aktif</span>
                    <span class="metric-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12.5a10 10 0 0 1 14 0"></path><path d="M8.5 16a5 5 0 0 1 7 0"></path><path d="M12 19h.01"></path>
                        </svg>
                    </span>
                </div>
                <div class="metric-value">{{ number_format($pelangganAktif) }}</div>
            </div>
            <div class="metric-bottom metric-sub">Buka PPPoE Active</div>
        </a>

        <a class="metric-card tone-danger" href="{{ url('/admin/mikrotik/pppoe-offline') }}">
            <div class="metric-top">
                <div class="metric-label">
                    <span>Pelanggan Offline</span>
                    <span class="metric-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 8.8a16 16 0 0 1 20 0"></path><path d="M5 12a11 11 0 0 1 14 0"></path><path d="M8.5 15.5a6 6 0 0 1 7 0"></path><path d="M3 3l18 18"></path>
                        </svg>
                    </span>
                </div>
                <div class="metric-value">{{ number_format($pelangganOffline) }}</div>
            </div>
            <div class="metric-bottom metric-sub">Buka PPPoE Offline</div>
        </a>

        <div class="metric-card tone-green">
            <div class="metric-top">
                <div class="metric-label">
                    <span>Pemasukan Bulan Ini</span>
                    <span class="metric-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3v18"></path><path d="M17 7H9.5a3.5 3.5 0 0 0 0 7H14a3 3 0 0 1 0 6H6"></path>
                        </svg>
                    </span>
                </div>
                <div class="metric-value">Rp {{ number_format($pemasukanBulanIni,0,',','.') }}</div>
            </div>
            <div class="metric-bottom metric-sub">Pembayaran bulan berjalan</div>
        </div>

        <div class="metric-card tone-orange">
            <div class="metric-top">
                <div class="metric-label">
                    <span>Pendapatan Tertunda</span>
                    <span class="metric-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>
                        </svg>
                    </span>
                </div>
                <div class="metric-value">Rp {{ number_format($pendapatanTertunda,0,',','.') }}</div>
            </div>
            <div class="metric-bottom metric-sub">Belum bayar + nunggak</div>
        </div>

        <div class="metric-card tone-danger">
            <div class="metric-top">
                <div class="metric-label">
                    <span>Pengeluaran Bulan Ini</span>
                    <span class="metric-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 21V3"></path><path d="M7 17h7.5a3.5 3.5 0 0 0 0-7H10a3 3 0 0 1 0-6h8"></path>
                        </svg>
                    </span>
                </div>
                <div class="metric-value">Rp {{ number_format($pengeluaranBulanIni,0,',','.') }}</div>
            </div>
            <div class="metric-bottom metric-sub">Biaya operasional bulan ini</div>
        </div>

        <div class="metric-card {{ $profitTone }}">
            <div class="metric-top">
                <div class="metric-label">
                    <span>Profit Bulan Ini</span>
                    <span class="metric-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 17l6-6 4 4 8-8"></path><path d="M14 7h7v7"></path>
                        </svg>
                    </span>
                </div>
                <div class="metric-value">Rp {{ number_format($profitBulanIni,0,',','.') }}</div>
            </div>
            <div class="metric-bottom metric-sub">Pemasukan - pengeluaran</div>
        </div>
    </section>

    <section class="status-card">
        <div class="status-head">
            <div>
                <h2>Status Tagihan</h2>
                <p>Ringkasan invoice saat ini.</p>
            </div>
        </div>

        <div class="status-grid">
            <div class="status-item status-total">
                <div class="status-label">Total Tagihan</div>
                <div class="status-value">{{ number_format($statusTagihan['total']) }}</div>
            </div>

            <div class="status-item status-paid">
                <div class="status-label">Sudah Bayar</div>
                <div class="status-value">{{ number_format($statusTagihan['sudah_bayar']) }}</div>
                <div class="status-sub">Lunas + bayar awal</div>
            </div>

            <div class="status-item status-unpaid">
                <div class="status-label">Belum Bayar</div>
                <div class="status-value">{{ number_format($statusTagihan['belum_bayar']) }}</div>
            </div>

            <div class="status-item status-overdue">
                <div class="status-label">Nunggak</div>
                <div class="status-value">{{ number_format($statusTagihan['nunggak']) }}</div>
            </div>
        </div>
    </section>
</div>
@endsection
