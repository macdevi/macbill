@extends('layouts.neo')
@section('title','Pengaturan OLT')
@section('content')
@php
    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'olt' => '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 8h8"/><path d="M8 12h8"/><path d="M8 16h4"/></svg>',
        ];
        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.setting-placeholder{background:#fff;border:1px solid #e4eaf3;border-radius:24px;padding:18px;box-shadow:0 10px 24px rgba(16,24,40,.055)}
.setting-placeholder h2{margin:0;color:#101828;font-size:20px;letter-spacing:-.05em}
.setting-placeholder p{margin:8px 0 0;color:#667085;font-size:14px;line-height:1.5}
.setting-list{margin-top:14px;display:grid;gap:8px}
.setting-item{border:1px solid #eef2f7;background:#f8fbff;border-radius:16px;padding:12px;color:#667085;font-size:13px}
.setting-item b{color:#101828}
</style>

<div class="pagehead">
    <div>
        <h1>Pengaturan OLT</h1>
        <p>Konfigurasi perangkat OLT untuk jaringan fiber.</p>
    </div>
    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">{!! $i('back') !!}Dashboard</a>
    </div>
</div>

<div class="setting-placeholder">
    <h2>{!! $i('olt') !!}Pengaturan OLT</h2>
    <p>Halaman ini disiapkan untuk pengaturan OLT, SNMP, Telnet/SSH, port PON, ONU, dan monitoring redaman pelanggan.</p>

    <div class="setting-list">
        <div class="setting-item"><b>Nanti:</b> IP OLT, brand, SNMP community, port, username, password.</div>
        <div class="setting-item"><b>Nanti:</b> monitoring ONU, RX power, status online/offline, dan mapping ODP.</div>
    </div>
</div>
@endsection
