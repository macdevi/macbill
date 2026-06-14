@extends('layouts.neo')

@section('title','PPPoE Secret')

@section('content')
@include('admin.mikrotik._style')

<style id="pppoe-secret-action-left-icon-css">
.pppoe-secret-scroll{
    overflow:auto !important;
    max-height:calc(100dvh - 320px);
    border-top:1px solid #eef2f7;
    border-bottom:1px solid #eef2f7;
    -webkit-overflow-scrolling:touch;
}

.pppoe-secret-scroll .mkt-table{
    min-width:1240px !important;
}

.pppoe-secret-scroll .mkt-table thead th{
    position:sticky;
    top:0;
    z-index:3;
    background:#f8fafc;
}

.pppoe-secret-action-th,
.pppoe-secret-action-td{
    position:sticky;
    left:0;
    z-index:6;
    width:64px !important;
    min-width:64px !important;
    max-width:64px !important;
    text-align:center !important;
    background:#fff;
    box-shadow:8px 0 16px rgba(15,23,42,.06);
    padding-left:8px !important;
    padding-right:8px !important;
}

.pppoe-secret-action-th{
    z-index:8;
    background:#f8fafc;
}

.pppoe-secret-icon-btn,
.pppoe-secret-icon-ok{
    width:38px;
    height:38px;
    border-radius:14px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    font-size:0;
}

.pppoe-secret-icon-btn{
    border:1px solid #dbeafe;
    background:#eff6ff;
    color:#1d4ed8;
}

.pppoe-secret-icon-btn:hover{
    background:#dbeafe;
}

.pppoe-secret-icon-ok{
    border:1px solid #abefc6;
    background:#ecfdf3;
    color:#027a48;
}

.pppoe-secret-icon-btn svg,
.pppoe-secret-icon-ok svg{
    width:18px;
    height:18px;
    stroke:currentColor;
}

.pppoe-status-tertaut{
    display:inline-flex;
    align-items:center;
    gap:6px;
    max-width:220px;
    min-height:30px;
    border-radius:999px;
    padding:6px 10px;
    background:#ecfdf3;
    color:#027a48;
    border:1px solid #abefc6;
    font-size:11px;
    font-weight:950;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.pppoe-status-belum{
    display:inline-flex;
    align-items:center;
    min-height:30px;
    border-radius:999px;
    padding:6px 10px;
    background:#f8fafc;
    color:#64748b;
    border:1px solid #e2e8f0;
    font-size:11px;
    font-weight:950;
}

.auto-link-secret-btn{
    border:1px solid #abefc6 !important;
    background:#ecfdf3 !important;
    color:#027a48 !important;
}

.auto-link-secret-btn:hover{
    background:#d1fadf !important;
}

.pppoe-secret-toolbar{
    display:flex;
    gap:8px;
    align-items:center;
    flex-wrap:wrap;
}

.pppoe-secret-summary{
    padding:11px 15px;
    border-top:1px solid #eef2f7;
    background:#f8fafc;
    color:#475569;
    font-size:12px;
    font-weight:900;
}

@media(max-width:760px){
    .pppoe-secret-scroll .mkt-table{
        min-width:1040px !important;
    }

    .pppoe-secret-action-th,
    .pppoe-secret-action-td{
        width:56px !important;
        min-width:56px !important;
        max-width:56px !important;
        padding-left:6px !important;
        padding-right:6px !important;
    }

    .pppoe-secret-icon-btn,
    .pppoe-secret-icon-ok{
        width:34px;
        height:34px;
        border-radius:12px;
    }

    .pppoe-secret-icon-btn svg,
    .pppoe-secret-icon-ok svg{
        width:16px;
        height:16px;
    }
}
</style>

<div class="mkt-wrap">
    <div class="mkt-card">
        <div class="mkt-head">
            <div class="mkt-title">
                <b>PPPoE Secret</b>
                <span>Daftar PPPoE Secret dari Mikrotik. Jika sudah sinkron ke pelanggan, status menjadi Tertaut.</span>
            </div>

            <form class="mkt-filter" method="GET" action="{{ url('/admin/mikrotik/pppoe-secret') }}">
                <select name="router_id">
                    <option value="">Semua Router</option>
                    @foreach($routers as $router)
                        <option value="{{ $router->id }}" @selected((string)$routerId === (string)$router->id)>
                            {{ $router->name }}
                        </option>
                    @endforeach
                </select>
                <button class="mkt-btn light" type="submit">Filter</button>
            </form>
        </div>

        <div class="mkt-body">
            <div class="pppoe-secret-toolbar">
                <form method="POST" action="{{ url('/admin/mikrotik/pppoe-secret/sync') }}">
                    @csrf
                    <input type="hidden" name="router_id" value="{{ $routerId }}">
                    <button class="mkt-btn primary" type="submit">Sync Secret dari Mikrotik</button>
                </form>

                <form method="POST" action="{{ url('/admin/mikrotik/pppoe-secret/auto-link') }}" onsubmit="return confirm('Jalankan auto sync tautkan? Sistem hanya akan menautkan data yang cocok kuat. Data ambigu akan diskip untuk manual.');">
                    @csrf
                    <input type="hidden" name="router_id" value="{{ $routerId }}">
                    <button class="mkt-btn light auto-link-secret-btn" type="submit">Sync Otomatis Tautkan</button>
                </form>
            </div>
        </div>

        <div class="mkt-table-wrap pppoe-secret-scroll">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th class="pppoe-secret-action-th">Aksi</th>
                        <th>Router</th>
                        <th>Name</th>
                        <th>Profile</th>
                        <th>Remote Address</th>
                        <th>Disabled</th>
                        <th>Status</th>
                        <th>Last Sync</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($secrets as $row)
                        @php
                            $usernameKey = mb_strtolower(trim((string) $row->name));

                            $linkedCustomer = ($linkedCustomersBySecretId ?? collect())->get($row->id);

                            if (!$linkedCustomer) {
                                $linkedCustomer = ($linkedCustomersByUsername ?? collect())->get($usernameKey);
                            }

                            $isLinked = !empty($linkedCustomer);
                        @endphp

                        <tr>
                            <td class="pppoe-secret-action-td">
                                @if($isLinked)
                                    <span class="pppoe-secret-icon-ok" title="Tertaut: {{ $linkedCustomer->name }}" aria-label="Tertaut">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 6L9 17l-5-5"></path>
                                        </svg>
                                    </span>
                                @else
                                    <a class="pppoe-secret-icon-btn" href="{{ url('/admin/mikrotik/pppoe-secret/'.$row->id.'/tautkan') }}" title="Tautkan {{ $row->name }}" aria-label="Tautkan">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M10 13a5 5 0 0 0 7.07 0l2.12-2.12a5 5 0 0 0-7.07-7.07L11 4.93"></path>
                                            <path d="M14 11a5 5 0 0 0-7.07 0L4.81 13.12a5 5 0 0 0 7.07 7.07L13 19.07"></path>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                            <td>{{ $row->router->name ?? '-' }}</td>
                            <td>{{ $row->name }}</td>
                            <td>{{ $row->profile ?? '-' }}</td>
                            <td>{{ $row->remote_address ?? '-' }}</td>
                            <td>{{ $row->disabled ?? '-' }}</td>
                            <td>
                                @if($isLinked)
                                    <span class="pppoe-status-tertaut" title="{{ $linkedCustomer->name }}">
                                        Tertaut: {{ $linkedCustomer->name }}
                                    </span>
                                @else
                                    <span class="pppoe-status-belum">Belum tertaut</span>
                                @endif
                            </td>
                            <td>{{ optional($row->last_synced_at)->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Belum ada data PPPoE secret. Klik Sync Secret dari Mikrotik terlebih dahulu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pppoe-secret-summary">
            Menampilkan semua data PPPoE Secret: {{ $secrets->count() }} data.
        </div>
    </div>
</div>
@endsection
