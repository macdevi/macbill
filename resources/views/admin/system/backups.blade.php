@extends('layouts.neo')
@section('title','Backup Database')
@section('content')
@php
    $i = function ($name) {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>',
            'backup' => '<svg viewBox="0 0 24 24"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>',
            'download' => '<svg viewBox="0 0 24 24"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>',
            'trash' => '<svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>',
            'server' => '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="6" rx="2"/><rect x="3" y="14" width="18" height="6" rx="2"/><path d="M7 7h.01"/><path d="M7 17h.01"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.backup-hero{
    background:linear-gradient(135deg,#0f172a,#1d4ed8);
    border-radius:24px;
    padding:22px;
    color:#fff;
    margin-bottom:12px;
    box-shadow:0 16px 36px rgba(16,24,40,.08);
}
.backup-hero span{display:block;color:#dbeafe;font-size:13px;font-weight:800}
.backup-hero b{display:block;margin-top:6px;font-size:28px;line-height:1;letter-spacing:-.07em}
.backup-hero p{margin:10px 0 0;color:#dbeafe;font-size:14px;line-height:1.45;max-width:820px}
.backup-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:12px}
.backup-card{background:#fff;border:1px solid #e4eaf3;border-radius:22px;padding:15px;box-shadow:0 10px 24px rgba(16,24,40,.055)}
.backup-card .label{color:#667085;font-size:12px;font-weight:850}
.backup-card .value{margin-top:7px;color:#101828;font-size:21px;font-weight:950;letter-spacing:-.055em;word-break:break-word}
.backup-actions{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.backup-actions form{margin:0}
.backup-note{background:#eff8ff;border:1px solid #b2ddff;color:#175cd3;border-radius:18px;padding:12px;font-size:13px;line-height:1.45;margin-bottom:12px}
.backup-row-actions{display:flex;gap:6px;align-items:center;flex-wrap:nowrap}
.backup-row-actions form{margin:0}
@media(max-width:980px){.backup-grid{grid-template-columns:1fr}}
@media(max-width:760px){
    .backup-hero{border-radius:22px;padding:18px}
    .backup-hero b{font-size:25px}
    .backup-card{border-radius:18px;padding:12px}
    .backup-actions .btn{width:100%;justify-content:center}
}
</style>

<div class="pagehead">
    <div>
        <h1>Backup Database</h1>
        <p>Buat dan download salinan database SQLite aplikasi.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">{!! $i('home') !!}Dashboard</a>
        <a class="btn light" href="{{ url('/admin/system/health') }}">{!! $i('server') !!}Status Sistem</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="backup-hero">
    <span>Database Safety</span>
    <b>Backup Database Manual</b>
    <p>Backup ini menyimpan salinan file database SQLite. Gunakan sebelum patch besar, migrasi data, atau perubahan fitur penting.</p>
</div>

<div class="backup-note">
    File backup berisi data aplikasi. Simpan di tempat aman. Jangan bagikan file backup ke pihak yang tidak berwenang.
</div>

<div class="backup-grid">
    <div class="backup-card">
        <div class="label">Status Database</div>
        <div class="value">
            <span class="badge {{ $dbInfo['exists'] ? 'green' : 'red' }}">
                {{ $dbInfo['exists'] ? 'Ada' : 'Tidak Ada' }}
            </span>
        </div>
    </div>

    <div class="backup-card">
        <div class="label">Ukuran Database</div>
        <div class="value">{{ $dbInfo['size'] }}</div>
    </div>

    <div class="backup-card">
        <div class="label">Update Database</div>
        <div class="value" style="font-size:16px">{{ $dbInfo['modified_at'] }}</div>
    </div>
</div>

<div class="backup-actions">
    <form method="POST" action="{{ url('/admin/system/backups') }}" onsubmit="return confirm('Buat backup database sekarang?')">
        @csrf
        <button class="btn" type="submit">{!! $i('backup') !!}Buat Backup Sekarang</button>
    </form>
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total backup: <b>{{ $files->count() }}</b></span>
        <span>Backup terbaru berada di baris atas</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th>Nama File</th>
                    <th>Ukuran</th>
                    <th>Dibuat / Diubah</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($files as $file)
                    <tr>
                        <td class="neo-strong">{{ $file['name'] }}</td>
                        <td>{{ $file['size'] }}</td>
                        <td>{{ $file['modified_at'] }}</td>
                        <td>
                            <div class="backup-row-actions">
                                <a class="btn light icon" title="Download" href="{{ url('/admin/system/backups/'.$file['name'].'/download') }}">
                                    {!! $i('download') !!}
                                </a>
<form method="POST" action="{{ url('/admin/system/backups/'.$file['name']) }}" onsubmit="return confirm('Hapus backup ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn red icon" title="Hapus" type="submit">{!! $i('trash') !!}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Belum ada file backup. Klik tombol Buat Backup Sekarang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
