@extends('layouts.neo')
@section('title','Reset Data Pelanggan')
@section('content')
@php
    $i = fn($x) => '<span class="neo-mini-ico"><svg viewBox="0 0 24 24">'.[
        'back'=>'<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
        'trash'=>'<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 15h10l1-15"/><path d="M10 11v6"/><path d="M14 11v6"/>',
    ][$x].'</svg></span>';
@endphp

<style>
.reset-box{
    max-width:760px;
}
.reset-warning{
    border-radius:24px;
    padding:24px;
    background:#fff7ed;
    border:1px solid #fed7aa;
    color:#9a3412;
    margin-bottom:18px;
}
.reset-stats{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:12px;
    margin:18px 0;
}
.reset-stat{
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:18px;
    padding:18px;
}
.reset-stat b{
    display:block;
    font-size:26px;
    letter-spacing:-.04em;
}
.reset-stat span{
    color:#64748b;
    font-weight:700;
    font-size:13px;
}
.reset-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-top:20px;
}
.reset-danger{
    background:#dc2626 !important;
    border-color:#dc2626 !important;
    color:#fff !important;
}
@media(max-width:760px){
    .reset-stats{
        grid-template-columns:1fr;
    }
    .reset-actions .btn{
        width:100%;
        justify-content:center;
    }
}
</style>

<div class="pagehead">
    <div>
        <h1>Reset Data Pelanggan</h1>
        <p>Hapus data pelanggan beserta tagihan dan pembayaran terkait.</p>
    </div>
    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">{!! $i('back') !!}Kembali</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="card reset-box">
    <div class="reset-warning">
        <b>Perhatian.</b><br>
        Proses ini akan menghapus data pelanggan, invoice, pembayaran, dan data turunan yang terkait dengan pelanggan. Data paket, ODP, port ODP, dan user login tidak dihapus.
    </div>

    <div class="reset-stats">
        <div class="reset-stat">
            <b>{{ $customerCount }}</b>
            <span>Pelanggan</span>
        </div>
        <div class="reset-stat">
            <b>{{ $invoiceCount }}</b>
            <span>Invoice</span>
        </div>
        <div class="reset-stat">
            <b>{{ $paymentCount }}</b>
            <span>Pembayaran</span>
        </div>
    </div>

    <form method="POST" action="{{ url()->current() }}" onsubmit="return confirm('Yakin reset semua data pelanggan?')">
        @csrf
        <div class="reset-actions">
            <button class="btn reset-danger" type="submit">{!! $i('trash') !!}Reset Data Pelanggan</button>
            <a class="btn light" href="{{ url('/admin/dashboard') }}">Batal</a>
        </div>
    </form>
</div>
@endsection
