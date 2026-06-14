{{-- percobaan-photo-url-helper-final-start --}}
@php
    $photoUrl = function ($path) {
        if (empty($path)) {
            return null;
        }

        $path = ltrim((string) $path, '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    };
@endphp
{{-- percobaan-photo-url-helper-final-end --}}

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Portal Percobaan Kasir')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root{
            --bg:#020713;
            --bg2:#06111f;
            --panel:#071123;
            --panel2:#0b1730;
            --gold:#f0d27a;
            --gold2:#fff0bb;
            --text:#f7f2dc;
            --muted:#c9c7bd;
            --line:rgba(240,210,122,.48);
            --green:#35d579;
            --red:#ff4257;
            --purple:#8357ff;
            --blue:#377cff;
        }

        *{box-sizing:border-box}

        html,body{
            margin:0;
            min-height:100%;
            font-family:Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 0% 0%, rgba(28,184,166,.22), transparent 30%),
                radial-gradient(circle at 100% 0%, rgba(255,170,55,.22), transparent 24%),
                radial-gradient(circle at 50% 40%, rgba(54,100,255,.10), transparent 36%),
                linear-gradient(180deg,#020713 0%,#041020 52%,#020713 100%);
            color:var(--text);
            color-scheme:dark;
        }

        body{
            padding-bottom:18px;
        }

        a{
            text-decoration:none;
            color:inherit;
        }

        .trial-shell{
            width:100%;
            max-width:820px;
            margin:0 auto;
            padding:14px 12px 18px;
        }

        .trial-topbar{
            position:sticky;
            top:0;
            z-index:80;
            padding:10px 12px;
            background:rgba(3,9,20,.88);
            border-bottom:1px solid rgba(240,210,122,.18);
            backdrop-filter:blur(18px);
        }

        .trial-topbar-inner{
            max-width:820px;
            margin:0 auto;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
        }

        .topbar-btn{
            width:48px;
            height:48px;
            border-radius:18px;
            border:1px solid rgba(240,210,122,.35);
            display:flex;
            align-items:center;
            justify-content:center;
            background:
                radial-gradient(circle at 30% 22%,rgba(255,255,255,.12),transparent 32%),
                linear-gradient(145deg,#101b2d,#06101f);
            color:var(--gold2);
            box-shadow:
                0 12px 24px rgba(0,0,0,.26),
                inset 0 1px 0 rgba(255,255,255,.08);
            cursor:pointer;
            padding:0;
        }

        .topbar-btn svg{
            width:24px;
            height:24px;
            stroke:currentColor;
            fill:none;
            stroke-width:2;
            stroke-linecap:round;
            stroke-linejoin:round;
        }
.profile-btn{
            width:48px;
            height:48px;
            border-radius:50%;
            border:1px solid rgba(240,210,122,.48);
            display:flex;
            align-items:center;
            justify-content:center;
            background:
                radial-gradient(circle at 32% 24%,rgba(255,255,255,.32),transparent 30%),
                linear-gradient(145deg,#ffe69c,#b98928);
            color:#302006;
            box-shadow:
                0 0 0 1px rgba(255,239,180,.25),
                0 0 24px rgba(240,210,122,.24),
                inset 0 2px 7px rgba(255,255,255,.35);
            cursor:pointer;
            padding:0;
            font-weight:950;
            overflow:hidden;
        }

        .profile-btn svg{
            width:25px;
            height:25px;
            stroke:currentColor;
            fill:none;
            stroke-width:2.2;
            stroke-linecap:round;
            stroke-linejoin:round;
        }

        .trial-drawer-backdrop{
            position:fixed;
            inset:0;
            z-index:100;
            background:rgba(0,0,0,.48);
            opacity:0;
            pointer-events:none;
            transition:.22s ease;
            backdrop-filter:blur(2px);
        }

        .trial-drawer{
            position:fixed;
            top:0;
            left:0;
            bottom:0;
            width:min(320px,82vw);
            z-index:101;
            transform:translateX(-105%);
            transition:.26s ease;
            padding:18px 14px;
            background:
                radial-gradient(circle at 0% 0%,rgba(28,184,166,.16),transparent 32%),
                linear-gradient(180deg,#07111f,#020713);
            border-right:1px solid rgba(240,210,122,.30);
            box-shadow:22px 0 44px rgba(0,0,0,.45);
        }

        body.drawer-open .trial-drawer-backdrop{
            opacity:1;
            pointer-events:auto;
        }

        body.drawer-open .trial-drawer{
            transform:translateX(0);
        }

        .drawer-head{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            padding-bottom:16px;
            border-bottom:1px solid rgba(240,210,122,.18);
        }

        .drawer-title b{
            display:block;
            font-size:16px;
            font-weight:950;
            color:var(--gold2);
        }

        .drawer-title span{
            display:block;
            margin-top:3px;
            font-size:12px;
            font-weight:800;
            color:var(--muted);
        }

        .drawer-close{
            width:40px;
            height:40px;
            border-radius:14px;
            border:1px solid rgba(240,210,122,.32);
            background:rgba(255,255,255,.04);
            color:var(--gold2);
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
        }

        .drawer-close svg{
            width:22px;
            height:22px;
            stroke:currentColor;
            stroke-width:2;
            fill:none;
            stroke-linecap:round;
        }

        .drawer-menu{
            margin-top:16px;
            display:grid;
            gap:10px;
        }

        .drawer-menu a{
            min-height:50px;
            border-radius:17px;
            display:flex;
            align-items:center;
            gap:10px;
            padding:0 14px;
            color:#201606;
            background:linear-gradient(145deg,#ffe9a3,#b98928);
            border:1px solid rgba(255,239,180,.62);
            font-weight:950;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.35);
        }

        .drawer-menu svg{
            width:22px;
            height:22px;
            stroke:currentColor;
            fill:none;
            stroke-width:2;
            stroke-linecap:round;
            stroke-linejoin:round;
        }

        ::-webkit-scrollbar{
            height:8px;
            width:8px;
        }

        ::-webkit-scrollbar-track{
            background:#06111f;
        }

        ::-webkit-scrollbar-thumb{
            border-radius:999px;
            background:linear-gradient(90deg,#2b66ff,#f0d27a);
        }

        @media(max-width:560px){
            .trial-shell{
                padding:12px 10px 18px;
            }

            .trial-topbar{
                padding:9px 12px;
            }

            .topbar-btn,
    
        .topbar-spacer{
            flex:1;
        }

        .profile-btn{
                width:44px;
                height:44px;
            }
}
    
/* portal-compact-topbar-v1 */

/* Header lebih ramping dan tombol tidak melebar */
.trial-topbar{
    padding:8px 12px !important;
}

.trial-topbar-inner{
    height:52px !important;
    min-height:52px !important;
    align-items:center !important;
    justify-content:space-between !important;
}

.topbar-btn{
    width:44px !important;
    height:44px !important;
    min-width:44px !important;
    max-width:44px !important;
    flex:0 0 44px !important;
    border-radius:16px !important;
    padding:0 !important;
}

.topbar-btn svg{
    width:22px !important;
    height:22px !important;
}

.profile-btn{
    width:44px !important;
    height:44px !important;
    min-width:44px !important;
    max-width:44px !important;
    flex:0 0 44px !important;
    border-radius:50% !important;
    padding:0 !important;
}

.profile-btn svg{
    width:23px !important;
    height:23px !important;
}

.topbar-spacer{
    flex:1 1 auto !important;
}

/* Pastikan tidak ada style tombol umum yang membuat hamburger jadi melebar */
.trial-topbar button{
    min-width:0 !important;
}

.trial-topbar .topbar-btn,
.trial-topbar .profile-btn{
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
}

/* Mobile */
@media(max-width:560px){
    .trial-topbar{
        padding:7px 10px !important;
    }

    .trial-topbar-inner{
        height:50px !important;
        min-height:50px !important;
    }

    .topbar-btn,
    .profile-btn{
        width:42px !important;
        height:42px !important;
        min-width:42px !important;
        max-width:42px !important;
        flex-basis:42px !important;
    }
}

/* end portal-compact-topbar-v1 */


        .drawer-logout-form{
            margin:0;
        }

        .drawer-logout-form button{
            width:100%;
            min-height:50px;
            border-radius:17px;
            display:flex;
            align-items:center;
            gap:10px;
            padding:0 14px;
            color:#ffdfdf;
            background:linear-gradient(145deg,rgba(255,89,99,.18),rgba(255,89,99,.08));
            border:1px solid rgba(255,89,99,.38);
            font-weight:950;
            cursor:pointer;
        }

        .drawer-logout-form svg{
            width:22px;
            height:22px;
            stroke:currentColor;
            fill:none;
            stroke-width:2;
            stroke-linecap:round;
            stroke-linejoin:round;
        }

</style>

    @stack('styles')

<style id="global-flash-popup-percobaan-style-v1">
    .global-flash-modal{
        position:fixed;
        inset:0;
        z-index:99999;
        display:none;
        align-items:center;
        justify-content:center;
        padding:20px;
    }

    .global-flash-modal.open{
        display:flex;
    }

    .global-flash-backdrop{
        position:absolute;
        inset:0;
        background:rgba(0,0,0,.62);
        backdrop-filter:blur(8px);
    }

    .global-flash-box{
        position:relative;
        z-index:2;
        width:min(360px,100%);
        border-radius:28px;
        padding:24px 18px 18px;
        text-align:center;
        background:
            radial-gradient(circle at 15% 0%,rgba(240,210,122,.16),transparent 34%),
            linear-gradient(145deg,#111d31,#071123);
        border:1px solid rgba(240,210,122,.34);
        box-shadow:
            0 26px 70px rgba(0,0,0,.58),
            inset 0 1px 0 rgba(255,255,255,.06);
        transform:translateY(8px) scale(.96);
        opacity:0;
        animation:globalFlashIn .22s ease forwards;
    }

    @keyframes globalFlashIn{
        to{
            transform:translateY(0) scale(1);
            opacity:1;
        }
    }

    .global-flash-icon{
        width:76px;
        height:76px;
        margin:0 auto 14px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        box-shadow:0 0 34px rgba(0,0,0,.30), inset 0 1px 0 rgba(255,255,255,.25);
    }

    .global-flash-modal.is-success .global-flash-icon{
        color:#092f1c;
        background:linear-gradient(145deg,#9dffc0,#35d579);
        border:1px solid rgba(157,255,192,.58);
    }

    .global-flash-modal.is-error .global-flash-icon{
        color:#4b0710;
        background:linear-gradient(145deg,#ff9aa5,#ff4257);
        border:1px solid rgba(255,154,165,.58);
    }

    .global-flash-icon svg{
        width:40px;
        height:40px;
        fill:none;
        stroke:currentColor;
        stroke-width:2.6;
        stroke-linecap:round;
        stroke-linejoin:round;
    }

    .global-flash-box h2{
        margin:0;
        color:#fff1c7;
        font-size:24px;
        line-height:1.1;
        font-weight:950;
    }

    .global-flash-box p{
        margin:10px auto 18px;
        color:#d8d4ca;
        font-size:14px;
        line-height:1.45;
        font-weight:800;
        word-break:break-word;
    }

    .global-flash-ok{
        width:100%;
        height:46px;
        border:0;
        border-radius:16px;
        cursor:pointer;
        color:#071123;
        font-size:15px;
        font-weight:950;
        background:linear-gradient(135deg,#fff1c7,#f0d27a);
        box-shadow:0 12px 28px rgba(240,210,122,.18);
    }

    .global-flash-ok:active{
        transform:scale(.98);
    }
</style>


<style id="profile-topbar-photo-style-v1">
.profile-btn img{
    width:100%;
    height:100%;
    object-fit:cover;
    border-radius:50%;
    display:block;
}
</style>

</head>
<body>
    <header class="trial-topbar">
        <div class="trial-topbar-inner">
            <button class="topbar-btn" type="button" onclick="document.body.classList.add('drawer-open')" aria-label="Buka sidebar">
                <svg viewBox="0 0 24 24">
                    <path d="M4 7h16"></path>
                    <path d="M4 12h16"></path>
                    <path d="M4 17h16"></path>
                </svg>
            </button>
<div class="topbar-spacer"></div>

                        <!-- profile-topbar-photo-v1-start -->
            <a class="profile-btn" href="{{ url('/kasir/profile') }}" aria-label="Profile">
                @php $topbarPhoto = optional(auth()->user())->profile_photo_path ?? null; @endphp
                @if($topbarPhoto)
                    <img src="{{ $photoUrl($topbarPhoto) }}" alt="Profile">
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21a8 8 0 0 0-16 0"></path>
                        <circle cx="12" cy="8" r="4"></circle>
                    </svg>
                @endif
            </a>
            <!-- profile-topbar-photo-v1-end -->
        </div>
    </header>

    <div class="trial-drawer-backdrop" onclick="document.body.classList.remove('drawer-open')"></div>

    <aside class="trial-drawer">
        <div class="drawer-head">
            <div class="drawer-title">
                <b>Portal Percobaan</b>
                <span>Menu desain baru</span>
            </div>

            <button class="drawer-close" type="button" onclick="document.body.classList.remove('drawer-open')" aria-label="Tutup sidebar">
                <svg viewBox="0 0 24 24">
                    <path d="M6 6l12 12"></path>
                    <path d="M18 6L6 18"></path>
                </svg>
            </button>
        </div>

        
        <nav class="drawer-menu">
            <a href="{{ url('/kasir') }}">
                <svg viewBox="0 0 24 24">
                    <path d="M4 10.5 12 4l8 6.5"></path>
                    <path d="M6 10v10h12V10"></path>
                </svg>
                Dashboard
            </a>

            <a href="{{ url('/kasir/tagihan') }}">
                <svg viewBox="0 0 24 24">
                    <path d="M7 3h10v18H7z"></path>
                    <path d="M9.5 8h5"></path>
                    <path d="M9.5 12h5"></path>
                    <path d="M9.5 16h3"></path>
                </svg>
                Tagihan
            </a>

            <a class="drawer-link {{ request()->is('collector/percobaan/status-pelanggan*') ? 'active' : '' }}" href="{{ url('/kasir/status-pelanggan') }}">
                <svg viewBox="0 0 24 24">
                    <path d="M4 7h16"></path>
                    <path d="M4 12h16"></path>
                    <path d="M4 17h10"></path>
                    <circle cx="18" cy="17" r="2"></circle>
                </svg>
                <span>Status Pelanggan</span>
            </a>


            <a href="{{ url('/kasir/tagihan-manual') }}">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14"></path>
                    <path d="M5 12h14"></path>
                </svg>
                Buat Tagihan Manual
            </a>

            <a href="{{ url('/kasir/pengeluaran') }}">
                <svg viewBox="0 0 24 24">
                    <path d="M4 8.5A2.5 2.5 0 0 1 6.5 6h12A1.5 1.5 0 0 1 20 7.5v10A2.5 2.5 0 0 1 17.5 20h-11A2.5 2.5 0 0 1 4 17.5z"></path>
                    <path d="M4 9h16"></path>
                    <path d="M16 14h.01"></path>
                </svg>
                Pengeluaran
            </a>

            <a href="{{ url('/kasir/riwayat') }}">
                <svg viewBox="0 0 24 24">
                    <path d="M3 12a9 9 0 1 0 3-6.7"></path>
                    <path d="M3 4v5h5"></path>
                    <path d="M12 7v6l4 2"></path>
                </svg>
                Riwayat
            </a>

            <a href="{{ url('/kasir/profile') }}">
                <svg viewBox="0 0 24 24">
                    <path d="M20 21a8 8 0 0 0-16 0"></path>
                    <circle cx="12" cy="8" r="4"></circle>
                </svg>
                Profile
            </a>

            <form class="drawer-logout-form" method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit">
                    <svg viewBox="0 0 24 24">
                        <path d="M10 17l5-5-5-5"></path>
                        <path d="M15 12H3"></path>
                        <path d="M21 3v18"></path>
                    </svg>
                    Keluar
                </button>
            </form>
        </nav>

    </aside>

    <main class="trial-shell">
        @yield('content')
    </main>

    
{{-- global-flash-popup-percobaan-v1-start --}}
@if(session('success') || session('error') || $errors->any())
    @php
        $isSuccess = session('success') && !session('error') && !$errors->any();
        $flashTitle = $isSuccess ? 'Berhasil' : 'Gagal';
        $flashMessage = session('success') ?? session('error') ?? $errors->first();
    @endphp

    <div class="global-flash-modal {{ $isSuccess ? 'is-success' : 'is-error' }}" id="globalFlashModal" aria-hidden="true">
        <div class="global-flash-backdrop js-global-flash-close"></div>

        <div class="global-flash-box">
            <div class="global-flash-icon">
                @if($isSuccess)
                    <svg viewBox="0 0 24 24">
                        <path d="M20 6 9 17l-5-5"></path>
                    </svg>
                @else
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 8v5"></path>
                        <path d="M12 17h.01"></path>
                    </svg>
                @endif
            </div>

            <h2>{{ $flashTitle }}</h2>
            <p>{{ $flashMessage }}</p>

            <button type="button" class="global-flash-ok js-global-flash-close">OK</button>
        </div>
    </div>
@endif
{{-- global-flash-popup-percobaan-v1-end --}}


@stack('scripts')

<script id="global-flash-popup-percobaan-script-v1">
(function(){
    const modal = document.getElementById('globalFlashModal');
    if(!modal) return;

    function openFlash(){
        modal.classList.add('open');
        modal.setAttribute('aria-hidden','false');
        document.body.style.overflow = 'hidden';
    }

    function closeFlash(){
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden','true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.js-global-flash-close').forEach(function(btn){
        btn.addEventListener('click', closeFlash);
    });

    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape') closeFlash();
    });

    setTimeout(openFlash, 160);
})();
</script>

</body>
</html>
