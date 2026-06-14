@extends('layouts.neo')
@section('title', $package->exists ? 'Edit Paket Internet' : 'Tambah Paket Internet')
@section('content')
@php
    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'save' => '<svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>',
            'wifi' => '<svg viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.package-form-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:24px;
    padding:18px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.package-form-note{
    background:#eff6ff;
    border:1px solid #b2ddff;
    color:#175cd3;
    border-radius:18px;
    padding:12px;
    font-size:13px;
    line-height:1.45;
    margin-bottom:14px;
}
.package-form-actions{
    display:flex;
    justify-content:flex-end;
    gap:8px;
    margin-top:14px;
}
@media(max-width:760px){
    .package-form-card{
        border-radius:20px;
        padding:14px;
    }
    .package-form-actions{
        flex-direction:column;
    }
    .package-form-actions .btn{
        width:100%;
        justify-content:center;
    }
}
</style>

<div class="pagehead">
    <div>
        <h1>{{ $package->exists ? 'Edit Paket Internet' : 'Tambah Paket Internet' }}</h1>
        <p>Isi nama paket dan kecepatan. Harga bulanan tetap diatur pada data pelanggan.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/packages') }}">{!! $i('back') !!}Kembali</a>
    </div>
</div>

@if($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif

<form class="package-form-card" method="POST" action="{{ $package->exists ? url('/admin/packages/'.$package->id) : url('/admin/packages') }}">
    @csrf
    @if($package->exists) @method('PUT') @endif

    <div class="package-form-note">
        <b>Catatan:</b> paket internet hanya sebagai master layanan. Nominal tagihan per bulan tidak diisi di sini, tetapi di data pelanggan.
    </div>

    <div class="formgrid">
        <div class="field">
            <label>Nama Paket</label>
            <input class="input" name="name" value="{{ old('name', $package->name) }}" placeholder="Contoh: Silver 10 Mbps" required>
        </div>

        <div class="field">
            <label>Kecepatan</label>
            <input class="input" name="speed" value="{{ old('speed', $package->speed) }}" placeholder="Contoh: Up to 10 Mbps">
        </div>

        <div class="field">
            <label>Status</label>
            <select class="select" name="status">
                <option value="active" @selected(old('status', $package->status ?: 'active') === 'active')>Aktif</option>
                <option value="inactive" @selected(old('status', $package->status) === 'inactive')>Nonaktif</option>
            </select>
        </div>
    </div>

    <div class="package-form-actions">
        <a class="btn light" href="{{ url('/admin/packages') }}">Batal</a>
        <button class="btn" type="submit">{!! $i('save') !!}Simpan Paket</button>
    </div>
</form>
@endsection
