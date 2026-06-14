@extends('layouts.neo')
@section('title', $user->exists ? 'Edit Pegawai' : 'Tambah Pegawai Login')
@section('content')
@php
    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'save' => '<svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>',
            'user' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
            'lock' => '<svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.user-form-layout{
    display:grid;
    grid-template-columns:1fr 320px;
    gap:12px;
    align-items:start;
}
.user-form-card,
.user-side-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:24px;
    padding:18px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.user-side-card{
    position:sticky;
    top:82px;
}
.user-section-title{
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:12px;
    color:#101828;
    font-size:15px;
    font-weight:950;
    letter-spacing:-.035em;
}
.user-note{
    background:#eff6ff;
    border:1px solid #b2ddff;
    color:#175cd3;
    border-radius:18px;
    padding:12px;
    font-size:13px;
    line-height:1.45;
    margin-bottom:14px;
}
.user-form-actions{
    display:flex;
    justify-content:flex-end;
    gap:8px;
    margin-top:14px;
}
.user-side-card b{
    display:block;
    color:#101828;
    font-size:15px;
    letter-spacing:-.035em;
}
.user-side-card p{
    margin:7px 0 0;
    color:#667085;
    font-size:12px;
    line-height:1.45;
}
@media(max-width:980px){
    .user-form-layout{
        grid-template-columns:1fr;
    }
    .user-side-card{
        position:static;
    }
}
@media(max-width:760px){
    .user-form-card,
    .user-side-card{
        border-radius:20px;
        padding:14px;
    }
    .user-form-actions{
        flex-direction:column;
    }
    .user-form-actions .btn{
        width:100%;
        justify-content:center;
    }
}
</style>

<div class="pagehead">
    <div>
        <h1>{{ $user->exists ? 'Edit Pegawai' : 'Tambah Pegawai Login' }}</h1>
        <p>Tambah akun kasir atau teknisi untuk login menggunakan username dan password.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/settings/users') }}">{!! $i('back') !!}Kembali</a>
    </div>
</div>

@if($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif

<div class="user-form-layout">
    <form class="user-form-card" method="POST" action="{{ $user->exists ? url('/admin/settings/users/'.$user->id) : url('/admin/settings/users') }}">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div class="user-note">
            <b>Catatan:</b> username dipakai untuk login. Password minimal 6 karakter. Untuk edit user, kosongkan password jika tidak ingin mengganti password.
        </div>

        <div class="user-section-title">{!! $i('user') !!}Data Pegawai</div>

        <div class="formgrid">
            <div class="field">
                <label>Nama Lengkap</label>
                <input class="input" name="name" value="{{ old('name', $user->name) }}" placeholder="Nama lengkap" required>
            </div>

            <div class="field">
                <label>Username</label>
                <input class="input" name="username" value="{{ old('username', $user->username) }}" placeholder="contoh: kasir01" required>
            </div>

            <div class="field">
                <label>No HP</label>
                <input class="input" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
            </div>

            <div class="field">
                <label>Role Login</label>
                <select class="select" name="role" required>
                    <option value="collector" @selected(old('role', $user->role ?: 'collector') === 'collector')>Kasir</option>
                    <option value="technician" @selected(old('role', $user->role) === 'technician')>Teknisi</option>
                </select>
            </div>

            <div class="field">
                <label>Status</label>
                <select class="select" name="status" required>
                    <option value="active" @selected(old('status', $user->status ?: 'active') === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Nonaktif</option>
                </select>
            </div>

            <div class="field">
                <label>Password</label>
                <input class="input" type="password" name="password" placeholder="{{ $user->exists ? 'Kosongkan jika tidak diganti' : 'Minimal 6 karakter' }}" {{ $user->exists ? '' : 'required' }}>
            </div>

            <div class="field full">
                <label>Alamat</label>
                <textarea class="textarea" name="address" placeholder="Alamat lengkap user">{{ old('address', $user->address) }}</textarea>
            </div>
        </div>

        <div class="user-form-actions">
            <a class="btn light" href="{{ url('/admin/settings/users') }}">Batal</a>
            <button class="btn" type="submit">{!! $i('save') !!}Simpan Pegawai</button>
        </div>
    </form>

    <aside class="user-side-card">
        <b>Akses Login</b>
        <p>
            Kasir login melalui <b>/collector/login</b>.
            Teknisi login melalui <b>/technician/login</b>.
        </p>

        <div style="margin-top:12px;display:grid;gap:8px">
            <div class="user-note" style="margin:0">
                Role <b>Kasir</b> dipakai untuk menerima pembayaran dan mencatat pengeluaran.
            </div>

            <div class="user-note" style="margin:0">
                Role <b>Teknisi</b> dipakai untuk akses pekerjaan teknis dan monitoring lapangan.
            </div>
        </div>
    </aside>
</div>
@endsection
