@extends('layouts.neo')
@section('title','Pelanggan')
@section('content')

<style>
/* Admin pelanggan: hilangkan pagination besar dan jadikan tabel scrollable */
.customer-page-scroll .pagination,
.customer-page-scroll nav[role="navigation"],
.customer-page-scroll .hidden.sm\:flex-1,
.customer-page-scroll .flex.justify-between,
.customer-page-scroll p.text-sm.text-gray-700{
    display:none !important;
}
.customer-page-scroll .neo-xls-scroll{
    max-height:calc(100vh - 260px);
    overflow:auto;
}
.customer-page-scroll .neo-xls-table thead th{
    position:sticky;
    top:0;
    z-index:5;
    background:#f8fafc;
}
.customer-page-total{
    margin:12px 0 0;
    color:#667085;
    font-size:13px;
    font-weight:800;
}
@media(max-width:620px){
    .customer-page-scroll .neo-xls-scroll{
        max-height:calc(100vh - 230px);
    }
}
</style>

<div class="customer-page-scroll">
@php
    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $i = function ($name) {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>',
            'menu' => '<svg viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>',
            'plus' => '<svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
            'download' => '<svg viewBox="0 0 24 24"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>',
            'upload' => '<svg viewBox="0 0 24 24"><path d="M12 21V9"/><path d="m7 14 5-5 5 5"/><path d="M5 3h14"/></svg>',
            'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
            'user' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
            'phone' => '<svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 3.1 5.2 2 2 0 0 1 5.1 3h3a2 2 0 0 1 2 1.7l.4 2.5a2 2 0 0 1-.6 1.8l-1.2 1.2a16 16 0 0 0 6.1 6.1l1.2-1.2a2 2 0 0 1 1.8-.6l2.5.4a2 2 0 0 1 1.7 2z"/></svg>',
            'wifi' => '<svg viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>',
            'box' => '<svg viewBox="0 0 24 24"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>',
            'cash' => '<svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>',
            'calendar' => '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>',
            'map' => '<svg viewBox="0 0 24 24"><path d="M9 18l-6 3V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>',
            'more' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.neo-mini-ico{
    width:18px;
    height:18px;
    display:inline-grid;
    place-items:center;
    vertical-align:-4px;
    margin-right:5px;
    color:currentColor;
}

.neo-mini-ico svg{
    width:15px;
    height:15px;
    stroke:currentColor;
    fill:none;
    stroke-width:2.25;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.customer-top-actions{
    display:flex;
    gap:8px;
    align-items:center;
    flex-wrap:wrap;
}

.action-dd{
    position:relative;
    display:inline-block;
}

.action-dd summary{
    list-style:none;
    cursor:pointer;
}

.action-dd summary::-webkit-details-marker{
    display:none;
}

.action-dd-menu{
    position:absolute;
    right:0;
    top:calc(100% + 8px);
    width:215px;
    padding:7px;
    border-radius:16px;
    background:#fff;
    border:1px solid #e4e7ec;
    box-shadow:0 18px 42px rgba(16,24,40,.18);
    z-index:9999;
}

.action-dd-menu a{
    display:flex;
    align-items:center;
    gap:8px;
    padding:10px 11px;
    border-radius:12px;
    color:#101828;
    font-size:13px;
    font-weight:850;
}

.action-dd-menu a:hover{
    background:#f8fafc;
}

.action-dd-menu .primary{
    background:#eff6ff;
    color:#175cd3;
}

.action-dd-menu .divider{
    height:1px;
    background:#eef2f6;
    margin:5px 4px;
}

.excel-search{
    display:flex;
    gap:7px;
    background:#fff;
    border:1px solid #e4e7ec;
    border-radius:18px;
    padding:7px;
    margin-bottom:10px;
    box-shadow:0 10px 26px rgba(16,24,40,.055);
}

.excel-search .input{
    min-height:38px;
    border-radius:13px;
    font-size:13px;
}

.excel-search .btn{
    min-height:38px;
    border-radius:13px;
    box-shadow:none;
}

.excel-shell{
    background:#fff;
    border:1px solid #e4e7ec;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 14px 36px rgba(16,24,40,.07);
}

.table-info{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    padding:9px 11px;
    background:#f8fafc;
    border-bottom:1px solid #e4e7ec;
    color:#667085;
    font-size:12px;
}

.excel-scroll{
    overflow:auto;
    -webkit-overflow-scrolling:touch;
    max-height:calc(100vh - 245px);
}

.excel-table{
    width:100%;
    min-width:1180px;
    border-collapse:separate;
    border-spacing:0;
    font-size:13px;
}

.excel-table th{
    position:sticky;
    top:0;
    z-index:5;
    background:linear-gradient(180deg,#f9fafb,#f2f4f7);
    color:#475467;
    text-align:left;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.055em;
    font-weight:900;
    padding:10px 9px;
    border-bottom:1px solid #d0d5dd;
    border-right:1px solid #eaecf0;
    white-space:nowrap;
}

.excel-table td{
    padding:8px 9px;
    border-bottom:1px solid #f2f4f7;
    border-right:1px solid #f2f4f7;
    vertical-align:middle;
    white-space:nowrap;
    line-height:1.25;
    background:#fff;
}

.excel-table tbody tr:nth-child(even) td{
    background:#fcfcfd;
}

.excel-table tr:hover td{
    background:#eff6ff;
}

.excel-table .sticky-left{
    position:sticky;
    left:0;
    z-index:4;
    background:inherit;
}

.excel-table th.sticky-left{
    z-index:7;
}

.excel-table .idcol{
    width:54px;
    text-align:center;
    color:#667085;
    font-weight:900;
}

.excel-table .namecol{
    min-width:190px;
    font-weight:900;
    color:#101828;
}

.excel-table .phonecol{
    color:#344054;
    font-weight:700;
}

.excel-table .moneycol{
    text-align:right;
    font-weight:900;
    color:#101828;
}

.excel-table .addresscol{
    max-width:280px;
    overflow:hidden;
    text-overflow:ellipsis;
    color:#667085;
}

.excel-actions{
    display:flex;
    gap:5px;
    align-items:center;
    flex-wrap:nowrap;
}

.excel-actions .btn{
    min-height:29px;
    padding:5px 8px;
    border-radius:10px;
    font-size:11px;
    box-shadow:none;
}

.excel-actions form{
    margin:0;
}

@media(max-width:760px){
    .pagehead{
        margin-bottom:8px !important;
    }

    .customer-top-actions{
        gap:7px;
    }

    .customer-top-actions .btn,
    .customer-top-actions summary.btn{
        min-height:36px !important;
        padding:8px 12px !important;
        border-radius:13px !important;
        font-size:12px !important;
        box-shadow:none !important;
    }

    .action-dd-menu{
        left:0;
        right:auto;
        width:195px;
    }

    .excel-search{
        margin-bottom:8px;
    }

    .excel-scroll{
        max-height:calc(100vh - 230px);
    }

    .excel-table{
        min-width:1120px;
        font-size:12px;
    }

    .excel-table th{
        padding:8px 7px;
        font-size:10px;
    }

    .excel-table td{
        padding:7px;
    }

    .excel-actions .btn{
        min-height:27px !important;
        padding:5px 7px !important;
        font-size:10px !important;
        border-radius:9px !important;
    }

    .table-info{
        padding:7px 9px;
        font-size:11px;
    }
}
</style>

<div class="pagehead">
    <div>
        <h1>Pelanggan</h1>
        <p>Tabel compact satu baris seperti Excel.</p>
    </div>

    <div class="customer-top-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">{!! $i('home') !!}Home</a>

        <details class="action-dd">
            <summary class="btn light">{!! $i('menu') !!}Aksi</summary>

            <div class="action-dd-menu">
                <a class="primary" href="{{ url('/admin/customers/create') }}">{!! $i('plus') !!}Tambah Pelanggan</a>
                <div class="divider"></div>
                <a href="{{ url('/admin/customers/template') }}">{!! $i('download') !!}Template XLSX</a>
                <a href="{{ url('/admin/customers/export') }}">{!! $i('download') !!}Export XLSX</a>
                <a href="{{ url('/admin/customers/import') }}">{!! $i('upload') !!}Import XLSX</a>
            </div>
        </details>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<form class="excel-search" method="GET" action="{{ url('/admin/customers') }}">
    <input class="input" name="q" value="{{ $q }}" placeholder="Cari nama, HP, alamat, ODP...">
    <button class="btn" type="submit">{!! $i('search') !!}Cari</button>
</form>

<div class="excel-shell">
    <div class="table-info">
        <span>Total halaman ini: <b>{{ $customers->count() }}</b></span>
        <span>Geser kanan untuk lihat semua kolom</span>
    </div>

    <div class="excel-scroll">
        <table class="excel-table">
            <thead>
                <tr>
                    <th class="sticky-left">ID</th>
                    <th>{!! $i('user') !!}Nama</th>
                    <th>{!! $i('phone') !!}HP</th>
                    <th>{!! $i('wifi') !!}ODP</th>
                    <th>Port</th>
                    <th>{!! $i('box') !!}Paket</th>
                    <th>Speed</th>
                    <th>{!! $i('cash') !!}Tagihan</th>
                    <th>{!! $i('calendar') !!}Tgl</th>
                    <th>Status</th>
                    <th>{!! $i('map') !!}Alamat</th>
                    <th>{!! $i('more') !!}Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td class="idcol sticky-left">#{{ $customer->id }}</td>
                        <td class="namecol">{{ $customer->name }}</td>
                        <td class="phonecol">{{ $customer->phone ?: '-' }}</td>
                        <td>{{ $customer->odp ?: '-' }}</td>
                        <td>{{ $customer->port_number ? 'Port '.$customer->port_number : '-' }}</td>
                        <td>{{ $customer->package?->name ?: '-' }}</td>
                        <td>{{ $customer->package?->speed ?: '-' }}</td>
                        <td class="moneycol">{{ $money($customer->monthly_price) }}</td>
                        <td>{{ $customer->billing_day ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $customer->status === 'active' ? 'green' : 'red' }}">
                                {{ $customer->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="addresscol" title="{{ $customer->address }}">{{ $customer->address ?: '-' }}</td>
                        <td>
                            <div class="excel-actions">
                                <a class="btn light icon" title="Detail" aria-label="Detail" href="{{ url('/admin/customers/'.$customer->id.'/detail') }}"><svg viewBox="0 0 24 24"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                <a class="btn light icon" title="Edit" aria-label="Edit" href="{{ url('/admin/customers/'.$customer->id.'/edit') }}"><svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg></a>

                                <form method="POST" action="{{ url('/admin/customers/'.$customer->id) }}" onsubmit="return confirm('Hapus pelanggan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn red icon" title="Hapus" aria-label="Hapus" type="submit"><svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">Belum ada pelanggan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">{{ $customers->links() }}</div>

<script>
document.addEventListener('click', function (e) {
    document.querySelectorAll('.action-dd[open]').forEach(function (dd) {
        if (!dd.contains(e.target)) dd.removeAttribute('open');
    });
});
</script>

</div>
@endsection
