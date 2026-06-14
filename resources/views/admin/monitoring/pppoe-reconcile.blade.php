@extends('layouts.neo')
@section('title','Rekonsiliasi PPPoE')
@section('content')

<div class="reconcile-note">
    <b>Smart Match Aman:</b> sistem hanya menghubungkan PPPoE username ke pelanggan lama jika nama bersihnya cocok persis dan kandidatnya hanya satu.
    <form method="POST" action="{{ url('/admin/monitoring/pppoe/smart-link') }}" style="margin-top:10px" onsubmit="return confirm('Jalankan Smart Match PPPoE? Hanya match aman yang akan diterapkan.')">
        @csrf
        <button class="btn" type="submit">Jalankan Smart Match Aman</button>
    </form>
</div>

@php
    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
            'wifi' => '<svg viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>',
            'eye' => '<svg viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.reconcile-hero{
    background:linear-gradient(135deg,#0f172a,#1d4ed8);
    border-radius:24px;
    padding:22px;
    color:#fff;
    margin-bottom:12px;
    box-shadow:0 16px 36px rgba(16,24,40,.08);
}
.reconcile-hero span{display:block;color:#dbeafe;font-size:13px;font-weight:800}
.reconcile-hero b{display:block;margin-top:6px;font-size:28px;line-height:1;letter-spacing:-.07em}
.reconcile-hero p{margin:10px 0 0;color:#dbeafe;font-size:14px;line-height:1.45;max-width:820px}
.reconcile-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:12px}
.reconcile-card{background:#fff;border:1px solid #e4eaf3;border-radius:22px;padding:15px;box-shadow:0 10px 24px rgba(16,24,40,.055)}
.reconcile-card .label{color:#667085;font-size:12px;font-weight:850}
.reconcile-card .value{margin-top:7px;color:#101828;font-size:24px;font-weight:950;letter-spacing:-.055em}
.reconcile-card.ok .value{color:#027a48}
.reconcile-card.warn .value{color:#b54708}
.reconcile-card.err .value{color:#b42318}
.reconcile-note{background:#fffaeb;border:1px solid #fedf89;color:#b54708;border-radius:18px;padding:12px;font-size:13px;line-height:1.45;margin-bottom:12px}
.reconcile-search{display:grid;grid-template-columns:1fr auto auto;gap:8px;background:#fff;border:1px solid #e4eaf3;border-radius:22px;padding:10px;margin-bottom:12px;box-shadow:0 10px 24px rgba(16,24,40,.045)}
.reconcile-search input{width:100%;border:1px solid #d9e3f0;border-radius:16px;padding:12px 13px;font-weight:800;color:#101828;background:#fff}
.reconcile-section{margin:16px 0 8px}
.reconcile-section b{display:block;color:#101828;font-size:20px;font-weight:950;letter-spacing:-.055em}
.reconcile-section span{display:block;color:#667085;font-size:13px;font-weight:800;margin-top:3px}
@media(max-width:980px){.reconcile-cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:760px){
    .reconcile-hero{border-radius:22px;padding:18px}
    .reconcile-hero b{font-size:25px}
    .reconcile-cards{gap:8px}
    .reconcile-card{border-radius:18px;padding:12px}
    .reconcile-card .value{font-size:20px}
    .reconcile-search{grid-template-columns:1fr}
    .reconcile-search .btn{width:100%;justify-content:center}
}
</style>

<div class="pagehead">
    <div>
        <h1>Rekonsiliasi PPPoE</h1>
        <p>Cek kecocokan antara PPP Active/Secret Mikrotik dengan data pelanggan di aplikasi.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/monitoring/pppoe') }}">{!! $i('back') !!}Monitoring</a>
        <a class="btn light" href="{{ url('/admin/settings/mikrotik') }}">{!! $i('wifi') !!}Mikrotik</a>
    </div>
</div>

<div class="reconcile-hero">
    <span>Data Matching</span>
    <b>PPP Active ≠ Pelanggan Otomatis</b>
    <p>Active session dari Mikrotik hanya menunjukkan user yang sedang online. Agar dihitung sebagai pelanggan aplikasi, username PPPoE harus tersimpan di data pelanggan dan router-nya cocok.</p>
</div>

<div class="reconcile-note">
    Jika Active Mikrotik 92 tetapi Pelanggan PPPoE hanya 1, berarti 91 user PPPoE belum dihubungkan ke data pelanggan aplikasi. Ini bukan error koneksi; ini masalah pemetaan data.
</div>

<div class="reconcile-cards">
    <div class="reconcile-card">
        <div class="label">Pelanggan PPPoE</div>
        <div class="value">{{ number_format($summary['customers'],0,',','.') }}</div>
    </div>

    <div class="reconcile-card">
        <div class="label">PPP Active Mikrotik</div>
        <div class="value">{{ number_format($summary['active_total'],0,',','.') }}</div>
    </div>

    <div class="reconcile-card ok">
        <div class="label">Active Cocok Pelanggan</div>
        <div class="value">{{ number_format($summary['active_matched'],0,',','.') }}</div>
    </div>

    <div class="reconcile-card err">
        <div class="label">Active Belum Terhubung</div>
        <div class="value">{{ number_format($summary['active_unmatched'],0,',','.') }}</div>
    </div>

    <div class="reconcile-card">
        <div class="label">Secret Mikrotik</div>
        <div class="value">{{ number_format($summary['secret_total'],0,',','.') }}</div>
    </div>

    <div class="reconcile-card ok">
        <div class="label">Secret Cocok Pelanggan</div>
        <div class="value">{{ number_format($summary['secret_matched'],0,',','.') }}</div>
    </div>

    <div class="reconcile-card warn">
        <div class="label">Secret Belum Terhubung</div>
        <div class="value">{{ number_format($summary['secret_unmatched'],0,',','.') }}</div>
    </div>

    <div class="reconcile-card">
        <div class="label">Langkah Lanjut</div>
        <div class="value">Import</div>
    </div>
</div>

<form class="reconcile-search" method="GET" action="{{ url('/admin/monitoring/pppoe/reconcile') }}">
    <input name="search" value="{{ $search }}" placeholder="Cari username, IP, caller ID, profile, komentar">
    <button class="btn" type="submit">{!! $i('search') !!}Cari</button>
    @if($search)
        <a class="btn light" href="{{ url('/admin/monitoring/pppoe/reconcile') }}">Reset</a>
    @endif
</form>

<div class="reconcile-section">
    <b>PPP Active Belum Terhubung ke Pelanggan</b>
    <span>User PPPoE sedang online di Mikrotik, tetapi belum punya pasangan di tabel pelanggan aplikasi.</span>
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total: <b>{{ $activeUnmatched->count() }}</b></span>
        <span>Ini sumber selisih angka dashboard</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th>Router</th>
                    <th>Username</th>
                    <th>Address</th>
                    <th>Caller ID</th>
                    <th>Uptime</th>
                    <th>Last Seen</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeUnmatched as $session)
                    <tr>
                        <td>{{ $session->router?->name ?: '-' }}</td>
                        <td class="neo-strong">{{ $session->name }}</td>
                        <td>{{ $session->address ?: '-' }}</td>
                        <td>{{ $session->caller_id ?: '-' }}</td>
                        <td>{{ $session->uptime ?: '-' }}</td>
                        <td>{{ $session->last_seen_at?->format('d/m/Y H:i') ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Semua active session sudah cocok dengan pelanggan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<div class="reconcile-note">
    Gunakan tombol <b>Import Pelanggan</b> satu per satu. Setelah import, buka detail pelanggan lalu lengkapi ODP, port, alamat, paket, dan harga bulanan.
</div>

<div class="reconcile-section">
    <b>PPP Secret Belum Terhubung ke Pelanggan</b>
    <span>Secret sudah ada di Mikrotik, tetapi belum terhubung ke data pelanggan aplikasi.</span>
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total: <b>{{ $secretUnmatched->count() }}</b></span>
        <span>Calon data yang bisa di-import ke pelanggan</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th>Router</th>
                    <th>Secret Name</th>
                    <th>Profile</th>
                    <th>Remote Address</th>
                    <th>Status</th>
                    <th>Komentar</th>
                    <th>Sync Terakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($secretUnmatched as $secret)
                    <tr>
                        <td>{{ $secret->router?->name ?: '-' }}</td>
                        <td class="neo-strong">{{ $secret->name }}</td>
                        <td>{{ $secret->profile ?: '-' }}</td>
                        <td>{{ $secret->remote_address ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $secret->disabled === 'true' ? 'red' : 'green' }}">
                                {{ $secret->disabled === 'true' ? 'Disabled' : 'Aktif' }}
                            </span>
                        </td>
                        <td class="neo-clip">{{ $secret->comment ?: '-' }}</td>
                        <td>{{ $secret->last_synced_at?->format('d/m/Y H:i') ?: '-' }}</td>
                        <td>
                            <form method="POST" action="{{ url('/admin/monitoring/pppoe/secrets/'.$secret->id.'/import-customer') }}" onsubmit="return confirm('Import Secret ini menjadi pelanggan baru?')">
                                @csrf
                                <button class="btn light" type="submit">Import Pelanggan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">Semua Secret sudah cocok dengan pelanggan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="reconcile-section">
    <b>Data yang Sudah Cocok</b>
    <span>Username PPPoE sudah ditemukan di data pelanggan aplikasi.</span>
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Active cocok: <b>{{ $activeMatched->count() }}</b></span>
        <span>Data ini sudah tersambung ke pelanggan</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th>Router</th>
                    <th>Username</th>
                    <th>Pelanggan</th>
                    <th>Address</th>
                    <th>Uptime</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeMatched as $session)
                    <tr>
                        <td>{{ $session->router?->name ?: '-' }}</td>
                        <td class="neo-strong">{{ $session->name }}</td>
                        <td>{{ $session->matched_customer?->name ?: '-' }}</td>
                        <td>{{ $session->address ?: '-' }}</td>
                        <td>{{ $session->uptime ?: '-' }}</td>
                        <td>
                            @if($session->matched_customer)
                                <a class="btn light icon" href="{{ url('/admin/customers/'.$session->matched_customer->id.'/detail') }}">{!! $i('eye') !!}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada active session yang cocok dengan pelanggan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
