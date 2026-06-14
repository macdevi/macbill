@extends('layouts.neo')
@section('title','Pengaturan Umum')
@section('content')
@php
    $v = fn($key) => old($key, $settings[$key] ?? '');

    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'save' => '<svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>',
            'gear' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1A2 2 0 1 1 7.1 4.2l.1.1A1.7 1.7 0 0 0 9 4.6 1.7 1.7 0 0 0 10 3V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.general-settings-layout{
    display:grid;
    grid-template-columns:1fr 320px;
    gap:12px;
    align-items:start;
}
.general-settings-card,
.general-settings-side{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:24px;
    padding:18px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.general-settings-side{
    position:sticky;
    top:82px;
}
.setting-section{
    margin-bottom:18px;
}
.setting-section:last-child{
    margin-bottom:0;
}
.setting-section-title{
    display:flex;
    align-items:center;
    gap:8px;
    color:#101828;
    font-size:15px;
    font-weight:950;
    letter-spacing:-.035em;
    margin-bottom:12px;
}
.setting-note{
    background:#eff6ff;
    border:1px solid #b2ddff;
    color:#175cd3;
    border-radius:18px;
    padding:12px;
    font-size:13px;
    line-height:1.45;
    margin-bottom:14px;
}
.general-settings-actions{
    display:flex;
    justify-content:flex-end;
    gap:8px;
    margin-top:14px;
}
.general-settings-side b{
    display:block;
    color:#101828;
    font-size:15px;
    letter-spacing:-.035em;
}
.general-settings-side p{
    margin:7px 0 0;
    color:#667085;
    font-size:12px;
    line-height:1.45;
}
.general-settings-side .mini{
    margin-top:12px;
    display:grid;
    gap:8px;
}
.general-settings-side .mini div{
    border:1px solid #eef2f7;
    background:#f8fbff;
    border-radius:14px;
    padding:10px;
    color:#667085;
    font-size:12px;
    line-height:1.4;
}
.general-settings-side .mini strong{
    color:#101828;
}
@media(max-width:980px){
    .general-settings-layout{
        grid-template-columns:1fr;
    }
    .general-settings-side{
        position:static;
    }
}
@media(max-width:760px){
    .general-settings-card,
    .general-settings-side{
        border-radius:20px;
        padding:14px;
    }
    .general-settings-actions{
        flex-direction:column;
    }
    .general-settings-actions .btn{
        width:100%;
        justify-content:center;
    }
}
</style>

<style id="general-settings-bottom-fix">
.general-settings-layout{
    padding-bottom:130px !important;
}
.general-settings-card{
    margin-bottom:130px !important;
}
.general-settings-actions{
    padding-bottom:110px !important;
}
@media(max-width:760px){
    .general-settings-layout{
        padding-bottom:145px !important;
    }
    .general-settings-card{
        margin-bottom:145px !important;
    }
    .general-settings-actions{
        padding-bottom:125px !important;
    }
}
</style>

<div class="pagehead">
    <div>
        <h1>Pengaturan Umum</h1>
        <p>Identitas aplikasi, kontak usaha, format invoice/nota, dan default billing.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">{!! $i('back') !!}Dashboard</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif

<div class="general-settings-layout">
    <form class="general-settings-card" method="POST" action="{{ url('/admin/settings/general') }}" enctype="multipart/form-data">
        @csrf

        <div class="setting-note">
            Pengaturan ini disimpan ke database dan nanti dipakai oleh invoice, nota, landing page, serta default form pelanggan. Route invoice lama tidak diganti.
        </div>

        <div class="setting-section">
            <div class="setting-section-title">{!! $i('gear') !!}Identitas Usaha</div>

            <div class="formgrid">
                <div class="field">
                    <label>Nama Aplikasi</label>
                    <input class="input" name="app_name" value="{{ $v('app_name') }}" required>
                </div>

                <div class="field">
                    <label>Nama Usaha / ISP</label>
                    <input class="input" name="business_name" value="{{ $v('business_name') }}" required>
                </div>

                <div class="field">
                    <label>Nama Pemilik / Penanggung Jawab</label>
                    <input class="input" name="owner_name" value="{{ $v('owner_name') }}">
                </div>

                <div class="field">
                    <label>Email</label>
                    <input class="input" type="email" name="business_email" value="{{ $v('business_email') }}">
                </div>

                <div class="field">
                    <label>No HP Admin</label>
                    <input class="input" name="business_phone" value="{{ $v('business_phone') }}">
                </div>

                <div class="field">
                    <label>WhatsApp Admin</label>
                    <input class="input" name="business_whatsapp" value="{{ $v('business_whatsapp') }}" placeholder="62xxxxxxxxxx">
                </div>

                <div class="field full">
                    <label>Alamat Usaha</label>
                    <textarea class="textarea" name="business_address">{{ $v('business_address') }}</textarea>
                </div>

                <div class="field">
                    <label>Logo Usaha</label>
                    <input class="input" type="file" name="business_logo" accept="image/*">
                    @if(!empty($settings['business_logo']))
                        <div style="margin-top:8px">
                            <img src="{{ asset('storage/'.$settings['business_logo']) }}" alt="Logo" style="height:54px;max-width:180px;object-fit:contain;border:1px solid #e4eaf3;border-radius:12px;padding:6px;background:#fff">
                        </div>
                    @endif
                </div>

                <div class="field">
                    <label>Favicon / Icon Kecil</label>
                    <input class="input" type="file" name="business_favicon" accept="image/*,.ico">
                    @if(!empty($settings['business_favicon']))
                        <div style="margin-top:8px">
                            <img src="{{ asset('storage/'.$settings['business_favicon']) }}" alt="Favicon" style="height:42px;width:42px;object-fit:contain;border:1px solid #e4eaf3;border-radius:12px;padding:6px;background:#fff">
                        </div>
                    @endif
                </div>

            </div>
        </div>

        
        <div class="setting-section" id="admin-login-setting-section">
            <div class="setting-section-title">{!! $i('gear') !!}Akun Admin</div>

            <div class="setting-note" style="margin-bottom:14px">
                Gunakan bagian ini untuk mengganti username dan password login admin. Kosongkan password jika tidak ingin mengubah password.
            </div>

            <div class="formgrid">
                <div class="field">
                    <label>Username Admin</label>
                    <input class="input" name="admin_username" value="{{ old('admin_username', auth()->user()->username ?? '') }}" autocomplete="username" placeholder="admin">
                </div>

                <div class="field">
                    <label>Password Admin Baru</label>
                    <input class="input" type="password" name="admin_password" autocomplete="new-password" placeholder="Kosongkan jika tidak diubah">
                </div>

                <div class="field">
                    <label>Ulangi Password Baru</label>
                    <input class="input" type="password" name="admin_password_confirmation" autocomplete="new-password" placeholder="Ulangi password baru">
                </div>
            </div>
        </div>

<div class="setting-section">
            <div class="setting-section-title">{!! $i('gear') !!}Landing Page</div>

            <div class="formgrid">
                <div class="field">
                    <label>Judul Landing Page</label>
                    <input class="input" name="landing_title" value="{{ $v('landing_title') }}" required>
                </div>

                <div class="field">
                    <label>Subjudul Landing Page</label>
                    <input class="input" name="landing_subtitle" value="{{ $v('landing_subtitle') }}">
                </div>
            </div>
        </div>

        <div class="setting-section">
            <div class="setting-section-title">{!! $i('gear') !!}Format Invoice dan Nota</div>

            <div class="formgrid">
                <div class="field">
                    <label>Prefix Invoice</label>
                    <input class="input" name="invoice_prefix" value="{{ $v('invoice_prefix') }}" required>
                </div>

                <div class="field">
                    <label>Prefix Nota Pembayaran</label>
                    <input class="input" name="receipt_prefix" value="{{ $v('receipt_prefix') }}" required>
                </div>

                <div class="field full">
                    <label>Catatan Invoice</label>
                    <textarea class="textarea" name="invoice_note">{{ $v('invoice_note') }}</textarea>
                </div>

                <div class="field full">
                    <label>Footer Nota</label>
                    <textarea class="textarea" name="receipt_footer">{{ $v('receipt_footer') }}</textarea>
                </div>
            </div>
        </div>

        <div class="setting-section">
            <div class="setting-section-title">{!! $i('gear') !!}Default Billing</div>

            <div class="formgrid">
                <div class="field">
                    <label>Mata Uang</label>
                    <select class="select" name="currency" required>
                        <option value="IDR" @selected($v('currency') === 'IDR')>IDR / Rupiah</option>
                    </select>
                </div>

                <div class="field">
                    <label>Zona Waktu</label>
                    <select class="select" name="timezone" required>
                        <option value="Asia/Jakarta" @selected($v('timezone') === 'Asia/Jakarta')>Asia/Jakarta</option>
                        <option value="Asia/Makassar" @selected($v('timezone') === 'Asia/Makassar')>Asia/Makassar</option>
                        <option value="Asia/Jayapura" @selected($v('timezone') === 'Asia/Jayapura')>Asia/Jayapura</option>
                    </select>
                </div>

                <div class="field">
                    <label>Default Metode Pembayaran</label>
                    <select class="select" name="default_payment_method" required>
                        <option value="Tunai" @selected($v('default_payment_method') === 'Tunai')>Tunai</option>
                        <option value="Transfer" @selected($v('default_payment_method') === 'Transfer')>Transfer</option>
                        <option value="QRIS" @selected($v('default_payment_method') === 'QRIS')>QRIS</option>
                    </select>
                </div>

                <div class="field">
                    <label>Batas Nunggak</label>
                    <input class="input" type="number" min="1" max="12" name="overdue_months" value="{{ $v('overdue_months') }}" required>
                </div>

                <div class="field">
                    <label>Default Tampilan Peta</label>
                    <select class="select" name="map_default_layer" required>
                        <option value="satellite" @selected($v('map_default_layer') === 'satellite')>Satelit</option>
                        <option value="street" @selected($v('map_default_layer') === 'street')>Peta Jalan</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="general-settings-actions">
            <button class="btn" type="submit">{!! $i('save') !!}Simpan Pengaturan</button>
        </div>
    </form>

    <aside class="general-settings-side">
        <b>Arah Integrasi</b>
        <p>Pengaturan umum ini akan menjadi sumber data untuk invoice, nota pembayaran, landing page, dan default input pelanggan.</p>

        <div class="mini">
            <div><strong>Aman:</strong> route invoice lama tidak diganti.</div>
            <div><strong>Aman:</strong> login/auth tidak disentuh.</div>
            <div><strong>Berikutnya:</strong> sambungkan setting ke print invoice dan nota pembayaran.</div>
        </div>
    </aside>
</div>
@endsection
