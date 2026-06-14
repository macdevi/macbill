@extends('layouts.neo')

@section('title','PPPoE Profile')

@section('content')
@include('admin.mikrotik._style')

<style id="pppoe-profile-package-css">
.pppoe-profile-scroll{
    overflow:auto !important;
    max-height:calc(100dvh - 320px);
    border-top:1px solid #eef2f7;
    border-bottom:1px solid #eef2f7;
    -webkit-overflow-scrolling:touch;
}

.pppoe-profile-scroll .mkt-table{
    min-width:1120px !important;
}

.pppoe-profile-scroll .mkt-table thead th{
    position:sticky;
    top:0;
    z-index:3;
    background:#f8fafc;
}

.profile-package-ok{
    display:inline-flex;
    align-items:center;
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

.profile-package-empty{
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
</style>

<div class="mkt-wrap">
    <div class="mkt-card">
        <div class="mkt-head">
            <div class="mkt-title">
                <b>PPPoE Profile</b>
                <span>Sync profile dari Mikrotik akan tambah/update paket billing berdasarkan nama profile.</span>
            </div>

            <form class="mkt-filter" method="GET" action="{{ url('/admin/mikrotik/pppoe-profile') }}">
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
            <form method="POST" action="{{ url('/admin/mikrotik/pppoe-profile/sync') }}">
                @csrf
                <input type="hidden" name="router_id" value="{{ $routerId }}">
                <button class="mkt-btn primary" type="submit">Sync Profile dari Mikrotik</button>
            </form>
        </div>

        <div class="mkt-table-wrap pppoe-profile-scroll">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th>Router</th>
                        <th>Name</th>
                        <th>Rate Limit</th>
                        <th>Local Address</th>
                        <th>Remote Address</th>
                        <th>Paket Billing</th>
                        <th>Last Sync</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $row)
                        @php
                            $packageKey = mb_strtolower(trim((string) $row->name));
                            $package = ($packagesByName ?? collect())->get($packageKey);
                        @endphp

                        <tr>
                            <td>{{ $row->router->name ?? '-' }}</td>
                            <td>{{ $row->name }}</td>
                            <td>{{ $row->rate_limit ?? '-' }}</td>
                            <td>{{ $row->local_address ?? '-' }}</td>
                            <td>{{ $row->remote_address ?? '-' }}</td>
                            <td>
                                @if($package)
                                    <span class="profile-package-ok" title="{{ $package->name }}">
                                        Ada: {{ $package->name }}
                                    </span>
                                @else
                                    <span class="profile-package-empty">Belum ada paket</span>
                                @endif
                            </td>
                            <td>{{ optional($row->last_synced_at)->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Belum ada data PPPoE profile.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mkt-body">
            {{ $profiles->links() }}
        </div>
    </div>
</div>
@endsection
