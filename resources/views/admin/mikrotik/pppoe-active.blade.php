@extends('layouts.neo')

@section('title','PPPoE Active')

@section('content')
@include('admin.mikrotik._style')

<style id="pppoe-active-clean-scroll-css">
.pppoe-active-scroll{
    overflow:auto !important;
    max-height:calc(100dvh - 300px);
    border-top:1px solid #eef2f7;
    border-bottom:1px solid #eef2f7;
    -webkit-overflow-scrolling:touch;
}

.pppoe-active-scroll .mkt-table{
    min-width:920px !important;
}

.pppoe-active-scroll .mkt-table thead th{
    position:sticky;
    top:0;
    z-index:3;
    background:#f8fafc;
}

.pppoe-active-scroll .mkt-table th:first-child,
.pppoe-active-scroll .mkt-table td:first-child{
    position:sticky;
    left:0;
    z-index:2;
    background:#fff;
    box-shadow:8px 0 16px rgba(15,23,42,.05);
}

.pppoe-active-scroll .mkt-table thead th:first-child{
    z-index:4;
    background:#f8fafc;
}

.pppoe-active-summary{
    padding:11px 15px;
    border-top:1px solid #eef2f7;
    background:#f8fafc;
    color:#475569;
    font-size:12px;
    font-weight:900;
}
</style>

<div class="mkt-wrap">
    <div class="mkt-card">
        <div class="mkt-head">
            <div class="mkt-title">
                <b>PPPoE Active</b>
                <span>Data koneksi PPPoE yang sedang aktif. Aksi tautkan dipindahkan ke PPPoE Secret.</span>
            </div>

            <form class="mkt-filter" method="GET" action="{{ url('/admin/mikrotik/pppoe-active') }}">
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
            <form method="POST" action="{{ url('/admin/mikrotik/pppoe-active/refresh') }}">
                @csrf
                <input type="hidden" name="router_id" value="{{ $routerId }}">
                <button class="mkt-btn primary" type="submit">Refresh dari Mikrotik</button>
            </form>
        </div>

        <div class="mkt-table-wrap pppoe-active-scroll">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th>Router</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Caller ID</th>
                        <th>Uptime</th>
                        <th>Last Seen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $row)
                        <tr>
                            <td>{{ $row->router->name ?? '-' }}</td>
                            <td>{{ $row->name }}</td>
                            <td>{{ $row->address ?? '-' }}</td>
                            <td>{{ $row->caller_id ?? '-' }}</td>
                            <td>{{ $row->uptime ?? '-' }}</td>
                            <td>{{ optional($row->last_seen_at)->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Belum ada data PPPoE active.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pppoe-active-summary">
            Menampilkan semua data PPPoE Active: {{ $sessions->count() }} data.
        </div>
    </div>
</div>
@endsection
