@extends('layouts.neo')
@section('title', $router->exists ? 'Edit Mikrotik' : 'Tambah Mikrotik')
@section('content')
@php
    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'save' => '<svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>',
            'router' => '<svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="8" rx="2"/><path d="M7 15h.01"/><path d="M11 15h.01"/><path d="M15 15h2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',
            'lock' => '<svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.mikrotik-form-layout{
    display:grid;
    grid-template-columns:1fr 320px;
    gap:12px;
    align-items:start;
}
.mikrotik-form-card,
.mikrotik-side-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:24px;
    padding:18px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.mikrotik-side-card{
    position:sticky;
    top:82px;
}
.mikrotik-section-title{
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:12px;
    color:#101828;
    font-size:15px;
    font-weight:950;
    letter-spacing:-.035em;
}
.mikrotik-note{
    background:#eff6ff;
    border:1px solid #b2ddff;
    color:#175cd3;
    border-radius:18px;
    padding:12px;
    font-size:13px;
    line-height:1.45;
    margin-bottom:14px;
}
.mikrotik-warning{
    background:#fffaeb;
    border:1px solid #fedf89;
    color:#b54708;
    border-radius:18px;
    padding:12px;
    font-size:13px;
    line-height:1.45;
    margin-bottom:14px;
}
.mikrotik-form-actions{
    display:flex;
    justify-content:flex-end;
    gap:8px;
    margin-top:14px;
}
.mikrotik-side-card b{
    display:block;
    color:#101828;
    font-size:15px;
    letter-spacing:-.035em;
}
.mikrotik-side-card p{
    margin:7px 0 0;
    color:#667085;
    font-size:12px;
    line-height:1.45;
}
.mikrotik-side-list{
    margin-top:12px;
    display:grid;
    gap:8px;
}
.mikrotik-side-item{
    border:1px solid #eef2f7;
    background:#f8fbff;
    border-radius:14px;
    padding:10px;
    color:#667085;
    font-size:12px;
    line-height:1.4;
}
.mikrotik-side-item strong{
    color:#101828;
}
@media(max-width:980px){
    .mikrotik-form-layout{
        grid-template-columns:1fr;
    }
    .mikrotik-side-card{
        position:static;
    }
}
@media(max-width:760px){
    .mikrotik-form-card,
    .mikrotik-side-card{
        border-radius:20px;
        padding:14px;
    }
    .mikrotik-form-actions{
        flex-direction:column;
    }
    .mikrotik-form-actions .btn{
        width:100%;
        justify-content:center;
    }
}
</style>

<div class="pagehead">
    <div>
        <h1>{{ $router->exists ? 'Edit Mikrotik' : 'Tambah Mikrotik' }}</h1>
        <p>Simpan konfigurasi koneksi router Mikrotik untuk tahap integrasi berikutnya.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/settings/mikrotik') }}">{!! $i('back') !!}Kembali</a>
    </div>
</div>

@if($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif

<div class="mikrotik-form-layout">
    <form class="mikrotik-form-card" method="POST" action="{{ $router->exists ? url('/admin/settings/mikrotik/'.$router->id) : url('/admin/settings/mikrotik') }}">
        @csrf
        @if($router->exists) @method('PUT') @endif

        <div class="mikrotik-note">
            Password API disimpan terenkripsi. Saat edit, kosongkan password jika tidak ingin mengganti password lama.
        </div>

        <div class="mikrotik-section-title">{!! $i('router') !!}Data Router</div>

        <div class="formgrid">
            <div class="field">
                <label>Nama Koneksi</label>
                <input class="input" name="name" value="{{ old('name', $router->name) }}" placeholder="Contoh: Mikrotik Utama" required>
            </div>

            <div class="field">
                <label>Host / IP Mikrotik</label>
                <input class="input" name="host" value="{{ old('host', $router->host) }}" placeholder="192.168.88.1 atau domain" required>
            </div>

            <div class="field">
                <label>Port API</label>
                <input class="input" type="number" min="1" max="65535" name="api_port" value="{{ old('api_port', $router->api_port ?: 8728) }}" required>
            </div>

            <div class="field">
                <label>Username API</label>
                <input class="input" name="username" value="{{ old('username', $router->username) }}" placeholder="admin-api" required>
            </div>

            <div class="field">
                <label>Password API</label>
                <input class="input" type="password" name="api_password" placeholder="{{ $router->exists ? 'Kosongkan jika tidak diganti' : 'Password API Mikrotik' }}" {{ $router->exists ? '' : 'required' }}>
            </div>

            <div class="field">
                <label>Pakai SSL</label>
                <select class="select" name="use_ssl" required>
                    <option value="0" @selected((string)old('use_ssl', $router->use_ssl ? '1' : '0') === '0')>Tidak / API biasa</option>
                    <option value="1" @selected((string)old('use_ssl', $router->use_ssl ? '1' : '0') === '1')>Ya / API SSL</option>
                </select>
            </div>

            <div class="field">
                <label>Status</label>
                <select class="select" name="status" required>
                    <option value="active" @selected(old('status', $router->status ?: 'active') === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status', $router->status) === 'inactive')>Nonaktif</option>
                </select>
            </div>

            <div class="field full">
                <label>Catatan</label>
                <textarea class="textarea" name="notes" placeholder="Contoh: router utama untuk PPPoE pelanggan area pusat">{{ old('notes', $router->notes) }}</textarea>
            </div>
        </div>

        <div class="mikrotik-form-actions">
            <a class="btn light" href="{{ url('/admin/settings/mikrotik') }}">Batal</a>
            <button class="btn" type="submit">{!! $i('save') !!}Simpan Mikrotik</button>
        </div>
    </form>

    <aside class="mikrotik-side-card">
        <div class="mikrotik-warning" style="margin-top:12px;margin-bottom:0">
            Jangan gunakan user Mikrotik utama jika tidak perlu. Lebih aman buat user API khusus dengan hak akses terbatas.
        </div>
    </aside>
</div>

<style>
/* MAC BILLING SAFE MIKROTIK UI - CSS ONLY */
/* Tidak ada display:none, tidak ada collapse, tidak menghapus data. */

.pagehead{
    align-items:flex-start;
    gap:14px;
}

.pagehead h1,
.pagehead .title,
.pagehead b{
    letter-spacing:-.03em;
}

.pagehead .neo-actions{
    gap:8px;
    flex-wrap:wrap;
}

.mikrotik-hero{
    border-radius:24px !important;
    padding:22px !important;
    background:linear-gradient(135deg,#0f172a,#1d4ed8) !important;
    box-shadow:0 18px 45px rgba(15,23,42,.18) !important;
}

.mikrotik-hero b{
    font-size:26px !important;
    line-height:1.15 !important;
    letter-spacing:-.04em !important;
}

.mikrotik-hero p,
.mikrotik-hero span{
    color:rgba(255,255,255,.78) !important;
}

.mikrotik-note{
    border-radius:18px !important;
    border:1px solid #e2e8f0 !important;
    background:#f8fafc !important;
    color:#475569 !important;
    box-shadow:0 8px 18px rgba(15,23,42,.035) !important;
}

.mikrotik-grid{
    gap:12px !important;
}

.mikrotik-card{
    border-radius:20px !important;
    border:1px solid #e5e7eb !important;
    background:#fff !important;
    box-shadow:0 10px 24px rgba(15,23,42,.055) !important;
}

.mikrotik-card .label{
    color:#64748b !important;
    font-size:12px !important;
    font-weight:800 !important;
}

.mikrotik-card .value{
    color:#0f172a !important;
    font-size:24px !important;
    font-weight:900 !important;
    letter-spacing:-.04em !important;
}

.neo-search{
    border:1px solid #e5e7eb !important;
    border-radius:18px !important;
    background:#fff !important;
    padding:10px !important;
    box-shadow:0 10px 24px rgba(15,23,42,.04) !important;
}

.neo-search input,
.neo-search select,
.neo-search .input,
.neo-search .select{
    border-radius:13px !important;
}

.neo-xls{
    border-radius:22px !important;
    border:1px solid #e5e7eb !important;
    background:#fff !important;
    overflow:hidden !important;
    box-shadow:0 12px 30px rgba(15,23,42,.055) !important;
}

.neo-xls-info{
    background:#f8fafc !important;
    border-bottom:1px solid #e5e7eb !important;
}

.neo-xls-table{
    border-collapse:separate !important;
    border-spacing:0 !important;
}

.neo-xls-table th{
    background:#f8fafc !important;
    color:#475569 !important;
    font-size:12px !important;
    font-weight:900 !important;
    white-space:nowrap !important;
}

.neo-xls-table td{
    vertical-align:middle !important;
}

.neo-xls-table tbody tr:hover{
    background:#f8fafc !important;
}

.btn{
    border-radius:12px !important;
    font-weight:850 !important;
}

.btn.primary,
button.btn.primary,
a.btn.primary{
    box-shadow:0 10px 20px rgba(37,99,235,.16) !important;
}

.neo-xls-table .btn,
.mikrotik-actions .btn,
td .btn{
    min-height:34px !important;
    padding:0 10px !important;
    font-size:12px !important;
    line-height:1 !important;
}

.mikrotik-actions{
    gap:6px !important;
    flex-wrap:wrap !important;
}

.mikrotik-actions form{
    margin:0 !important;
}

.badge,
.pill,
.status{
    border-radius:999px !important;
    font-weight:850 !important;
}

/* Form tambah/edit Mikrotik */
.mikrotik-form-layout{
    gap:16px !important;
}

.mikrotik-form-card,
.mikrotik-side-card,
.card{
    border-radius:22px !important;
    border:1px solid #e5e7eb !important;
    background:#fff !important;
    box-shadow:0 12px 30px rgba(15,23,42,.055) !important;
}

.mikrotik-section-title{
    color:#0f172a !important;
    letter-spacing:-.02em !important;
}

.field label{
    color:#475569 !important;
    font-size:12px !important;
    font-weight:900 !important;
}

.input,
.select,
.textarea,
input[type="text"],
input[type="number"],
input[type="password"],
input[type="email"],
select,
textarea{
    border-radius:14px !important;
    border:1px solid #dbe3ef !important;
    background:#fff !important;
}

.input:focus,
.select:focus,
.textarea:focus,
input:focus,
select:focus,
textarea:focus{
    border-color:#2563eb !important;
    box-shadow:0 0 0 4px rgba(37,99,235,.10) !important;
    outline:none !important;
}

.mikrotik-warning{
    border-radius:18px !important;
    border:1px solid #fde68a !important;
    background:#fffbeb !important;
}

.mikrotik-side-item{
    border-radius:16px !important;
    border-color:#e5e7eb !important;
    background:#f8fafc !important;
}

.mikrotik-form-actions{
    gap:8px !important;
    flex-wrap:wrap !important;
}

@media(max-width:760px){
    .pagehead .neo-actions,
    .mikrotik-form-actions{
        width:100% !important;
    }

    .pagehead .neo-actions .btn,
    .mikrotik-form-actions .btn{
        flex:1 1 auto !important;
        justify-content:center !important;
    }

    .mikrotik-hero{
        border-radius:20px !important;
        padding:18px !important;
    }

    .mikrotik-hero b{
        font-size:22px !important;
    }

    .mikrotik-grid{
        grid-template-columns:repeat(2,minmax(0,1fr)) !important;
        gap:8px !important;
    }

    .mikrotik-card{
        border-radius:17px !important;
        padding:13px !important;
    }

    .mikrotik-card .value{
        font-size:20px !important;
    }

    .neo-xls{
        border-radius:18px !important;
    }

    .neo-xls-scroll{
        overflow-x:auto !important;
        -webkit-overflow-scrolling:touch !important;
    }

    .neo-xls-table{
        min-width:900px !important;
    }

    .mikrotik-form-layout{
        grid-template-columns:1fr !important;
    }
}
</style>

@endsection
