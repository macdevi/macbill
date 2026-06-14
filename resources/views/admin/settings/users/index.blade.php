@extends('layouts.neo')
@section('title','Pegawai')
@section('content')
@php
    $roleLabel = fn($role) => in_array($role, ['collector', 'kasir'], true) ? 'Kasir' : 'Teknisi';

    $i = function ($name) {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>',
            'plus' => '<svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
            'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
            'edit' => '<svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>',
            'trash' => '<svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>',
            'user' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.user-summary{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:10px;
    margin-bottom:10px;
}
.user-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:20px;
    padding:14px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.user-card span{
    display:block;
    color:#667085;
    font-size:12px;
    font-weight:800;
}
.user-card b{
    display:block;
    margin-top:5px;
    color:#101828;
    font-size:22px;
    letter-spacing:-.055em;
}
.user-actions{
    display:flex;
    gap:6px;
    flex-wrap:nowrap;
    align-items:center;
}
.user-actions form{
    margin:0;
}
@media(max-width:760px){
    .user-summary{
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:7px;
    }
    .user-card{
        border-radius:16px;
        padding:10px;
    }
    .user-card span{
        font-size:10px;
    }
    .user-card b{
        font-size:18px;
    }
}
</style>

<div class="pagehead">
    <div>
        <h1>Pegawai</h1>
        <p>Kelola data pegawai untuk akun login kasir dan teknisi.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">{!! $i('home') !!}Home</a>
        <a class="btn" href="{{ url('/admin/settings/users/create') }}">{!! $i('plus') !!}Tambah Pegawai</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<form class="neo-search" method="GET" action="{{ url('/admin/settings/users') }}">
    <span>{!! $i('search') !!}</span>
    <input name="search" value="{{ $search }}" placeholder="Cari nama, username, atau no HP">
    <button class="btn" type="submit">Cari</button>
    @if($search)
        <a class="btn light" href="{{ url('/admin/settings/users') }}">Reset</a>
    @endif
</form>

<div class="user-summary">
    <div class="user-card">
        <span>Total Pegawai</span>
        <b>{{ $users->total() }}</b>
    </div>

    <div class="user-card">
        <span>Kasir</span>
        <b>{{ \App\Models\User::whereIn('role', ['collector','kasir'])->count() }}</b>
    </div>

    <div class="user-card">
        <span>Teknisi</span>
        <b>{{ \App\Models\User::whereIn('role', ['technician','teknisi'])->count() }}</b>
    </div>
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total halaman ini: <b>{{ $users->count() }}</b></span>
        <span>Geser kanan untuk aksi</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th class="sticky-left">ID</th>
                    <th>{!! $i('user') !!}Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>No HP</th>
                    <th>Alamat</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="neo-id sticky-left">#{{ $user->id }}</td>
                        <td class="neo-strong">{{ $user->name }}</td>
                        <td>{{ $user->username ?: '-' }}</td>
                        <td><span class="badge blue">{{ $roleLabel($user->role) }}</span></td>
                        <td>{{ $user->phone ?: '-' }}</td>
                        <td class="neo-clip">{{ $user->address ?: '-' }}</td>
                        <td>
                            <span class="badge {{ ($user->status ?? 'active') === 'active' ? 'green' : 'red' }}">
                                {{ ($user->status ?? 'active') === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>{{ $user->created_at?->format('d/m/Y') ?: '-' }}</td>
                        <td>
                            <div class="user-actions">
                                <a class="btn light icon" title="Edit" href="{{ url('/admin/settings/users/'.$user->id.'/edit') }}">
                                    {!! $i('edit') !!}
                                </a>

                                <form method="POST" action="{{ url('/admin/settings/users/'.$user->id) }}" onsubmit="return confirm('Hapus user login ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn red icon" title="Hapus" type="submit">{!! $i('trash') !!}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">Belum ada pegawai kasir/teknisi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">{{ $users->links() }}</div>
@endsection
