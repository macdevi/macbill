@php
    $settings = $appSettings ?? \App\Services\SettingService::allMerged();

    $appName = $settings['app_name'] ?? 'MAC-SERVICE';
    $businessName = $settings['business_name'] ?? 'MACNET RT/RW.NET';
    $title = $settings['landing_title'] ?? 'Sistem Billing RT/RW.NET Terintegrasi';
    $subtitle = $settings['landing_subtitle'] ?? 'Kelola pelanggan, tagihan, pembayaran, pemasangan baru, dan portal operasional dalam satu sistem yang rapi.';
    $logo = $settings['business_logo'] ?? '';
    $phone = trim((string)($settings['business_phone'] ?? ''));
    $wa = trim((string)($settings['business_whatsapp'] ?? $phone));
    $email = trim((string)($settings['business_email'] ?? ''));
    $address = trim((string)($settings['business_address'] ?? ''));

    $waClean = preg_replace('/\D+/', '', $wa);
    if (substr($waClean, 0, 1) === '0') {
        $waClean = '62'.substr($waClean, 1);
    } elseif ($waClean !== '' && substr($waClean, 0, 1) === '8') {
        $waClean = '62'.$waClean;
    }

    $waLink = $waClean ? 'https://wa.me/'.$waClean.'?text='.rawurlencode('Halo '.$businessName.', saya ingin bertanya tentang layanan internet.') : '#';
    $initial = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $appName), 0, 1) ?: 'M');
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appName }}</title>

    @if(!empty($settings['business_favicon']))
        <link rel="icon" href="{{ asset('storage/'.$settings['business_favicon']) }}">
    @endif

    <style>
        *{box-sizing:border-box}

        body{
            margin:0;
            min-height:100vh;
            font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            color:#101828;
            background:
                radial-gradient(circle at 50% -10%,rgba(37,99,235,.55),transparent 32%),
                linear-gradient(180deg,#0b1220,#111827);
        }

        a{text-decoration:none;color:inherit}

        .page{
            min-height:100vh;
            padding:18px;
            display:grid;
            place-items:center;
        }

        .phone{
            width:min(440px,100%);
            min-height:calc(100vh - 36px);
            background:#f5f8ff;
            border-radius:38px;
            overflow:hidden;
            border:1px solid rgba(255,255,255,.18);
            box-shadow:0 40px 110px rgba(0,0,0,.40);
            display:flex;
            flex-direction:column;
            position:relative;
        }

        .hero{
            position:relative;
            padding:22px 20px 32px;
            color:#fff;
            background:
                radial-gradient(circle at 95% 5%,rgba(255,255,255,.22),transparent 28%),
                linear-gradient(145deg,#0b1b3a,#2563eb 58%,#06b6d4);
            border-radius:0 0 34px 34px;
            overflow:visible;
        }

        .hero::after{
            content:"";
            position:absolute;
            right:-70px;
            top:-70px;
            width:220px;
            height:220px;
            border-radius:999px;
            background:rgba(255,255,255,.12);
            pointer-events:none;
        }

        .topbar{
            position:relative;
            z-index:5;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
        }

        .brand{
            min-width:0;
            display:flex;
            align-items:center;
            gap:12px;
        }

        .logo{
            width:54px;
            height:54px;
            min-width:54px;
            border-radius:18px;
            display:grid;
            place-items:center;
            overflow:hidden;
            color:#1d4ed8;
            background:#fff;
            font-weight:950;
            line-height:.85;
            box-shadow:0 14px 34px rgba(0,0,0,.16);
        }

        .logo img{
            width:100%;
            height:100%;
            object-fit:contain;
            padding:7px;
            background:#fff;
        }

        .logo .initial{
            font-size:22px;
        }

        .brand b{
            display:block;
            color:#fff;
            font-size:18px;
            font-weight:950;
            letter-spacing:-.045em;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
            max-width:210px;
        }

        .brand span{
            display:block;
            margin-top:2px;
            color:#dbeafe;
            font-size:12px;
            font-weight:800;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
            max-width:210px;
        }

        .menu-wrap{
            position:relative;
            z-index:20;
            flex:0 0 auto;
        }

        .hamburger{
            width:44px;
            height:44px;
            border:0;
            border-radius:16px;
            background:rgba(255,255,255,.16);
            color:#fff;
            display:grid;
            place-items:center;
            cursor:pointer;
            box-shadow:inset 0 0 0 1px rgba(255,255,255,.20);
        }

        .hamburger span,
        .hamburger span::before,
        .hamburger span::after{
            content:"";
            display:block;
            width:18px;
            height:2px;
            border-radius:99px;
            background:#fff;
            transition:.18s ease;
        }

        .hamburger span::before{
            transform:translateY(-6px);
        }

        .hamburger span::after{
            transform:translateY(4px);
        }

        .menu-wrap.open .hamburger span{
            background:transparent;
        }

        .menu-wrap.open .hamburger span::before{
            transform:translateY(1px) rotate(45deg);
        }

        .menu-wrap.open .hamburger span::after{
            transform:translateY(-1px) rotate(-45deg);
        }

        .dropdown{
            position:absolute;
            right:0;
            top:54px;
            width:230px;
            padding:8px;
            border-radius:22px;
            background:rgba(255,255,255,.98);
            border:1px solid #dbe5f2;
            box-shadow:0 24px 70px rgba(15,23,42,.24);
            backdrop-filter:blur(18px);
            opacity:0;
            transform:translateY(-8px) scale(.97);
            pointer-events:none;
            transition:.18s ease;
        }

        .menu-wrap.open .dropdown{
            opacity:1;
            transform:translateY(0) scale(1);
            pointer-events:auto;
        }

        .dropdown a{
            min-height:48px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            padding:0 13px;
            border-radius:16px;
            color:#101828;
            font-size:13px;
            font-weight:950;
        }

        .dropdown a:hover{
            background:#eff6ff;
            color:#175cd3;
        }

        .dropdown a:first-child{
            color:#fff;
            background:linear-gradient(135deg,#2563eb,#06b6d4);
        }

        h1{
            position:relative;
            z-index:2;
            margin:34px 0 0;
            color:#fff;
            font-size:36px;
            line-height:.98;
            letter-spacing:-.075em;
        }

        .lead{
            position:relative;
            z-index:2;
            margin:13px 0 0;
            color:#e0f2fe;
            font-size:13.5px;
            line-height:1.55;
            font-weight:700;
        }

        .quick{
            position:relative;
            z-index:4;
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:10px;
            margin:-24px 16px 14px;
        }

        .quick-card{
            min-height:88px;
            border-radius:23px;
            padding:14px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            background:#fff;
            border:1px solid #dbe5f2;
            box-shadow:0 16px 38px rgba(16,24,40,.12);
            font-weight:950;
        }

        .quick-card.primary{
            color:#fff;
            background:#0f172a;
            border-color:#0f172a;
        }

        .quick-card span{
            color:#667085;
            font-size:11px;
            font-weight:800;
        }

        .quick-card.primary span{
            color:#cbd5e1;
        }

        .cta{
            padding:0 16px;
            display:grid;
            gap:10px;
        }

        .btn{
            min-height:52px;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:9px;
            border-radius:19px;
            font-size:14px;
            font-weight:950;
            border:1px solid #dbe5f2;
            background:#fff;
        }

        .btn.main{
            color:#fff;
            border-color:transparent;
            background:linear-gradient(135deg,#2563eb,#06b6d4);
            box-shadow:0 16px 34px rgba(37,99,235,.20);
        }

        .btn.wa{
            color:#067647;
            background:#ecfdf3;
            border-color:#bbf7d0;
        }

        .waico{
            width:19px;
            height:19px;
            fill:currentColor;
        }

        .info{
            margin:14px 16px 90px;
            padding:16px;
            border-radius:25px;
            background:#fff;
            border:1px solid #dbe5f2;
            box-shadow:0 12px 34px rgba(16,24,40,.06);
            display:grid;
            gap:10px;
        }

        .info h2{
            margin:0 0 4px;
            font-size:17px;
            line-height:1.1;
            font-weight:950;
            letter-spacing:-.04em;
        }

        .row{
            display:flex;
            align-items:flex-start;
            gap:10px;
            color:#475467;
            font-size:13px;
            line-height:1.35;
            font-weight:800;
            padding:9px 0;
            border-top:1px solid #edf2f7;
        }

        .row:first-of-type{
            border-top:0;
        }

        .mini{
            width:30px;
            height:30px;
            min-width:30px;
            border-radius:11px;
            display:grid;
            place-items:center;
            color:#175cd3;
            background:#eff6ff;
            font-size:12px;
            font-weight:950;
        }

        .mini.wa-mini{
            color:#067647;
            background:#ecfdf3;
        }

        .mini .waico{
            width:16px;
            height:16px;
        }

        .dock{
            position:absolute;
            left:0;
            right:0;
            bottom:0;
            padding:10px 14px 14px;
            background:rgba(255,255,255,.88);
            border-top:1px solid #dbe5f2;
            backdrop-filter:blur(18px);
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:10px;
        }

        .dock .btn{
            min-height:48px;
            border-radius:18px;
            font-size:13px;
        }

        .dock .btn.dark{
            color:#fff;
            background:#0f172a;
            border-color:#0f172a;
        }

        .menu-backdrop{
            position:fixed;
            inset:0;
            z-index:10;
            display:none;
        }

        .menu-backdrop.show{
            display:block;
        }

        @media(max-width:520px){
            .page{
                padding:0;
                display:block;
            }

            .phone{
                width:100%;
                min-height:100vh;
                border-radius:0;
                border:0;
                box-shadow:none;
            }

            .hero{
                padding-top:18px;
            }

            h1{
                font-size:34px;
            }

            .brand b,
            .brand span{
                max-width:175px;
            }
        }
    

/* Cleanup Layout 2: hilangkan duplikat dan rapikan informasi */
.quick,
.dock{
    display:none !important;
}

.cta{
    margin-top:-14px !important;
}

.info.info-compact{
    margin:14px 16px 24px !important;
    padding:15px !important;
    border-radius:24px !important;
    background:#fff !important;
    border:1px solid #dbe5f2 !important;
    box-shadow:0 12px 34px rgba(16,24,40,.06) !important;
}

.info.info-compact h2{
    margin:0 0 11px !important;
    font-size:17px !important;
    font-weight:950 !important;
    letter-spacing:-.04em !important;
}

.info-line{
    display:flex !important;
    flex-wrap:wrap !important;
    align-items:center !important;
    gap:8px !important;
}

.info-pill{
    min-height:36px !important;
    display:inline-flex !important;
    align-items:center !important;
    gap:7px !important;
    padding:0 11px !important;
    border-radius:999px !important;
    background:#f8fbff !important;
    border:1px solid #e4eaf3 !important;
    color:#475467 !important;
    font-size:12px !important;
    font-weight:850 !important;
    line-height:1 !important;
}

.info-pill.wa-pill{
    background:#ecfdf3 !important;
    border-color:#bbf7d0 !important;
    color:#067647 !important;
}

.info-pill .waico{
    width:16px !important;
    height:16px !important;
    min-width:16px !important;
    fill:currentColor !important;
}

.text-icon{
    width:18px !important;
    height:18px !important;
    min-width:18px !important;
    display:inline-grid !important;
    place-items:center !important;
    border-radius:7px !important;
    background:#eff6ff !important;
    color:#175cd3 !important;
    font-size:11px !important;
    font-weight:950 !important;
}

.phone{
    padding-bottom:0 !important;
}

@media(max-width:520px){
    .cta{
        margin-top:-10px !important;
    }

    .info.info-compact{
        margin-bottom:18px !important;
    }

    .info-line{
        display:grid !important;
        grid-template-columns:1fr !important;
        gap:8px !important;
    }

    .info-pill{
        width:100% !important;
        justify-content:flex-start !important;
        white-space:normal !important;
        line-height:1.25 !important;
        padding:9px 11px !important;
    }
}

</style>
<style id="landing-final-polish">
/* Final polish landing */
.logo{
    width:60px !important;
    height:60px !important;
    min-width:60px !important;
    border-radius:20px !important;
}

.logo img{
    padding:3px !important;
    transform:scale(1.16) !important;
    object-fit:contain !important;
}

/* Hamburger lebih kontras */
.hamburger{
    width:48px !important;
    height:48px !important;
    border-radius:18px !important;
    background:rgba(15,23,42,.46) !important;
    border:1px solid rgba(255,255,255,.38) !important;
    box-shadow:
        0 12px 30px rgba(0,0,0,.20),
        inset 0 0 0 1px rgba(255,255,255,.16) !important;
    backdrop-filter:blur(14px) !important;
}

.hamburger span,
.hamburger span::before,
.hamburger span::after{
    width:21px !important;
    height:2.5px !important;
    background:#ffffff !important;
}

.menu-wrap.open .hamburger{
    background:#ffffff !important;
    border-color:#ffffff !important;
}

.menu-wrap.open .hamburger span{
    background:transparent !important;
}

.menu-wrap.open .hamburger span::before,
.menu-wrap.open .hamburger span::after{
    background:#175cd3 !important;
}

/* Jarak tombol dari hero */
.cta{
    margin-top:18px !important;
    padding-top:0 !important;
}

.hero{
    padding-bottom:44px !important;
}

.btn.main{
    margin-top:0 !important;
}

/* Sedikit rapikan jarak antar tombol */
.cta .btn{
    min-height:54px !important;
}

@media(max-width:520px){
    .logo{
        width:58px !important;
        height:58px !important;
        min-width:58px !important;
    }

    .logo img{
        padding:3px !important;
        transform:scale(1.15) !important;
    }

    .hamburger{
        width:46px !important;
        height:46px !important;
        border-radius:17px !important;
    }

    .cta{
        margin-top:16px !important;
    }

    .hero{
        padding-bottom:42px !important;
    }
}
</style>
<style id="landing-info-compact-customer-space">
/* Compact Informasi Layanan agar ada ruang untuk Login Customer */
.info.info-compact{
    margin:12px 16px 8px !important;
    padding:10px 12px !important;
    border-radius:20px !important;
    background:rgba(255,255,255,.94) !important;
    box-shadow:0 8px 22px rgba(16,24,40,.045) !important;
}

.info.info-compact h2{
    margin:0 0 8px !important;
    font-size:14px !important;
    line-height:1.1 !important;
    letter-spacing:-.03em !important;
}

.info-line{
    display:grid !important;
    grid-template-columns:1fr !important;
    gap:6px !important;
}

.info-pill{
    width:100% !important;
    min-height:32px !important;
    height:auto !important;
    padding:7px 10px !important;
    border-radius:14px !important;
    font-size:11px !important;
    line-height:1.25 !important;
    font-weight:850 !important;
    justify-content:flex-start !important;
}

.info-pill .waico{
    width:15px !important;
    height:15px !important;
    min-width:15px !important;
}

.text-icon{
    width:16px !important;
    height:16px !important;
    min-width:16px !important;
    border-radius:6px !important;
    font-size:10px !important;
}

/* Area cadangan untuk Login Customer nanti */
.customer-login-space{
    margin:8px 16px 16px !important;
}

/* Tombol CTA tetap rapat tapi tidak sesak */
.cta{
    margin-top:14px !important;
}

.cta .btn{
    min-height:50px !important;
}

/* Jarak bawah lebih aman */
.phone{
    padding-bottom:14px !important;
}

@media(max-width:520px){
    .info.info-compact{
        margin:11px 16px 8px !important;
        padding:10px !important;
    }

    .info.info-compact h2{
        font-size:13.5px !important;
        margin-bottom:7px !important;
    }

    .info-pill{
        min-height:30px !important;
        padding:6px 9px !important;
        font-size:10.8px !important;
    }
}
</style>
<style id="landing-login-click-fix">
/* Fix hamburger login clickable */
.menu-wrap{
    z-index:10020 !important;
}

.dropdown{
    z-index:10030 !important;
    pointer-events:none !important;
}

.menu-wrap.open .dropdown{
    pointer-events:auto !important;
}

.menu-backdrop{
    z-index:10010 !important;
}
</style>
<style id="hamburger-click-final-css">
.menu-backdrop{
    display:none !important;
    pointer-events:none !important;
}

.menu-wrap{
    position:relative !important;
    z-index:999999 !important;
}

.dropdown{
    position:absolute !important;
    right:0 !important;
    top:56px !important;
    z-index:1000000 !important;
    pointer-events:none !important;
}

.menu-wrap.open .dropdown{
    opacity:1 !important;
    transform:translateY(0) scale(1) !important;
    pointer-events:auto !important;
}

.dropdown a{
    position:relative !important;
    z-index:1000001 !important;
    pointer-events:auto !important;
    cursor:pointer !important;
    user-select:none !important;
    -webkit-tap-highlight-color:transparent !important;
}

.dropdown a *{
    pointer-events:none !important;
}

.hero,
.topbar,
.phone{
    overflow:visible !important;
}
</style>
<style id="customer-login-temp-style">
.customer-login-box{
    margin:8px 16px 18px !important;
}

.customer-login-btn{
    min-height:58px !important;
    display:flex !important;
    align-items:center !important;
    gap:12px !important;
    padding:11px 13px !important;
    border-radius:21px !important;
    color:#fff !important;
    background:linear-gradient(135deg,#0f172a,#1d4ed8) !important;
    border:1px solid rgba(255,255,255,.16) !important;
    box-shadow:0 16px 34px rgba(15,23,42,.18) !important;
    text-decoration:none !important;
    -webkit-tap-highlight-color:transparent !important;
}

.customer-login-icon{
    width:38px !important;
    height:38px !important;
    min-width:38px !important;
    border-radius:15px !important;
    display:grid !important;
    place-items:center !important;
    background:rgba(255,255,255,.14) !important;
    color:#fff !important;
}

.customer-login-icon svg{
    width:20px !important;
    height:20px !important;
    fill:none !important;
    stroke:currentColor !important;
    stroke-width:2.2 !important;
    stroke-linecap:round !important;
    stroke-linejoin:round !important;
}

.customer-login-btn span:nth-child(2){
    min-width:0 !important;
    flex:1 !important;
}

.customer-login-btn b{
    display:block !important;
    font-size:14px !important;
    line-height:1.1 !important;
    font-weight:950 !important;
    letter-spacing:-.025em !important;
}

.customer-login-btn small{
    display:block !important;
    margin-top:3px !important;
    color:#dbeafe !important;
    font-size:11px !important;
    line-height:1.2 !important;
    font-weight:800 !important;
}

.customer-login-btn i{
    width:28px !important;
    height:28px !important;
    min-width:28px !important;
    border-radius:999px !important;
    display:grid !important;
    place-items:center !important;
    background:rgba(255,255,255,.14) !important;
    font-style:normal !important;
    font-weight:950 !important;
}

@media(max-width:520px){
    .customer-login-box{
        margin:8px 16px 16px !important;
    }

    .customer-login-btn{
        min-height:56px !important;
        border-radius:20px !important;
    }
}
</style>
<style id="landing-cta-color-order-final">
/* Urutan dan warna CTA final */
.customer-login-box{
    margin:16px 16px 10px !important;
}

.cta{
    margin-top:0 !important;
    padding:0 16px !important;
    gap:10px !important;
}

/* Daftar Baru / Pasang Baru dibuat putih biasa */
.cta .btn.main.daftar-baru-white{
    color:#101828 !important;
    background:#ffffff !important;
    border:1px solid #dbe5f2 !important;
    box-shadow:0 12px 28px rgba(16,24,40,.075) !important;
}

/* Lihat Paket disamakan dengan Login Pelanggan */
.cta .btn.package-premium{
    color:#ffffff !important;
    background:linear-gradient(135deg,#0f172a,#1d4ed8) !important;
    border:1px solid rgba(255,255,255,.16) !important;
    box-shadow:0 16px 34px rgba(15,23,42,.18) !important;
}

/* Jarak antara Lihat Paket dan WhatsApp tetap rapi */
.cta .btn.wa{
    margin-top:0 !important;
}

/* Login Pelanggan tetap paling menonjol di atas Daftar Baru */
.customer-login-btn{
    min-height:58px !important;
}

@media(max-width:520px){
    .customer-login-box{
        margin:14px 16px 10px !important;
    }

    .cta{
        gap:9px !important;
    }
}
</style>
<style id="landing-cta-center-whatsapp-final">
/* Final CTA: Login Pelanggan, Daftar Baru/Pasang Baru, Lihat Paket dibuat seragam */
.customer-login-box{
    margin:16px 16px 10px !important;
}

.customer-login-btn,
.cta .btn.main.daftar-baru-white,
.cta .btn.package-premium{
    min-height:58px !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    gap:10px !important;
    text-align:center !important;
    color:#ffffff !important;
    background:linear-gradient(135deg,#0f172a,#1d4ed8) !important;
    border:1px solid rgba(255,255,255,.16) !important;
    box-shadow:0 16px 34px rgba(15,23,42,.18) !important;
    border-radius:21px !important;
    font-weight:950 !important;
}

/* Text Login Pelanggan center */
.customer-login-btn span:nth-child(2){
    flex:0 1 auto !important;
    min-width:0 !important;
    text-align:center !important;
}

.customer-login-btn b{
    text-align:center !important;
}

.customer-login-btn small{
    text-align:center !important;
    color:#dbeafe !important;
}

/* Arrow Login Pelanggan disembunyikan supaya teks benar-benar center */
.customer-login-btn i{
    display:none !important;
}

/* Icon Login Pelanggan tetap rapi */
.customer-login-icon{
    width:36px !important;
    height:36px !important;
    min-width:36px !important;
    border-radius:15px !important;
    background:rgba(255,255,255,.14) !important;
}

/* Daftar Baru dan Lihat Paket center */
.cta .btn.main.daftar-baru-white,
.cta .btn.package-premium{
    font-size:14px !important;
    line-height:1.2 !important;
}

/* WhatsApp dibuat hijau ala WhatsApp dengan icon putih */
.cta .btn.wa{
    min-height:54px !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    gap:9px !important;
    text-align:center !important;
    color:#ffffff !important;
    background:linear-gradient(135deg,#128C7E,#25D366) !important;
    border:1px solid rgba(255,255,255,.18) !important;
    box-shadow:0 16px 34px rgba(37,211,102,.22) !important;
    border-radius:21px !important;
    font-weight:950 !important;
}

.cta .btn.wa .waico,
.cta .btn.wa svg{
    color:#ffffff !important;
    fill:#ffffff !important;
}

/* Biar jarak antar box tetap enak */
.cta{
    gap:10px !important;
}

@media(max-width:520px){
    .customer-login-btn,
    .cta .btn.main.daftar-baru-white,
    .cta .btn.package-premium{
        min-height:56px !important;
        border-radius:20px !important;
    }

    .cta .btn.wa{
        min-height:52px !important;
        border-radius:20px !important;
    }
}
</style>
<style id="landing-horizontal-overflow-fix">
/* Fix bug halaman bisa digeser horizontal */
html,
body{
    width:100% !important;
    max-width:100% !important;
    overflow-x:hidden !important;
}

body{
    position:relative !important;
    overscroll-behavior-x:none !important;
}

.page{
    width:100% !important;
    max-width:100vw !important;
    overflow-x:hidden !important;
}

.phone{
    width:100% !important;
    max-width:440px !important;
    overflow:hidden !important;
    margin-left:auto !important;
    margin-right:auto !important;
}

.hero{
    overflow:hidden !important;
}

.topbar{
    overflow:visible !important;
}

.menu-wrap{
    position:relative !important;
    z-index:999999 !important;
}

.dropdown{
    right:0 !important;
    max-width:calc(100vw - 36px) !important;
    z-index:1000000 !important;
}

.cta,
.customer-login-box,
.info.info-compact{
    max-width:calc(100% - 32px) !important;
}

.customer-login-btn,
.cta .btn,
.info-pill{
    max-width:100% !important;
}

/* Mobile: landing harus benar-benar full screen, tidak boleh nyamping */
@media(max-width:520px){
    .page{
        padding:0 !important;
        display:block !important;
    }

    .phone{
        max-width:100vw !important;
        min-height:100vh !important;
        border-radius:0 !important;
        border:0 !important;
    }

    .hero{
        border-radius:0 0 34px 34px !important;
    }
}
</style>
<style id="landing-color-polish-uniform">
/* Final color polish: warna dibuat lebih seragam dan bersih */
body{
    background:
        radial-gradient(circle at 50% -10%,rgba(37,99,235,.34),transparent 34%),
        linear-gradient(180deg,#eef5ff 0%,#f7f9fc 52%,#f3f6fb 100%) !important;
}

.hero{
    background:
        radial-gradient(circle at 96% 2%,rgba(255,255,255,.20),transparent 30%),
        linear-gradient(145deg,#0b1b3a 0%,#1d4ed8 56%,#0891b2 100%) !important;
    box-shadow:0 18px 42px rgba(29,78,216,.16) !important;
}

.customer-login-btn,
.cta .btn.main.daftar-baru-white,
.cta .btn.package-premium{
    background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 48%,#1d4ed8 100%) !important;
    border:1px solid rgba(255,255,255,.16) !important;
    box-shadow:0 14px 30px rgba(15,23,42,.16) !important;
    color:#ffffff !important;
}

.customer-login-btn{
    background:linear-gradient(135deg,#101828 0%,#1d4ed8 100%) !important;
}

.customer-login-icon,
.customer-login-btn i{
    background:rgba(255,255,255,.13) !important;
    border:1px solid rgba(255,255,255,.12) !important;
}

/* WhatsApp hijau dibuat lebih elegan, tidak terlalu neon */
.cta .btn.wa{
    background:linear-gradient(135deg,#047857 0%,#16a34a 58%,#22c55e 100%) !important;
    border:1px solid rgba(255,255,255,.18) !important;
    box-shadow:0 14px 30px rgba(22,163,74,.18) !important;
    color:#ffffff !important;
}

.cta .btn.wa .waico,
.cta .btn.wa svg{
    fill:#ffffff !important;
    color:#ffffff !important;
}

/* Card informasi dibuat lebih menyatu dengan tema */
.info.info-compact{
    background:rgba(255,255,255,.96) !important;
    border:1px solid #dbe7f5 !important;
    box-shadow:0 16px 34px rgba(15,23,42,.07) !important;
}

.info.info-compact h2{
    color:#101828 !important;
}

.info-pill{
    background:#f8fbff !important;
    border:1px solid #e3ebf6 !important;
    color:#475467 !important;
    box-shadow:none !important;
}

.info-pill.wa-pill{
    background:#ecfdf3 !important;
    border-color:#c8f3d6 !important;
    color:#047857 !important;
}

.text-icon{
    background:#eef6ff !important;
    color:#175cd3 !important;
}

/* Logo dan hamburger disamakan karakter visualnya */
.logo{
    box-shadow:0 12px 30px rgba(15,23,42,.16) !important;
}

.hamburger{
    background:rgba(15,23,42,.48) !important;
    border:1px solid rgba(255,255,255,.32) !important;
    box-shadow:0 12px 28px rgba(15,23,42,.20), inset 0 0 0 1px rgba(255,255,255,.10) !important;
}

/* Spacing lebih konsisten */
.customer-login-box{
    margin-top:18px !important;
    margin-bottom:10px !important;
}

.cta{
    gap:10px !important;
}

.info.info-compact{
    margin-top:14px !important;
}

/* Lebar tombol dibuat konsisten */
.customer-login-box,
.cta,
.info.info-compact{
    width:calc(100% - 32px) !important;
    max-width:calc(100% - 32px) !important;
}

.customer-login-btn,
.cta .btn{
    width:100% !important;
}

/* Typography sedikit lebih rapi */
.customer-login-btn b,
.cta .btn{
    letter-spacing:-.02em !important;
}

.customer-login-btn small{
    color:#e0ecff !important;
}

@media(max-width:520px){
    .hero{
        box-shadow:0 16px 34px rgba(29,78,216,.13) !important;
    }

    .customer-login-box{
        margin-top:16px !important;
    }

    .info.info-compact{
        margin-top:13px !important;
    }
}
</style>
<style id="landing-final-spacing-align-polish">
/* Final alignment polish */

/* Semua blok utama dibuat sejajar lebarnya */
.customer-login-box,
.cta,
.info.info-compact{
    width:auto !important;
    max-width:none !important;
    margin-left:28px !important;
    margin-right:28px !important;
}

.cta{
    padding-left:0 !important;
    padding-right:0 !important;
    gap:10px !important;
}

/* Semua tombol utama sama lebar, tinggi, radius */
.customer-login-btn,
.cta .btn.main.daftar-baru-white,
.cta .btn.package-premium,
.cta .btn.wa{
    width:100% !important;
    min-height:58px !important;
    border-radius:21px !important;
}

/* Login Pelanggan: teks benar-benar center, icon tetap di kiri */
.customer-login-btn{
    position:relative !important;
    justify-content:center !important;
    padding-left:74px !important;
    padding-right:74px !important;
}

.customer-login-icon{
    position:absolute !important;
    left:18px !important;
    top:50% !important;
    transform:translateY(-50%) !important;
}

.customer-login-btn span:nth-child(2){
    flex:0 1 auto !important;
    width:auto !important;
    text-align:center !important;
}

.customer-login-btn b,
.customer-login-btn small{
    text-align:center !important;
}

/* Tombol Daftar Baru dan Lihat Paket lebih stabil secara visual */
.cta .btn.main.daftar-baru-white,
.cta .btn.package-premium{
    justify-content:center !important;
    text-align:center !important;
    font-size:14px !important;
}

/* WhatsApp juga sejajar dan tidak terlalu mencolok */
.cta .btn.wa{
    justify-content:center !important;
    text-align:center !important;
    background:linear-gradient(135deg,#047857 0%,#16a34a 60%,#22c55e 100%) !important;
}

/* Card informasi dibuat lebih compact */
.info.info-compact{
    margin-top:14px !important;
    padding:14px 16px 16px !important;
    border-radius:24px !important;
}

.info.info-compact h2{
    margin:0 0 10px !important;
    font-size:16px !important;
    line-height:1.1 !important;
}

.info-line{
    gap:7px !important;
}

.info-pill{
    min-height:34px !important;
    padding:7px 12px !important;
    border-radius:999px !important;
    font-size:11.5px !important;
}

/* Spasi bawah tidak terlalu kosong */
.phone{
    padding-bottom:18px !important;
}

/* Mobile adjustment */
@media(max-width:520px){
    .customer-login-box,
    .cta,
    .info.info-compact{
        margin-left:28px !important;
        margin-right:28px !important;
    }

    .customer-login-btn,
    .cta .btn.main.daftar-baru-white,
    .cta .btn.package-premium,
    .cta .btn.wa{
        min-height:56px !important;
        border-radius:20px !important;
    }

    .customer-login-btn{
        padding-left:68px !important;
        padding-right:68px !important;
    }

    .customer-login-icon{
        left:17px !important;
        width:36px !important;
        height:36px !important;
        min-width:36px !important;
    }

    .info.info-compact{
        padding:13px 16px 15px !important;
    }
}
</style>
<style id="landing-cta-icons-uniform-final">
/* Icon seragam untuk semua tombol utama */
.customer-login-btn,
.cta-icon-btn{
    position:relative !important;
    justify-content:center !important;
    text-align:center !important;
    padding-left:76px !important;
    padding-right:76px !important;
}

/* Icon Login Pelanggan ikut dibuat ukuran standar */
.customer-login-icon,
.cta-left-icon{
    position:absolute !important;
    left:18px !important;
    top:50% !important;
    transform:translateY(-50%) !important;
    width:42px !important;
    height:42px !important;
    min-width:42px !important;
    border-radius:16px !important;
    display:grid !important;
    place-items:center !important;
    color:#ffffff !important;
    background:rgba(255,255,255,.14) !important;
    border:1px solid rgba(255,255,255,.13) !important;
}

.customer-login-icon svg,
.cta-left-icon svg{
    width:22px !important;
    height:22px !important;
    fill:none !important;
    stroke:currentColor !important;
    stroke-width:2.35 !important;
    stroke-linecap:round !important;
    stroke-linejoin:round !important;
}

/* Teks tombol tetap center walau ada icon kiri */
.customer-login-btn span:nth-child(2),
.cta-center-text{
    display:block !important;
    width:100% !important;
    text-align:center !important;
    font-weight:950 !important;
}

.customer-login-btn b,
.customer-login-btn small{
    text-align:center !important;
}

/* WhatsApp icon digeser ke kiri dan diperbesar */
.whatsapp-left-icon{
    background:rgba(255,255,255,.18) !important;
    border-color:rgba(255,255,255,.20) !important;
}

.whatsapp-left-icon svg,
.whatsapp-left-icon .waico{
    width:25px !important;
    height:25px !important;
    fill:#ffffff !important;
    stroke:none !important;
}

/* Samakan tinggi dan radius semua tombol */
.customer-login-btn,
.cta .btn.main.daftar-baru-white,
.cta .btn.package-premium,
.cta .btn.wa{
    min-height:58px !important;
    border-radius:21px !important;
}

/* Hilangkan arrow lama login pelanggan kalau masih ada */
.customer-login-btn i{
    display:none !important;
}

@media(max-width:520px){
    .customer-login-btn,
    .cta-icon-btn{
        padding-left:72px !important;
        padding-right:72px !important;
    }

    .customer-login-icon,
    .cta-left-icon{
        left:17px !important;
        width:40px !important;
        height:40px !important;
        min-width:40px !important;
        border-radius:15px !important;
    }

    .customer-login-icon svg,
    .cta-left-icon svg{
        width:21px !important;
        height:21px !important;
    }

    .whatsapp-left-icon svg,
    .whatsapp-left-icon .waico{
        width:24px !important;
        height:24px !important;
    }
}
</style>

<style id="landing-final-clean-customer-login-v1">
/* ======================================================
   LANDING FINAL CLEAN UI
   Khusus landing page. Tidak menyentuh kasir.
   ====================================================== */

.customer-login-box{
    margin:24px 24px 18px !important;
    padding:0 !important;
    display:block !important;
}

.customer-login-card{
    background:#ffffff !important;
    border:1px solid #e7ebf4 !important;
    border-radius:28px !important;
    padding:26px 24px !important;
    box-shadow:0 20px 44px rgba(28,53,112,.12) !important;
}

.customer-login-head{
    display:flex !important;
    align-items:center !important;
    gap:16px !important;
    margin-bottom:22px !important;
}

.customer-login-icon-final{
    width:76px !important;
    height:76px !important;
    border-radius:22px !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    color:#ffffff !important;
    background:linear-gradient(135deg,#122766,#2f5bf2 60%,#56baf4) !important;
    box-shadow:0 12px 24px rgba(47,91,242,.26) !important;
    flex:0 0 76px !important;
}

.customer-login-icon-final svg{
    width:34px !important;
    height:34px !important;
}

.customer-login-headtext b{
    display:block !important;
    font-size:24px !important;
    line-height:1.2 !important;
    color:#16213e !important;
    font-weight:900 !important;
    margin-bottom:4px !important;
}

.customer-login-headtext small{
    display:block !important;
    font-size:15px !important;
    line-height:1.45 !important;
    color:#697389 !important;
    font-weight:700 !important;
}

.customer-login-label{
    display:block !important;
    margin:18px 0 10px !important;
    font-size:18px !important;
    font-weight:900 !important;
    color:#313a50 !important;
}

.customer-login-field-final{
    display:flex !important;
    align-items:center !important;
    gap:12px !important;
    width:100% !important;
    min-height:72px !important;
    border:2px solid #e4e8f1 !important;
    border-radius:24px !important;
    background:#ffffff !important;
    padding:0 20px !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.6) !important;
}

.customer-login-field-final .field-ico{
    color:#6c7aa1 !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    flex:0 0 28px !important;
}

.customer-login-field-final .field-ico svg{
    width:26px !important;
    height:26px !important;
}

.customer-login-field-final input{
    width:100% !important;
    border:none !important;
    outline:none !important;
    background:transparent !important;
    font-size:18px !important;
    color:#26314c !important;
    font-weight:800 !important;
    padding:0 !important;
    box-shadow:none !important;
}

.customer-login-field-final input::placeholder{
    color:#9aa3b2 !important;
    font-weight:800 !important;
}

.customer-login-submit-final{
    width:100% !important;
    border:none !important;
    min-height:72px !important;
    border-radius:24px !important;
    margin-top:24px !important;
    color:#ffffff !important;
    font-size:19px !important;
    font-weight:900 !important;
    cursor:pointer !important;
    background:linear-gradient(135deg,#12307b,#2454f1 58%,#56baf4) !important;
    box-shadow:0 14px 28px rgba(36,84,241,.24) !important;
}

/* Sembunyikan CTA lama agar tidak dobel */
.cta{
    display:none !important;
}

/* Quick Action final */
.landing-quick-actions-final{
    margin:0 24px 22px !important;
    display:grid !important;
    grid-template-columns:1fr 1fr !important;
    gap:16px !important;
}

.quick-btn-final{
    min-height:98px !important;
    border-radius:24px !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    flex-direction:column !important;
    gap:10px !important;
    color:#ffffff !important;
    text-align:center !important;
    font-weight:900 !important;
    box-shadow:0 16px 30px rgba(27,44,88,.14) !important;
    padding:16px !important;
    text-decoration:none !important;
}

.quick-btn-final span:last-child{
    font-size:17px !important;
    line-height:1.2 !important;
}

.quick-btn-primary-final{
    background:linear-gradient(135deg,#12307b,#2f5cf3 60%,#56baf4) !important;
}

.quick-btn-wa-final{
    background:linear-gradient(135deg,#14853f,#1fb651 58%,#62d86d) !important;
}

.quick-user-badge-final{
    position:relative !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    width:38px !important;
    height:38px !important;
    color:#ffffff !important;
}

.quick-user-badge-final svg,
.quick-wa-icon-final svg{
    width:34px !important;
    height:34px !important;
    color:#ffffff !important;
}

.quick-wa-icon-final{
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    color:#ffffff !important;
}

.plus-badge-final{
    position:absolute !important;
    right:-7px !important;
    top:-5px !important;
    width:18px !important;
    height:18px !important;
    border-radius:999px !important;
    background:#ffffff !important;
    color:#2f5cf3 !important;
    font-size:16px !important;
    line-height:18px !important;
    font-weight:900 !important;
    text-align:center !important;
    box-shadow:0 3px 8px rgba(0,0,0,.18) !important;
}

/* Informasi layanan ikut dirapikan sedikit */
.info-card{
    background:#ffffff !important;
    border:1px solid #e7ebf4 !important;
    border-radius:28px !important;
    box-shadow:0 18px 40px rgba(28,53,112,.10) !important;
}

.info-card h2,
.info-card h3{
    color:#16213e !important;
    font-weight:900 !important;
}

.info-pill{
    border-radius:999px !important;
    min-height:56px !important;
    border:1px solid #e7ebf4 !important;
}

.info-pill.wa-pill{
    background:#f0fff5 !important;
    border-color:#bdeecb !important;
}

/* Tombol paket lama tetap hilang */
.btn.package-premium{
    display:none !important;
}

@media (max-width:480px){
    .customer-login-box{
        margin:22px 18px 16px !important;
    }

    .customer-login-card{
        padding:24px 20px !important;
        border-radius:26px !important;
    }

    .customer-login-icon-final{
        width:64px !important;
        height:64px !important;
        flex-basis:64px !important;
        border-radius:20px !important;
    }

    .customer-login-icon-final svg{
        width:30px !important;
        height:30px !important;
    }

    .customer-login-headtext b{
        font-size:22px !important;
    }

    .customer-login-headtext small{
        font-size:14px !important;
    }

    .customer-login-label{
        font-size:17px !important;
    }

    .customer-login-field-final{
        min-height:68px !important;
        border-radius:22px !important;
        padding:0 18px !important;
    }

    .customer-login-submit-final{
        min-height:68px !important;
        border-radius:22px !important;
    }

    .landing-quick-actions-final{
        margin:0 18px 20px !important;
        gap:14px !important;
    }

    .quick-btn-final{
        min-height:92px !important;
        border-radius:22px !important;
    }
}
</style>

</head>

<body>

<main class="page">
    <div class="phone">
        <section class="hero">
            <div class="topbar">
                <div class="brand">
                    <div class="logo">
                        @if($logo)
                            <img src="{{ asset('storage/'.$logo) }}" alt="{{ $businessName }}">
                        @else
                            <span class="initial">{{ $initial }}</span>
                        @endif
                    </div>

                    <div>
                        <b>{{ $appName }}</b>
                        <span>{{ $businessName }}</span>
                    </div>
                </div>

                <div class="menu-wrap" id="loginMenu">
                    <button class="hamburger" type="button" aria-label="Buka menu login" id="loginMenuButton">
                        <span></span>
                    </button>

                    <div class="dropdown" id="loginDropdown">
                        <a href="{{ url('/admin/login') }}" onclick="window.location.href=this.href; return false;">Login Admin <span>→</span></a>
                        <a href="{{ url('/collector/login') }}" onclick="window.location.href=this.href; return false;">Login Kasir <span>→</span></a>
                        <a href="{{ url('/technician/login') }}" onclick="window.location.href=this.href; return false;">Login Teknisi <span>→</span></a>
                    </div>

</div>
            </div>

            <h1>{{ $title }}</h1>
            <p class="lead">{{ $subtitle }}</p>
        </section>


<section class="customer-login-box" id="customerLoginBox">
    <div class="customer-login-card">
        <div class="customer-login-head">
            <span class="customer-login-icon-final">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M12 12c2.76 0 5-2.46 5-5.5S14.76 1 12 1 7 3.46 7 6.5 9.24 12 12 12Zm0 2c-4.42 0-8 2.69-8 6v1h16v-1c0-3.31-3.58-6-8-6Z"/>
                </svg>
            </span>

            <div class="customer-login-headtext">
                <b>Login Pelanggan</b>
                <small>Cek tagihan dan status layanan</small>
            </div>
        </div>

        <div class="customer-login-form-wrap">
            <label class="customer-login-label" for="customerNameFinal">User Pelanggan</label>
            <div class="customer-login-field-final">
                <span class="field-ico">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5Zm0 2c-4.42 0-8 1.79-8 4v2h16v-2c0-2.21-3.58-4-8-4Z"/>
                    </svg>
                </span>
                <input id="customerNameFinal" type="text" placeholder="Masukan nama pelanggan" autocomplete="username">
            </div>

            <label class="customer-login-label" for="customerPasswordFinal">Password</label>
            <div class="customer-login-field-final">
                <span class="field-ico">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M17 9h-1V7a4 4 0 1 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-7 0V7a2 2 0 1 1 4 0v2h-4Z"/>
                    </svg>
                </span>
                <input id="customerPasswordFinal" type="password" placeholder="Password pelanggan" autocomplete="current-password">
            </div>

            <button type="button" class="customer-login-submit-final" id="customerLoginButtonFinal">
                Login Pelanggan
            </button>
        </div>
    </div>
</section>

<div class="landing-quick-actions-final">
    <a class="quick-btn-final quick-btn-primary-final" href="{{ url('/order') }}">
        <span class="quick-user-badge-final">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5Zm0 2c-4.42 0-8 1.79-8 4v2h16v-2c0-2.21-3.58-4-8-4Z"/>
            </svg>
            <span class="plus-badge-final">+</span>
        </span>
        <span>Daftar Baru</span>
    </a>

    @if($waClean)
        <a class="quick-btn-final quick-btn-wa-final" href="{{ $waLink }}" target="_blank" rel="noopener">
            <span class="quick-wa-icon-final">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M20.52 3.48A11.8 11.8 0 0 0 12.08 0C5.56 0 .25 5.3.25 11.82c0 2.08.54 4.11 1.57 5.91L0 24l6.47-1.69a11.8 11.8 0 0 0 5.61 1.43h.01c6.52 0 11.83-5.3 11.83-11.82 0-3.16-1.23-6.12-3.4-8.44ZM12.09 21.7h-.01a9.85 9.85 0 0 1-5.02-1.37l-.36-.21-3.84 1 1.03-3.74-.23-.39a9.82 9.82 0 0 1-1.51-5.17c0-5.43 4.42-9.85 9.87-9.85 2.64 0 5.12 1.03 6.98 2.89a9.78 9.78 0 0 1 2.9 6.97c0 5.43-4.43 9.86-9.88 9.86Zm5.41-7.37c-.3-.15-1.77-.87-2.05-.97-.27-.1-.47-.15-.67.15s-.77.97-.95 1.17c-.17.2-.35.22-.65.07-.3-.15-1.25-.46-2.38-1.46-.88-.78-1.47-1.74-1.65-2.04-.17-.3-.02-.46.13-.61.14-.14.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.03-1.05 2.5s1.08 2.9 1.23 3.1c.15.2 2.11 3.23 5.11 4.53.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.43-.07-.12-.27-.2-.57-.35Z"/>
                </svg>
            </span>
            <span>WhatsApp</span>
        </a>
    @endif
</div>



        <section class="cta">
            <a class="btn main daftar-baru-white cta-icon-btn" href="{{ url('/order') }}">
                
<span class="cta-left-icon daftar-user-plus-icon">
    <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M10.5 11.5a4.2 4.2 0 1 0-4.2-4.2 4.2 4.2 0 0 0 4.2 4.2Zm0 2.1c-4.5 0-7.5 2.25-7.5 5.35v.45A1.6 1.6 0 0 0 4.6 21h11.8a1.6 1.6 0 0 0 1.6-1.6v-.45c0-3.1-3-5.35-7.5-5.35Z" fill="currentColor"/>
        <circle cx="18.2" cy="7.2" r="3.7" fill="#ffffff"/>
        <path d="M18.2 4.9v4.6M15.9 7.2h4.6" stroke="#2563eb" stroke-width="1.9" stroke-linecap="round"/>
    </svg>
</span>

                <span class="cta-center-text">Daftar Baru</span>
            </a>
            <a class="btn package-premium cta-icon-btn" href="{{ url('/packages') }}">
                <span class="cta-left-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 6h16"/>
                        <path d="M4 12h16"/>
                        <path d="M4 18h16"/>
                    </svg>
                </span>
                <span class="cta-center-text">Lihat Paket</span>
            </a>

            @if($waClean)
                <a class="btn wa cta-icon-btn whatsapp-icon-btn" href="{{ $waLink }}" target="_blank" rel="noopener">
                    <span class="cta-left-icon whatsapp-left-icon">
                        <svg class="waico" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12.04 3.5A8.45 8.45 0 0 0 4.8 16.3L4 20.5l4.3-.78A8.45 8.45 0 1 0 12.04 3.5Zm0 15.38a6.92 6.92 0 0 1-3.54-.97l-.25-.15-2.55.46.48-2.48-.16-.26a6.92 6.92 0 1 1 6.02 3.4Z"/>
                            <path d="M15.87 13.55c-.21-.1-1.25-.62-1.45-.69-.2-.07-.34-.1-.48.1-.14.21-.55.69-.67.83-.12.14-.25.16-.46.05-.21-.1-.89-.33-1.69-1.05-.62-.56-1.04-1.25-1.16-1.46-.12-.21-.01-.32.09-.43.09-.09.21-.25.31-.37.1-.12.14-.21.21-.35.07-.14.03-.26-.02-.37-.05-.1-.48-1.16-.66-1.59-.17-.42-.35-.36-.48-.37h-.41c-.14 0-.37.05-.56.26-.19.21-.74.72-.74 1.76s.76 2.04.86 2.18c.1.14 1.49 2.28 3.62 3.2.51.22.9.35 1.21.45.51.16.97.14 1.34.08.41-.06 1.25-.51 1.43-1 .18-.49.18-.91.12-1-.05-.09-.19-.14-.4-.25Z"/>
                        </svg>
                    </span>
                    <span class="cta-center-text">WhatsApp</span>
                </a>
            @endif
        </section>

        
        <section class="info info-compact">
            <h2>Informasi Layanan</h2>

            <div class="info-line">
                @if($phone || $waClean)
                    <a class="info-pill wa-pill" href="{{ $waClean ? $waLink : '#' }}" @if($waClean) target="_blank" rel="noopener" @endif>
                        <svg class="waico" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12.04 3.5A8.45 8.45 0 0 0 4.8 16.3L4 20.5l4.3-.78A8.45 8.45 0 1 0 12.04 3.5Zm0 15.38a6.92 6.92 0 0 1-3.54-.97l-.25-.15-2.55.46.48-2.48-.16-.26a6.92 6.92 0 1 1 6.02 3.4Z"/>
                            <path d="M15.87 13.55c-.21-.1-1.25-.62-1.45-.69-.2-.07-.34-.1-.48.1-.14.21-.55.69-.67.83-.12.14-.25.16-.46.05-.21-.1-.89-.33-1.69-1.05-.62-.56-1.04-1.25-1.16-1.46-.12-.21-.01-.32.09-.43.09-.09.21-.25.31-.37.1-.12.14-.21.21-.35.07-.14.03-.26-.02-.37-.05-.1-.48-1.16-.66-1.59-.17-.42-.35-.36-.48-.37h-.41c-.14 0-.37.05-.56.26-.19.21-.74.72-.74 1.76s.76 2.04.86 2.18c.1.14 1.49 2.28 3.62 3.2.51.22.9.35 1.21.45.51.16.97.14 1.34.08.41-.06 1.25-.51 1.43-1 .18-.49.18-.91.12-1-.05-.09-.19-.14-.4-.25Z"/>
                        </svg>
                        <span>{{ $phone ?: $wa }}</span>
                    </a>
                @endif

                @if($address)
                    <span class="info-pill">
                        <span class="text-icon">⌂</span>
                        <span>{{ $address }}</span>
                    </span>
                @endif

                @if($email)
                    <a class="info-pill" href="mailto:{{ $email }}">
                        <span class="text-icon">@</span>
                        <span>{{ $email }}</span>
                    </a>
                @endif

                @if(!$phone && !$waClean && !$address && !$email)
                    <span class="info-pill">
                        <span class="text-icon">i</span>
                        <span>Informasi layanan dapat diatur dari Pengaturan Umum.</span>
                    </span>
                @endif
            </div>
        </section>
</div>
</main>

<script id="hamburger-click-final-js">
(function(){
    var menu = document.getElementById('loginMenu');
    var button = document.getElementById('loginMenuButton');

    if(!menu || !button) return;

    button.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        menu.classList.toggle('open');
    });

    document.querySelectorAll('#loginDropdown a').forEach(function(link){
        link.addEventListener('click', function(e){
            e.preventDefault();
            e.stopPropagation();

            var href = this.getAttribute('href');
            if(href){
                window.location.assign(href);
            }
        }, true);
    });

    document.addEventListener('click', function(e){
        if(!menu.contains(e.target)){
            menu.classList.remove('open');
        }
    });
})();
</script>

<script id="landing-customer-login-final-script">
(function(){
    var btn = document.getElementById('customerLoginButtonFinal');
    var user = document.getElementById('customerNameFinal');
    var pass = document.getElementById('customerPasswordFinal');

    if(!btn) return;

    btn.addEventListener('click', function(){
        if(user && !user.value.trim()){
            alert('Nama pelanggan wajib diisi.');
            user.focus();
            return;
        }

        if(pass && !pass.value.trim()){
            alert('Password wajib diisi.');
            pass.focus();
            return;
        }

        alert('Login pelanggan sedang disiapkan.');
    });
})();
</script>

</body>
</html>
