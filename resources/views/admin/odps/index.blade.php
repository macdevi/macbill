@extends('layouts.neo')
@section('title','Master ODP')
@section('content')
@php
    $i = fn($x) => '<span class="neo-mini-ico"><svg viewBox="0 0 24 24">'.[
        'home'=>'<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/>',
        'plus'=>'<path d="M12 5v14"/><path d="M5 12h14"/>',
        'map'=>'<path d="M9 18l-6 3V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/>',
        'search'=>'<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'wifi'=>'<path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/>',
    ][$x].'</svg></span>';

    $qValue = $q ?? '';
@endphp

<style id="admin-odp-action-click-fix">
.admin-odp-actions,
.admin-odp-actions *,
.admin-odp-row-actions,
.admin-odp-row-actions *{
    pointer-events:auto !important;
    position:relative;
    z-index:20;
}

.admin-odp-row-actions{
    display:flex;
    gap:6px;
    align-items:center;
    flex-wrap:nowrap;
}

.admin-odp-row-actions form{
    margin:0;
    padding:0;
    display:inline-flex;
}

.admin-odp-action-btn{
    min-width:auto !important;
    width:auto !important;
    height:32px !important;
    padding:0 11px !important;
    border-radius:10px !important;
    font-size:12px !important;
    font-weight:800 !important;
    line-height:30px !important;
    text-decoration:none !important;
    white-space:nowrap !important;
    cursor:pointer !important;
}

.admin-odp-action-btn.port{
    background:#eff6ff !important;
    border-color:#bfdbfe !important;
    color:#1d4ed8 !important;
}

.admin-odp-action-btn.edit{
    background:#f8fafc !important;
    border-color:#dbe3ef !important;
    color:#0f172a !important;
}

.admin-odp-action-btn.delete{
    background:#fef2f2 !important;
    border-color:#fecaca !important;
    color:#b91c1c !important;
}

.neo-xls-scroll{
    position:relative;
    z-index:1;
}
</style>

<div class="pagehead">
    <div>
        <h1>Master ODP</h1>
        <p>Data ODP, port, dan titik lokasi.</p>
    </div>

    <div class="neo-actions admin-odp-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">{!! $i('home') !!}Home</a>
        <a class="btn" href="{{ url('/admin/odps/create') }}">{!! $i('plus') !!}Tambah ODP</a>
        <a class="btn light" href="{{ url('/admin/odps-map') }}">{!! $i('map') !!}Peta ODP</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<form class="neo-search" method="GET" action="{{ url('/admin/odps') }}">
    <input class="input" name="q" value="{{ $qValue }}" placeholder="Cari ODP atau lokasi...">
    <button class="btn" type="submit">{!! $i('search') !!}Cari</button>
</form>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total halaman ini: <b>{{ $odps->count() }}</b></span>
        <span>Geser kanan untuk aksi</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th class="sticky-left">ID</th>
                    <th>ODP</th>
                    <th>Lokasi</th>
                    <th>Koordinat</th>
                    <th>Port</th>
                    <th>Sisa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($odps as $odp)
                    @php
                        $used = (int)($odp->customers_count ?? 0);
                        $total = (int)($odp->port_count ?? 0);
                        $available = max(0, $total - $used);
                        $portClass = $available <= 0 ? 'red' : ($available <= 2 ? 'yellow' : 'green');
                    @endphp

                    <tr>
                        <td class="neo-id sticky-left">#{{ $odp->id }}</td>
                        <td class="neo-strong">{{ $odp->name }}</td>
                        <td class="neo-clip">{{ $odp->location ?: '-' }}</td>
                        <td>
                            @if($odp->latitude && $odp->longitude)
                                <a class="badge blue" target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $odp->latitude }},{{ $odp->longitude }}">Map</a>
                            @else
                                <span class="badge yellow">Belum ada</span>
                            @endif
                        </td>
                        <td>{{ $used }}/{{ $total }}</td>
                        <td><span class="badge {{ $portClass }}">{{ $available }}</span></td>
                        <td>
                            <span class="badge {{ $odp->status === 'active' ? 'green' : 'red' }}">
                                {{ $odp->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="admin-odp-row-actions">
                                <a class="btn light admin-odp-action-btn port" href="{{ url('/admin/odps/'.$odp->id.'/ports') }}">Port</a>
                                <a class="btn light admin-odp-action-btn edit" href="{{ url('/admin/odps/'.$odp->id.'/edit') }}">Edit</a>

                                <form method="POST" action="{{ url('/admin/odps/'.$odp->id) }}" onsubmit="return confirm('Hapus ODP ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn admin-odp-action-btn delete" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">Belum ada ODP.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(method_exists($odps, 'links'))
    <div class="pagination">{{ $odps->links() }}</div>
@endif

<style id="admin-odp-compact-table-style">
/* Khusus tampilan Master ODP: merapikan lebar kolom tanpa mengubah fungsi */
.neo-xls-table{
    table-layout:fixed;
    min-width:980px;
}

.neo-xls-table th,
.neo-xls-table td{
    vertical-align:middle;
}

.neo-xls-table th{
    height:54px !important;
    padding:12px 14px !important;
    white-space:nowrap !important;
    line-height:1.15 !important;
}

.neo-xls-table td{
    padding:14px 14px !important;
    height:58px !important;
}

.neo-xls-table th:nth-child(1),
.neo-xls-table td:nth-child(1){
    width:76px !important;
    min-width:76px !important;
    max-width:76px !important;
}

.neo-xls-table th:nth-child(2),
.neo-xls-table td:nth-child(2){
    width:180px !important;
    min-width:180px !important;
    max-width:180px !important;
}

.neo-xls-table th:nth-child(3),
.neo-xls-table td:nth-child(3){
    width:230px !important;
    min-width:230px !important;
    max-width:230px !important;
}

.neo-xls-table th:nth-child(4),
.neo-xls-table td:nth-child(4){
    width:130px !important;
    min-width:130px !important;
    max-width:130px !important;
}

.neo-xls-table th:nth-child(5),
.neo-xls-table td:nth-child(5){
    width:86px !important;
    min-width:86px !important;
    max-width:86px !important;
    text-align:center;
}

.neo-xls-table th:nth-child(6),
.neo-xls-table td:nth-child(6){
    width:82px !important;
    min-width:82px !important;
    max-width:82px !important;
    text-align:center;
}

.neo-xls-table th:nth-child(7),
.neo-xls-table td:nth-child(7){
    width:112px !important;
    min-width:112px !important;
    max-width:112px !important;
    text-align:center;
}

.neo-xls-table th:nth-child(8),
.neo-xls-table td:nth-child(8){
    width:210px !important;
    min-width:210px !important;
    max-width:210px !important;
}

.neo-xls-table .neo-mini-ico,
.neo-xls-table .neo-mini-ico svg{
    width:16px !important;
    height:16px !important;
    min-width:16px !important;
    max-width:16px !important;
}

.neo-xls-table .neo-clip{
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.admin-odp-row-actions{
    justify-content:flex-start;
}

@media(max-width:760px){
    .neo-xls-table{
        min-width:920px;
    }

    .neo-xls-table th{
        height:50px !important;
        padding:10px 12px !important;
        font-size:12px !important;
    }

    .neo-xls-table td{
        padding:12px 12px !important;
        height:54px !important;
        font-size:14px !important;
    }

    .neo-xls-table th:nth-child(1),
    .neo-xls-table td:nth-child(1){
        width:72px !important;
        min-width:72px !important;
        max-width:72px !important;
    }

    .neo-xls-table th:nth-child(2),
    .neo-xls-table td:nth-child(2){
        width:170px !important;
        min-width:170px !important;
        max-width:170px !important;
    }

    .neo-xls-table th:nth-child(3),
    .neo-xls-table td:nth-child(3){
        width:210px !important;
        min-width:210px !important;
        max-width:210px !important;
    }
}
</style>

@endsection
