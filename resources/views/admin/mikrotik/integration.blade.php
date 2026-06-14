@extends('layouts.neo')

@section('title','Integrasi Mikrotik')

@section('content')
@include('admin.mikrotik._style')

@php
    $isEdit = !empty($editRouter);
    $formAction = $isEdit
        ? url('/admin/mikrotik/router/'.$editRouter->id)
        : url('/admin/mikrotik/integrasi');

    $statusValue = old('status', $editRouter->status ?? 'active');
@endphp

<div class="mkt-wrap">
    <div class="mkt-card">
        <div class="mkt-head">
            <div class="mkt-title">
                <b>Router Terdaftar</b>
                <span>Daftar koneksi Mikrotik yang tersimpan. Pesan test dibuat ringkas.</span>
            </div>

            <a class="mkt-btn light" href="{{ url('/admin/mikrotik/integrasi?new=1') }}">Tambah Baru</a>
        </div>

        <div class="mkt-table-wrap">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Host</th>
                        <th>Port</th>
                        <th>Status</th>
                        <th>Pesan Test</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($routers as $router)
                        @php
                            $msg = (string) ($router->last_test_message ?? '-');

                            if (preg_match('/PPPoE\s+Active\s*:\s*(\d+)/i', $msg, $m)) {
                                $msg = 'PPPoE Active: '.$m[1];
                            } elseif (preg_match('/(\d+)\s*sesi/i', $msg, $m)) {
                                $msg = 'PPPoE Active: '.$m[1];
                            } elseif (strlen($msg) > 24) {
                                $msg = $router->last_test_status === 'failed' ? 'Gagal koneksi' : 'Koneksi OK';
                            }
                        @endphp

                        <tr>
                            <td>{{ $router->name }}</td>
                            <td>{{ $router->host }}</td>
                            <td>{{ $router->api_port ?? 8728 }}</td>
                            <td>
                                <span class="mkt-pill {{ ($router->status ?? '') === 'active' ? 'green' : '' }}">
                                    {{ ($router->status ?? '') === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>{{ $msg }}</td>
                            <td>
                                <div class="mkt-actions" style="margin-top:0">
                                    <form method="POST" action="{{ url('/admin/mikrotik/router/'.$router->id.'/test') }}">
                                        @csrf
                                        <button class="mkt-btn light" type="submit">Test</button>
                                    </form>

                                    <a class="mkt-btn light" href="{{ url('/admin/mikrotik/integrasi?edit='.$router->id) }}">Edit</a>

                                    <form method="POST" action="{{ url('/admin/mikrotik/router/'.$router->id) }}" onsubmit="return confirm('Hapus integrasi Mikrotik ini? Data pelanggan tidak dihapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="mkt-btn light" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Belum ada router Mikrotik.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mkt-body">
            {{ $routers->links() }}
        </div>
    </div>

    <div class="mkt-card">
        <div class="mkt-head">
            <div class="mkt-title">
                <b>{{ $isEdit ? 'Edit Integrasi Mikrotik' : 'Tambah Integrasi Mikrotik' }}</b>
                <span>
                    {{ $isEdit ? 'Form otomatis berisi data Mikrotik yang sudah terhubung.' : 'Isi data koneksi Mikrotik baru.' }}
                </span>
            </div>
        </div>

        <div class="mkt-body">
            <form method="POST" action="{{ $formAction }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="mkt-grid">
                    <div class="mkt-field">
                        <label>Nama Router</label>
                        <input type="text" name="name" value="{{ old('name', $editRouter->name ?? '') }}" placeholder="Contoh: Router Utama" required>
                    </div>

                    <div class="mkt-field">
                        <label>Host / IP</label>
                        <input type="text" name="host" value="{{ old('host', $editRouter->host ?? '') }}" placeholder="192.168.88.1" required>
                    </div>

                    <div class="mkt-field">
                        <label>API Port</label>
                        <input type="number" name="api_port" value="{{ old('api_port', $editRouter->api_port ?? 8728) }}" required>
                    </div>

                    <div class="mkt-field">
                        <label>Username API</label>
                        <input type="text" name="username" value="{{ old('username', $editRouter->username ?? '') }}" required>
                    </div>

                    <div class="mkt-field">
                        <label>Password API</label>
                        <input type="password" name="api_password" value="{{ old('api_password', $editRouter->api_password ?? '') }}" {{ $isEdit ? '' : 'required' }}>
                    </div>

                    <div class="mkt-field">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" @selected($statusValue === 'active')>Aktif</option>
                            <option value="inactive" @selected($statusValue === 'inactive')>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="mkt-actions">
                    <button class="mkt-btn primary" type="submit">
                        {{ $isEdit ? 'Update Integrasi' : 'Simpan Integrasi' }}
                    </button>

                    @if($isEdit)
                        <a class="mkt-btn light" href="{{ url('/admin/mikrotik/integrasi?new=1') }}">Kosongkan Form</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
