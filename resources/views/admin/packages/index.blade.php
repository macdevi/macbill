@extends('layouts.neo')
@section('title','Paket Internet')
@section('content')
@php
    $i = function ($name) {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>',
            'plus' => '<svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
            'wifi' => '<svg viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>',
            'edit' => '<svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>',
            'trash' => '<svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.package-summary{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:10px;
    margin-bottom:10px;
}
.package-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:20px;
    padding:14px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.package-card span{
    display:block;
    color:#667085;
    font-size:12px;
    font-weight:800;
}
.package-card b{
    display:block;
    margin-top:5px;
    color:#101828;
    font-size:22px;
    letter-spacing:-.055em;
}
.package-actions{
    display:flex;
    align-items:center;
    gap:5px;
    flex-wrap:nowrap;
}
.package-actions form{
    margin:0;
}
@media(max-width:760px){
    .package-summary{
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:7px;
    }
    .package-card{
        border-radius:16px;
        padding:10px;
    }
    .package-card span{
        font-size:10px;
    }
    .package-card b{
        font-size:18px;
    }
}
</style>

<div class="pagehead">
    <div>
        <h1>Paket Internet</h1>
        <p>Master paket hanya berisi nama dan kecepatan. Nominal bulanan diatur pada data pelanggan.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">{!! $i('home') !!}Home</a>
        <a class="btn" href="{{ url('/admin/packages/create') }}">{!! $i('plus') !!}Tambah Paket</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="package-summary">
    <div class="package-card">
        <span>Total Paket</span>
        <b>{{ $packages->total() }}</b>
    </div>
    <div class="package-card">
        <span>Halaman Ini</span>
        <b>{{ $packages->count() }}</b>
    </div>
    <div class="package-card">
        <span>Mode Harga</span>
        <b>Per Pelanggan</b>
    </div>
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total halaman ini: <b>{{ $packages->count() }}</b></span>
        <span>Geser kanan untuk aksi</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th class="sticky-left">ID</th>
                    <th>{!! $i('wifi') !!}Nama Paket</th>
                    <th>Kecepatan</th>
                    <th>Status</th>
                    <th>Pelanggan</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($packages as $package)
                    <tr>
                        <td class="neo-id sticky-left">#{{ $package->id }}</td>
                        <td class="neo-strong">{{ $package->name }}</td>
                        <td>{{ $package->speed ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $package->status === 'active' ? 'green' : 'red' }}">
                                {{ $package->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>{{ $package->customers()->count() }}</td>
                        <td>{{ $package->created_at?->format('d/m/Y') ?: '-' }}</td>
                        <td>
                            <div class="package-actions">
                                <a class="btn light icon" title="Edit" aria-label="Edit" href="{{ url('/admin/packages/'.$package->id.'/edit') }}">
                                    {!! $i('edit') !!}
                                </a>

                                <form method="POST" action="{{ url('/admin/packages/'.$package->id) }}" onsubmit="return confirm('Hapus paket ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn red icon" title="Hapus" aria-label="Hapus" type="submit">
                                        {!! $i('trash') !!}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada paket internet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">{{ $packages->links() }}</div>
@endsection
