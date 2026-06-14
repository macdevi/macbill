@extends('layouts.neo')
@section('title','Dashboard Teknisi')
@section('content')
@php
    $i = function ($name) {
        $icons = [
            'wifi' => '<svg viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>',
            'map' => '<svg viewBox="0 0 24 24"><path d="M9 18 3 21V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>',
            'tool' => '<svg viewBox="0 0 24 24"><path d="M14.7 6.3a4 4 0 0 0-5 5L3 18v3h3l6.7-6.7a4 4 0 0 0 5-5l-2.4 2.4-3-3 2.4-2.4z"/></svg>',
            'alert' => '<svg viewBox="0 0 24 24"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>',
        ];

        return '<span class="dash-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.dash-hero{
    background:linear-gradient(135deg,#1d4ed8,#0891b2);
    border-radius:28px;
    padding:22px;
    color:#fff;
    box-shadow:0 18px 44px rgba(16,24,40,.08);
    margin-bottom:12px;
}
.dash-hero span{display:block;color:#e0f2fe;font-size:13px;font-weight:800}
.dash-hero b{display:block;margin-top:6px;font-size:31px;line-height:1;letter-spacing:-.07em}
.dash-hero p{margin:10px 0 0;color:#e0f2fe;max-width:620px;line-height:1.45;font-size:14px}
.dash-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:12px}
.dash-stat{
    background:#fff;border:1px solid #e4eaf3;border-radius:22px;padding:15px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);color:#101828;
}
.dash-stat-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px}
.dash-stat .label{color:#667085;font-size:12px;font-weight:800}
.dash-stat .value{display:block;margin-top:7px;color:#101828;font-size:22px;font-weight:950;letter-spacing:-.055em}
.dash-ico{width:32px;height:32px;border-radius:13px;display:inline-grid;place-items:center;color:#175cd3;background:#eff6ff;flex:none}
.dash-ico svg{width:17px;height:17px;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
.dash-note{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:22px;
    padding:16px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
    color:#667085;
    line-height:1.5;
    font-size:14px;
}
.dash-note b{color:#101828}
@media(max-width:920px){.dash-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:620px){
    .dash-hero{border-radius:22px;padding:18px}.dash-hero b{font-size:25px}
    .dash-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .dash-stat{border-radius:18px;padding:12px}.dash-stat .value{font-size:19px}
    .dash-ico{width:29px;height:29px;border-radius:11px}
}
</style>

<div class="dash-hero">
    <span>Dashboard Teknisi</span>
    <b>Area Teknisi</b>
    <p>Dashboard teknisi disiapkan untuk monitoring ODP, gangguan, pasang baru, dan pekerjaan lapangan.</p>
</div>

<div class="dash-grid">
    <div class="dash-stat">
        <div class="dash-stat-top">
            <div><div class="label">Customer Online</div><div class="value">0</div></div>
            {!! $i('wifi') !!}
        </div>
    </div>

    <div class="dash-stat">
        <div class="dash-stat-top">
            <div><div class="label">Customer Offline</div><div class="value">0</div></div>
            {!! $i('alert') !!}
        </div>
    </div>

    <div class="dash-stat">
        <div class="dash-stat-top">
            <div><div class="label">ODP</div><div class="value">0</div></div>
            {!! $i('map') !!}
        </div>
    </div>

    <div class="dash-stat">
        <div class="dash-stat-top">
            <div><div class="label">Gangguan</div><div class="value">0</div></div>
            {!! $i('tool') !!}
        </div>
    </div>
</div>

<div class="dash-note">
    <b>Catatan:</b> fitur teknisi belum dihubungkan ke data NOC/monitoring. Tampilan ini dirapikan dulu agar konsisten dengan admin dan kasir. Nanti bisa dilanjutkan ke modul pasang baru, gangguan, dan monitoring pelanggan.
</div>
@endsection
