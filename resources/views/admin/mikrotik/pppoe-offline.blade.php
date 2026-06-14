@extends('layouts.neo')
@section('title','PPPoE Offline')

@section('content')
@include('admin.mikrotik._style')

<style>
.pppoe-offline-scroll{overflow:auto;max-height:calc(100dvh - 280px);border-top:1px solid #eef2f7;border-bottom:1px solid #eef2f7;}
.pppoe-offline-scroll .mkt-table{min-width:880px;}
.pppoe-offline-summary{padding:12px 14px;color:#64748b;font-size:13px;font-weight:800;}
.offline-badge{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:7px 12px;background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;font-size:12px;font-weight:900;}
</style>

<div class="mkt-wrap">
    <div class="mkt-card">
        <div class="mkt-head">
            <div class="mkt-title">
                <b>PPPoE Offline</b>
                <span>Daftar pelanggan PPPoE yang tidak online.</span>
            </div>

            <form class="mkt-filter" method="GET" action="{{ url('/admin/mikrotik/pppoe-offline') }}">
                <select name="router_id">
                    <option value="">Semua Router</option>
                    @foreach($routers as $router)
                        <option value="{{ $router->id }}" @selected((string)$routerId === (string)$router->id)>{{ $router->name }}</option>
                    @endforeach
                </select>
                <button class="mkt-btn light" type="submit">Filter</button>
            </form>
        </div>

        <div class="mkt-table-wrap pppoe-offline-scroll">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>PPPoE Username</th>
                        <th>Status</th>
                        <th>Last Seen</th>
                        <th>Remote Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->name }}</td>
                            <td>{{ $row->pppoe_username ?: '-' }}</td>
                            <td><span class="offline-badge">{{ $row->pppoe_online_status ?: 'Unknown' }}</span></td>
                            <td>{{ $row->pppoe_last_seen_at ? \Carbon\Carbon::parse($row->pppoe_last_seen_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $row->pppoe_remote_address ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Tidak ada pelanggan offline.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pppoe-offline-summary">
            Total pelanggan offline / unknown: {{ $rows->count() }} data.
        </div>
    </div>
</div>
@endsection
