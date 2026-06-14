@extends('layouts.neo')
@section('title','Reset Data Pelanggan')
@section('content')
@php
    $before = session('before_counts', []);
@endphp

<style>
.reset-wrap{
    display:grid;
    gap:14px;
}
.reset-warning{
    background:linear-gradient(135deg,#b42318,#dc2626);
    border-radius:26px;
    color:#fff;
    padding:22px;
    box-shadow:0 18px 44px rgba(180,35,24,.16);
}
.reset-warning span{
    display:block;
    color:#fee4e2;
    font-size:13px;
    font-weight:800;
}
.reset-warning b{
    display:block;
    margin-top:7px;
    font-size:28px;
    letter-spacing:-.06em;
}
.reset-warning p{
    margin:10px 0 0;
    color:#fff1f0;
    line-height:1.45;
    font-weight:700;
}
.reset-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:10px;
}
.reset-stat{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:22px;
    padding:15px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.reset-stat .label{
    color:#667085;
    font-size:12px;
    font-weight:800;
}
.reset-stat .val{
    margin-top:7px;
    color:#101828;
    font-size:26px;
    font-weight:950;
    letter-spacing:-.055em;
}
.reset-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:24px;
    padding:18px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.reset-list{
    margin:0;
    padding-left:18px;
    color:#475467;
    line-height:1.7;
    font-weight:700;
}
.reset-list b{
    color:#101828;
}
.reset-confirm{
    margin-top:14px;
}
.reset-confirm label{
    display:block;
    color:#344054;
    font-size:13px;
    font-weight:900;
    margin-bottom:7px;
}
.reset-actions{
    display:flex;
    justify-content:flex-end;
    gap:8px;
    margin-top:12px;
}
.btn.danger{
    background:#dc2626;
    border-color:#dc2626;
    color:#fff;
}
.btn.danger:hover{
    filter:brightness(.96);
}
.reset-backup{
    background:#ecfdf3;
    border:1px solid #abefc6;
    color:#027a48;
    border-radius:18px;
    padding:12px;
    font-weight:800;
    word-break:break-word;
}
@media(max-width:720px){
    .reset-grid{grid-template-columns:1fr}
    .reset-warning{border-radius:22px;padding:18px}
    .reset-warning b{font-size:24px}
    .reset-actions{display:block}
    .reset-actions .btn{width:100%;justify-content:center;margin-top:8px}
}
</style>

<div class="pagehead">
    <div>
        <h1>Reset Data Pelanggan</h1>
        <p>Menu admin untuk membersihkan data pelanggan, invoice, dan riwayat pembayaran.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">Dashboard</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif

@if(session('backup_file'))
    <div class="reset-backup">
        Backup database dibuat sebelum reset:<br>
        {{ session('backup_file') }}
    </div>
@endif

<div class="reset-wrap">
    <div class="reset-warning">
        <span>PERINGATAN</span>
        <b>Reset ini menghapus data pelanggan.</b>
        <p>Gunakan hanya jika ingin mengosongkan data pelanggan dan memulai ulang data operasional pelanggan dari awal.</p>
    </div>

    <div class="reset-grid">
        <div class="reset-stat">
            <div class="label">Pelanggan</div>
            <div class="val">{{ $counts['customers'] ?? 0 }}</div>
        </div>

        <div class="reset-stat">
            <div class="label">Invoice</div>
            <div class="val">{{ $counts['invoices'] ?? 0 }}</div>
        </div>

        <div class="reset-stat">
            <div class="label">Riwayat Pembayaran</div>
            <div class="val">{{ $counts['payments'] ?? 0 }}</div>
        </div>
    </div>

    <div class="reset-card">
        <h3 style="margin:0 0 10px;color:#101828;letter-spacing:-.04em">Data yang akan dihapus</h3>

        <ul class="reset-list">
            <li><b>Semua pelanggan</b> di admin dan kasir.</li>
            <li><b>Semua invoice/tagihan</b>, termasuk Belum Bayar, Nunggak, Bayar Awal, dan Lunas.</li>
            <li><b>Semua riwayat pembayaran dan nota pembayaran</b>.</li>
        </ul>

        <h3 style="margin:18px 0 10px;color:#101828;letter-spacing:-.04em">Data yang tidak dihapus</h3>

        <ul class="reset-list">
            <li>Paket internet.</li>
            <li>ODP dan data jaringan.</li>
            <li>User/pegawai/login.</li>
            <li>Pengaturan, Mikrotik, dan OLT.</li>
            <li>Pengeluaran admin/kasir.</li>
        </ul>

        <form method="POST" action="{{ url('/admin/settings/reset-data') }}" onsubmit="return confirm('Yakin reset data pelanggan? Backup akan dibuat, lalu customer, invoice, dan pembayaran akan dihapus.');">
            @csrf

            <div class="reset-confirm">
                <label>Ketik RESET PELANGGAN untuk melanjutkan</label>
                <input class="input" name="confirmation" value="{{ old('confirmation') }}" placeholder="RESET PELANGGAN" autocomplete="off">
            </div>

            <div class="reset-actions">
                <a class="btn light" href="{{ url('/admin/dashboard') }}">Batal</a>
                <button class="btn danger" type="submit">Reset Data Pelanggan</button>
            </div>
        </form>
    </div>
</div>
@endsection
