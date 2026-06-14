<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $appSettings['app_name'] ?? 'MAC Billing')</title>

    @if(!empty($appSettings['business_favicon']))
        <link rel="icon" href="{{ asset('storage/'.$appSettings['business_favicon']) }}">
    @endif

    <style>
        :root{
            --bg:#f4f7fb;
            --bg2:#eef4ff;
            --card:#ffffff;
            --ink:#101828;
            --muted:#667085;
            --line:#e4eaf3;
            --soft:#f8fafc;
            --nav:#0b1b3a;
            --nav2:#102a5c;
            --blue:#2563eb;
            --blue2:#1d4ed8;
            --cyan:#06b6d4;
            --green:#027a48;
            --red:#b42318;
            --yellow:#b54708;
            --shadow:0 14px 36px rgba(16,24,40,.075);
            --shadow-sm:0 8px 22px rgba(16,24,40,.055);
            --radius:22px;
            --side:276px;
            --top:68px;
        }

        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:var(--ink);-webkit-font-smoothing:antialiased}
        body{
            background:
                radial-gradient(circle at 10% 0%, rgba(37,99,235,.09), transparent 28%),
                radial-gradient(circle at 90% 4%, rgba(6,182,212,.08), transparent 30%),
                linear-gradient(180deg,#f8fbff 0%,var(--bg2) 100%);
        }
        a{color:inherit;text-decoration:none}
        button,input,select,textarea{font:inherit}
        img{max-width:100%}
        .small{font-size:12px;color:var(--muted);font-weight:750;line-height:1.35}

        .app{min-height:100vh;display:flex}
        .overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.48);z-index:55}
        body.sidebar-open .overlay{display:block}

        .sidebar{
            width:var(--side);
            position:fixed;
            inset:0 auto 0 0;
            z-index:60;
            overflow-y:auto;
            overflow-x:hidden;
            padding:15px 13px 18px;
            background:
                radial-gradient(circle at 18% 0%, rgba(96,165,250,.24), transparent 34%),
                linear-gradient(180deg,var(--nav) 0%,var(--nav2) 100%);
            color:#fff;
            border-right:1px solid rgba(255,255,255,.10);
            box-shadow:18px 0 50px rgba(15,23,42,.16);
            transition:transform .2s ease;
        }

        .brand{
            min-height:58px;
            display:flex;
            align-items:center;
            gap:11px;
            padding:8px 8px 14px;
            margin-bottom:8px;
            border-bottom:1px solid rgba(255,255,255,.10);
        }
        .brandmark{
            width:42px;height:42px;flex:0 0 42px;border-radius:15px;
            display:grid;place-items:center;
            background:linear-gradient(135deg,var(--blue),var(--cyan));
            color:#fff;font-weight:950;letter-spacing:-.06em;
            box-shadow:0 14px 28px rgba(37,99,235,.24);
        }
        .brandtext{min-width:0}
        .brandtext b{display:block;font-size:16px;line-height:1.1;letter-spacing:-.04em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .brandtext span{display:block;margin-top:4px;color:#b6c7e6;font-size:11px;line-height:1.25}

        .nav-title{
            margin:14px 10px 7px;
            color:#b6c7e6;
            font-size:10px;
            font-weight:900;
            text-transform:uppercase;
            letter-spacing:.15em;
        }
        .nav{display:flex;flex-direction:column;gap:5px}
        .navitem,.nav a{
            min-height:41px;
            display:flex;
            align-items:center;
            gap:10px;
            padding:9px 10px;
            border-radius:14px;
            color:#dbeafe;
            font-size:13px;
            font-weight:850;
            line-height:1.2;
            transition:.15s ease;
        }
        .navitem:hover,.nav a:hover{background:rgba(96,165,250,.12);color:#fff}
        .navitem.active,.nav a.active{background:rgba(96,165,250,.18);color:#fff;box-shadow:inset 3px 0 0 #60a5fa}
        .ico{
            width:24px;height:24px;min-width:24px;border-radius:9px;
            display:inline-grid;place-items:center;
            background:rgba(96,165,250,.16);
            color:#dbeafe;
        }
        .ico svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2.25;stroke-linecap:round;stroke-linejoin:round}

        .side-group{margin:0}
        .side-group summary{list-style:none;cursor:pointer}
        .side-group summary::-webkit-details-marker{display:none}
        .side-chev{margin-left:auto;transition:transform .16s ease;color:#b6c7e6}
        .side-group[open] .side-chev{transform:rotate(90deg);color:#fff}
        .side-sub{
            display:grid;
            gap:4px;
            margin:5px 0 6px 34px;
            padding-left:9px;
            border-left:1px solid rgba(191,219,254,.22);
        }
        .side-sub a{
            min-height:34px;
            display:flex;
            align-items:center;
            padding:8px 10px;
            border-radius:12px;
            color:#c7d7f2;
            font-size:12px;
            font-weight:850;
        }
        .side-sub a:hover{background:rgba(96,165,250,.12);color:#fff}
        .side-sub a.active{background:#eff6ff;color:#175cd3}

        .sidebar-foot{
            margin-top:14px;
            padding-top:12px;
            border-top:1px solid rgba(255,255,255,.10);
        }
        .userbox{
            display:flex;
            align-items:center;
            gap:10px;
            padding:10px;
            border-radius:18px;
            background:rgba(255,255,255,.08);
        }
        .avatar{
            width:34px;height:34px;border-radius:14px;
            display:grid;place-items:center;
            background:#fff;color:#0f172a;font-weight:950;
        }
        .userbox b{display:block;max-width:158px;font-size:13px;line-height:1.1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .userbox span{display:block;margin-top:2px;color:#b6c7e6;font-size:11px}

        .main{flex:1;min-width:0;margin-left:var(--side)}
        .topbar{
            position:sticky;top:0;z-index:40;
            min-height:var(--top);
            display:flex;align-items:center;justify-content:space-between;gap:12px;
            padding:11px 22px;
            background:rgba(248,251,255,.92);
            border-bottom:1px solid rgba(228,234,243,.92);
            backdrop-filter:blur(18px);
            box-shadow:0 8px 24px rgba(16,24,40,.05);
        }
        .top-left{display:flex;align-items:center;gap:12px;min-width:0}
        .hamb{
            display:none;
            width:42px;height:42px;
            border:1px solid var(--line);
            border-radius:15px;
            background:#fff;
            color:#175cd3;
            cursor:pointer;
            box-shadow:var(--shadow-sm);
        }
        .hamb svg{width:20px;height:20px;stroke:currentColor}
        .pagetitle{min-width:0}
        .pagetitle b{display:block;font-size:18px;line-height:1.1;letter-spacing:-.04em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .pagetitle span{display:block;margin-top:3px;color:var(--muted);font-size:12px;font-weight:750}
        .logout{
            min-height:42px;
            border:1px solid var(--line);
            border-radius:16px;
            background:#fff;
            color:var(--ink);
            cursor:pointer;
            padding:9px 15px;
            font-weight:900;
            box-shadow:var(--shadow-sm);
        }

        .content{width:100%;max-width:1340px;margin:0 auto;padding:20px 22px 44px}
        .pagehead{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin:0 0 14px}
        .pagehead h1{margin:0;font-size:25px;line-height:1.12;letter-spacing:-.055em}
        .pagehead p{margin:6px 0 0;color:var(--muted);font-size:14px;line-height:1.4;font-weight:700}
        .actions,.neo-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}

        .hero{
            background:linear-gradient(135deg,var(--blue2),#0891b2);
            color:#fff;
            border-radius:28px;
            padding:26px;
            margin-bottom:16px;
            box-shadow:var(--shadow);
            border:1px solid rgba(255,255,255,.22);
        }
        .hero span{display:block;color:#e0f2fe;font-size:14px;margin-bottom:8px}
        .hero b{display:block;font-size:33px;line-height:1.05;letter-spacing:-.06em}
        .hero p{color:#e0f2fe}

        .grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
        .card,.menucard,.tablewrap,.neo-search,.neo-xls,.searchbar{
            background:#fff;
            border:1px solid var(--line);
            border-radius:var(--radius);
            box-shadow:var(--shadow-sm);
        }
        .card{padding:20px}
        a.card{display:block}
        .label{color:var(--muted);font-size:13px;line-height:1.25;font-weight:750}
        .val{margin-top:6px;color:var(--ink);font-size:27px;line-height:1.05;letter-spacing:-.05em;font-weight:950}
        .muted{color:var(--muted);font-size:13px;line-height:1.45}
        .main-text{font-weight:850;color:var(--ink)}

        .menu{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:18px}
        .menucard{min-height:112px;padding:20px;transition:.15s ease}
        .menucard:hover{transform:translateY(-1px);box-shadow:var(--shadow)}
        .menucard b{display:block;margin-bottom:8px;color:var(--ink);font-size:17px;letter-spacing:-.03em}
        .menucard span{display:block;color:var(--muted);font-size:14px;line-height:1.45}

        .btn{
            display:inline-flex;align-items:center;justify-content:center;gap:7px;
            min-height:42px;
            padding:10px 16px;
            border:1px solid rgba(37,99,235,.12);
            border-radius:16px;
            background:linear-gradient(135deg,var(--blue),var(--blue2));
            color:#fff;
            font-size:14px;
            font-weight:900;
            cursor:pointer;
            white-space:nowrap;
            box-shadow:0 10px 22px rgba(37,99,235,.18);
        }
        .btn:hover{filter:brightness(1.03)}
        .btn.light{background:#fff;color:var(--ink);border:1px solid var(--line);box-shadow:var(--shadow-sm)}
        .btn.red{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;border-color:rgba(220,38,38,.14);box-shadow:none}
        .btn.icon,.btn-icon{
            width:31px;height:31px;min-width:31px;min-height:31px;padding:0;border-radius:10px;
            display:inline-grid;place-items:center;font-size:0;gap:0;
        }
        .btn.icon svg,.btn-icon svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2.25;stroke-linecap:round;stroke-linejoin:round}

        .alert{padding:13px 15px;border-radius:18px;margin:0 0 14px;font-weight:800;font-size:14px;line-height:1.4}
        .alert.ok{background:#ecfdf3;color:#027a48;border:1px solid #abefc6}
        .alert.err{background:#fef3f2;color:#b42318;border:1px solid #fecdca}

        .badge{
            display:inline-flex;align-items:center;justify-content:center;gap:5px;
            min-height:24px;padding:4px 9px;border-radius:999px;
            font-size:12px;font-weight:950;white-space:nowrap;
        }
        .badge.green{background:#ecfdf3;color:#027a48;border:1px solid #abefc6}
        .badge.red{background:#fef3f2;color:#b42318;border:1px solid #fecdca}
        .badge.yellow{background:#fffaeb;color:#b54708;border:1px solid #fedf89}
        .badge.blue{background:#eff6ff;color:#175cd3;border:1px solid #b2ddff}

        .formgrid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .field{display:flex;flex-direction:column;gap:7px;margin-bottom:0}
        .field.full{grid-column:1/-1}
        .field label{color:#344054;font-weight:850;font-size:13px}
        .input,.select,.textarea,input,select,textarea{
            width:100%;
            min-height:46px;
            border:1px solid var(--line);
            border-radius:16px;
            padding:11px 13px;
            background:#fff;
            color:var(--ink);
            outline:none;
        }
        .textarea,textarea{min-height:92px;resize:vertical}
        .input:focus,.select:focus,.textarea:focus,input:focus,select:focus,textarea:focus{
            border-color:rgba(37,99,235,.65);
            box-shadow:0 0 0 4px rgba(37,99,235,.08);
        }
        .input::placeholder,textarea::placeholder{color:#98a2b3}

        .searchbar{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;padding:12px;margin-bottom:14px}
        .neo-search{
            display:flex;
            gap:7px;
            padding:7px;
            margin-bottom:10px;
            border-radius:18px;
        }
        .neo-search .input,.neo-search .select{min-height:38px;border-radius:13px;font-size:13px}
        .neo-search .input{flex:1;min-width:0}
        .neo-search .btn{min-height:38px;border-radius:13px;box-shadow:none}

        .tablewrap{overflow:hidden}
        .scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}
        .table{min-width:820px}
        .tr{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));border-bottom:1px solid #f1f5f9}
        .tr.head{background:#f8fafc;color:#475467;font-weight:950;font-size:12px;text-transform:uppercase;letter-spacing:.06em}
        .td{padding:13px 14px;min-width:0;font-size:14px;line-height:1.35}

        .neo-dd{position:relative;display:inline-block}
        .neo-dd summary{list-style:none;cursor:pointer}
        .neo-dd summary::-webkit-details-marker{display:none}
        .neo-dd-menu{
            position:absolute;right:0;top:calc(100% + 8px);z-index:9999;
            width:236px;
            padding:7px;
            border-radius:16px;
            background:#fff;
            border:1px solid var(--line);
            box-shadow:0 18px 42px rgba(16,24,40,.18);
        }
        .neo-dd-menu a,.neo-dd-menu button{
            width:100%;
            display:flex;
            align-items:center;
            gap:8px;
            padding:10px 11px;
            border:0;
            background:transparent;
            border-radius:12px;
            color:var(--ink);
            font-size:13px;
            font-weight:850;
            text-align:left;
            cursor:pointer;
        }
        .neo-dd-menu a:hover,.neo-dd-menu button:hover{background:#f8fafc}
        .neo-dd-menu .primary{background:#eff6ff;color:#175cd3}
        .neo-dd-menu .divider{height:1px;background:#eef2f6;margin:5px 4px}

        .neo-xls{overflow:hidden;border-radius:20px}
        .neo-xls-info{
            display:flex;justify-content:space-between;align-items:center;gap:10px;
            padding:9px 11px;
            background:#f8fafc;
            border-bottom:1px solid var(--line);
            color:var(--muted);
            font-size:12px;
            font-weight:750;
        }
        .neo-xls-scroll{overflow:auto;-webkit-overflow-scrolling:touch;max-height:calc(100vh - 245px)}
        .neo-xls-table{
            width:100%;
            min-width:1100px;
            border-collapse:separate;
            border-spacing:0;
            font-size:13px;
        }
        .neo-xls-table th{
            position:sticky;top:0;z-index:5;
            background:linear-gradient(180deg,#ffffff,#f5f7fb);
            color:#667085;
            text-align:left;
            font-size:11px;
            text-transform:uppercase;
            letter-spacing:.055em;
            font-weight:950;
            padding:10px 9px;
            border-bottom:1px solid #d0d5dd;
            border-right:1px solid #eaecf0;
            white-space:nowrap;
        }
        .neo-xls-table td{
            padding:8px 9px;
            border-bottom:1px solid #f2f4f7;
            border-right:1px solid #f2f4f7;
            vertical-align:middle;
            white-space:nowrap;
            line-height:1.25;
            background:#fff;
            color:var(--ink);
        }
        .neo-xls-table tbody tr:nth-child(even) td{background:#fcfdff}
        .neo-xls-table tr:hover td{background:#eff6ff}
        .neo-xls-table .sticky-left{position:sticky;left:0;z-index:4;background:inherit}
        .neo-xls-table th.sticky-left{z-index:7}
        .neo-id{width:54px;text-align:center;color:#667085;font-weight:950}
        .neo-strong{font-weight:950;color:var(--ink)}
        .neo-money{text-align:right;font-weight:950;color:var(--ink)}
        .neo-clip{max-width:260px;overflow:hidden;text-overflow:ellipsis}
        .neo-row-actions,.row-actions,.excel-actions{display:flex;gap:5px;align-items:center;flex-wrap:nowrap}
        .neo-row-actions form,.row-actions form,.excel-actions form{margin:0}
        .neo-row-actions .btn{min-height:29px;padding:5px 8px;border-radius:10px;font-size:11px;box-shadow:none}

        .pagination{margin-top:14px}
        .mobile-bottom{display:none}

        *::-webkit-scrollbar{height:8px;width:8px}
        *::-webkit-scrollbar-track{background:#f2f4f7}
        *::-webkit-scrollbar-thumb{background:linear-gradient(135deg,var(--blue),var(--cyan));border-radius:999px}

        @media(max-width:1080px){
            .grid{grid-template-columns:repeat(2,minmax(0,1fr))}
            .menu{grid-template-columns:repeat(2,minmax(0,1fr))}
            .searchbar{grid-template-columns:repeat(2,minmax(0,1fr))}
        }

        @media(max-width:760px){
            :root{--top:58px;--radius:20px}
            .sidebar{transform:translateX(-105%);width:292px;max-width:86vw;padding-bottom:100px}
            body.sidebar-open .sidebar{transform:translateX(0)}
            .main{margin-left:0;width:100%;padding-bottom:76px}
            .topbar{min-height:58px;padding:8px 12px}
            .hamb{display:grid;place-items:center;width:40px;height:40px;box-shadow:none}
            .pagetitle b{font-size:16px}
            .pagetitle span{display:none}
            .logout{min-height:40px;padding:8px 13px;border-radius:14px;font-size:13px;box-shadow:none}
            .content{padding:12px 10px 28px}
            .pagehead{flex-direction:column;align-items:stretch;gap:8px;margin-bottom:10px}
            .pagehead h1{font-size:21px}
            .pagehead p{font-size:12px;margin-top:4px}
            .actions,.neo-actions{width:100%;display:flex;flex-wrap:nowrap;overflow-x:auto;gap:7px;padding-bottom:3px;-webkit-overflow-scrolling:touch}
            .actions::-webkit-scrollbar,.neo-actions::-webkit-scrollbar{display:none}
            .actions .btn,.neo-actions .btn,.neo-actions summary.btn{flex:0 0 auto;min-height:36px;padding:8px 12px;border-radius:13px;font-size:12px;box-shadow:none}
            .hero{padding:18px;border-radius:22px;margin-bottom:10px}
            .hero b{font-size:25px}
            .grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
            .card{padding:13px;border-radius:18px}
            .label{font-size:11px}
            .val{font-size:21px;margin-top:4px}
            .menu{grid-template-columns:1fr;gap:8px;margin-top:10px}
            .menucard{min-height:82px;padding:14px;border-radius:18px}
            .menucard b{font-size:15px;margin-bottom:4px}
            .menucard span{font-size:12px;line-height:1.35}
            .formgrid{grid-template-columns:1fr;gap:12px}
            .searchbar{grid-template-columns:1fr;gap:8px;padding:8px;border-radius:18px}
            .neo-search{display:grid;grid-template-columns:1fr;gap:7px;padding:7px;border-radius:18px}
            .btn{min-height:40px;padding:9px 12px;border-radius:14px;font-size:13px}
            .input,.select,.textarea,input,select,textarea{min-height:42px;border-radius:14px;font-size:13px}
            .neo-dd-menu{left:0;right:auto;width:210px}
            .neo-xls-scroll{max-height:calc(100vh - 230px)}
            .neo-xls-table{min-width:1050px;font-size:12px}
            .neo-xls-table th{padding:8px 7px;font-size:10px}
            .neo-xls-table td{padding:7px}
            .neo-xls-info{padding:7px 9px;font-size:11px}
            .neo-row-actions .btn{min-height:27px;padding:5px 7px;font-size:10px;border-radius:9px}

            .mobile-bottom{
                position:fixed;
                left:8px;right:8px;bottom:7px;
                height:54px;
                display:grid;
                grid-template-columns:repeat(4,1fr);
                gap:5px;
                padding:5px;
                border-radius:20px;
                background:rgba(255,255,255,.97);
                border:1px solid #dbe5f2;
                box-shadow:0 14px 34px rgba(16,24,40,.16);
                backdrop-filter:blur(18px);
                z-index:45;
            }
            .mobile-bottom a{
                display:flex;
                flex-direction:column;
                align-items:center;
                justify-content:center;
                gap:2px;
                border-radius:16px;
                color:#475467;
                font-size:10px;
                font-weight:850;
                min-width:0;
            }
            .mobile-bottom a .ico{
                width:22px;height:22px;min-width:22px;border-radius:9px;
                background:#eff6ff;color:#2563eb;border:1px solid #dbeafe;
            }
            .mobile-bottom a.active{background:#eaf2ff;color:#175cd3}
            .mobile-bottom a.active .ico{background:linear-gradient(135deg,var(--blue),var(--cyan));color:#fff;border-color:transparent}
        }

        @media(max-width:390px){
            .grid{grid-template-columns:1fr}
            .val{font-size:23px}
        }
    </style>

    
<style id="ui-polish-no-text-change">
/* UI polish only. Tidak mengubah teks, bahasa, route, atau logika. */

/* Halaman lebih padat dan rapi */
.content{
    animation: uiFade .14s ease-out;
}
@keyframes uiFade{
    from{opacity:.65;transform:translateY(3px)}
    to{opacity:1;transform:none}
}

/* Card dan tabel lebih bersih */
.card,
.menucard,
.tablewrap,
.neo-search,
.neo-xls,
.searchbar{
    border-color:#dfe7f3;
}

.card:hover,
.menucard:hover{
    border-color:#cbd5e1;
}

/* Button lebih konsisten */
.btn,
.logout,
.hamb{
    transition:transform .12s ease, box-shadow .12s ease, filter .12s ease, background .12s ease;
}
.btn:active,
.logout:active,
.hamb:active{
    transform:scale(.985);
}

/* Action button di tabel jangan terlalu besar */
.neo-row-actions .btn,
.row-actions .btn,
.excel-actions .btn{
    min-height:30px;
    padding:6px 9px;
    border-radius:11px;
    font-size:11px;
    line-height:1;
}

/* Tabel lebih nyaman dibaca */
.neo-xls-table th{
    height:42px;
}
.neo-xls-table td{
    height:44px;
}
.neo-xls-table td,
.neo-xls-table th{
    vertical-align:middle;
}

/* Badge tidak membuat tinggi baris melonjak */
.badge{
    line-height:1;
}

/* Input filter lebih seimbang */
.neo-search{
    align-items:center;
}
.neo-search .btn{
    min-width:86px;
}

/* Dropdown tidak ketutup area tabel */
.neo-dd-menu{
    z-index:10050;
}

/* Mobile polish */
@media(max-width:760px){
    body{
        background:
            radial-gradient(circle at 0% 0%, rgba(37,99,235,.08), transparent 32%),
            linear-gradient(180deg,#f8fbff 0%,#edf4ff 100%);
    }

    .topbar{
        border-bottom-color:#dbe5f2;
    }

    .content{
        padding-left:9px;
        padding-right:9px;
    }

    .pagehead{
        gap:6px;
    }

    .pagehead h1{
        font-size:22px;
        letter-spacing:-.06em;
    }

    .pagehead p{
        max-width:96%;
    }

    .card{
        box-shadow:0 8px 18px rgba(16,24,40,.045);
    }

    .neo-xls{
        border-radius:20px;
        overflow:hidden;
    }

    .neo-xls-scroll{
        max-height:calc(100vh - 210px);
        overflow:auto;
        -webkit-overflow-scrolling:touch;
        overscroll-behavior:contain;
    }

    .neo-xls-table{
        min-width:980px;
    }

    .neo-xls-table th{
        height:38px;
        padding:8px 7px;
        font-size:10px;
    }

    .neo-xls-table td{
        height:40px;
        padding:7px;
        font-size:12px;
    }

    .neo-row-actions,
    .row-actions,
    .excel-actions{
        gap:4px;
    }

    .neo-row-actions .btn,
    .row-actions .btn,
    .excel-actions .btn{
        min-height:30px;
        min-width:34px;
        padding:6px 8px;
        border-radius:11px;
    }

    .badge{
        min-height:22px;
        padding:4px 8px;
        font-size:10px;
    }

    .neo-search{
        margin-bottom:9px;
    }

    .neo-search .btn{
        width:100%;
    }

    .mobile-bottom{
        left:10px;
        right:10px;
        bottom:8px;
    }

    .mobile-bottom a{
        line-height:1.05;
    }

    .sidebar{
        box-shadow:20px 0 60px rgba(15,23,42,.26);
    }
}

/* Layar sangat kecil */
@media(max-width:380px){
    .mobile-bottom{
        grid-template-columns:repeat(4,1fr);
        gap:3px;
        padding:4px;
    }

    .mobile-bottom a{
        font-size:9px;
    }

    .mobile-bottom a .ico{
        width:20px;
        height:20px;
        min-width:20px;
    }

    .neo-xls-table{
        min-width:940px;
    }
}
</style>


    @stack('styles')


<style id="collector-header-button-fix">
.top-actions{
    display:flex !important;
    align-items:center !important;
    gap:6px !important;
    flex-shrink:0 !important;
}
.top-actions .logout{
    min-height:38px !important;
    height:38px !important;
    padding:0 13px !important;
    border-radius:14px !important;
    font-size:14px !important;
    font-weight:900 !important;
    line-height:36px !important;
    white-space:nowrap !important;
}
@media(max-width:760px){
    .top-actions .logout{
        min-height:36px !important;
        height:36px !important;
        padding:0 11px !important;
        border-radius:13px !important;
        font-size:13px !important;
        line-height:34px !important;
    }
}
</style>
<style id="collector-header-final-fix">
/* Final fix tombol header: Kembali + Keluar */
.topbar{
    display:flex !important;
    align-items:center !important;
    justify-content:space-between !important;
    gap:8px !important;
}

.top-left{
    flex:1 1 auto !important;
    min-width:0 !important;
    display:flex !important;
    align-items:center !important;
    gap:9px !important;
}

.pagetitle{
    min-width:0 !important;
    flex:1 1 auto !important;
}

.pagetitle b{
    white-space:nowrap !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
}

.top-actions{
    flex:0 0 auto !important;
    display:flex !important;
    align-items:center !important;
    justify-content:flex-end !important;
    gap:6px !important;
    margin-left:auto !important;
}

.top-actions form{
    margin:0 !important;
    padding:0 !important;
}

.top-actions .logout{
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    height:36px !important;
    min-height:36px !important;
    padding:0 11px !important;
    border-radius:13px !important;
    font-size:13px !important;
    font-weight:900 !important;
    line-height:1 !important;
    white-space:nowrap !important;
    box-shadow:none !important;
}

.top-actions a.logout{
    min-width:88px !important;
    max-width:92px !important;
}

.top-actions form .logout{
    min-width:68px !important;
    max-width:72px !important;
}

@media(max-width:760px){
    .topbar{
        min-height:54px !important;
        padding:7px 12px !important;
        gap:7px !important;
    }

    .hamb{
        width:38px !important;
        height:38px !important;
        min-width:38px !important;
        flex:0 0 38px !important;
        border-radius:14px !important;
    }

    .top-left{
        gap:8px !important;
    }

    .pagetitle b{
        font-size:16px !important;
        max-width:120px !important;
    }

    .top-actions{
        gap:5px !important;
    }

    .top-actions .logout{
        height:34px !important;
        min-height:34px !important;
        padding:0 9px !important;
        border-radius:12px !important;
        font-size:12px !important;
    }

    .top-actions a.logout{
        min-width:82px !important;
        max-width:86px !important;
    }

    .top-actions form .logout{
        min-width:64px !important;
        max-width:68px !important;
    }
}

@media(max-width:380px){
    .topbar{
        padding-left:10px !important;
        padding-right:10px !important;
    }

    .pagetitle b{
        max-width:90px !important;
        font-size:15px !important;
    }

    .top-actions .logout{
        height:33px !important;
        min-height:33px !important;
        padding:0 8px !important;
        font-size:11.5px !important;
    }

    .top-actions a.logout{
        min-width:78px !important;
        max-width:82px !important;
    }

    .top-actions form .logout{
        min-width:60px !important;
        max-width:64px !important;
    }
}
</style>
<style id="collector-shared-header-final">
.topbar{
    display:flex !important;
    align-items:center !important;
    justify-content:space-between !important;
    gap:8px !important;
}
.top-left{
    flex:1 1 auto !important;
    min-width:0 !important;
    display:flex !important;
    align-items:center !important;
    gap:9px !important;
}
.pagetitle{
    min-width:0 !important;
    flex:1 1 auto !important;
}
.pagetitle b{
    white-space:nowrap !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
}
.top-actions{
    flex:0 0 auto !important;
    display:flex !important;
    align-items:center !important;
    justify-content:flex-end !important;
    gap:6px !important;
    margin-left:auto !important;
}
.top-actions form{
    margin:0 !important;
    padding:0 !important;
}
.top-actions .logout{
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    height:34px !important;
    min-height:34px !important;
    padding:0 10px !important;
    border-radius:12px !important;
    font-size:12px !important;
    font-weight:900 !important;
    line-height:1 !important;
    white-space:nowrap !important;
    box-shadow:none !important;
}
.top-actions a.logout{
    min-width:78px !important;
    max-width:86px !important;
}
.top-actions form .logout{
    min-width:62px !important;
    max-width:68px !important;
}
.top-actions .top-add-btn{
    min-width:58px !important;
    max-width:66px !important;
}
@media(max-width:760px){
    .topbar{
        min-height:54px !important;
        padding:7px 10px !important;
        gap:6px !important;
    }
    .hamb{
        width:38px !important;
        height:38px !important;
        min-width:38px !important;
        flex:0 0 38px !important;
        border-radius:14px !important;
    }
    .top-left{
        gap:8px !important;
    }
    .pagetitle b{
        font-size:15px !important;
        max-width:95px !important;
    }
    .top-actions{
        gap:5px !important;
    }
    .top-actions .logout{
        height:33px !important;
        min-height:33px !important;
        padding:0 8px !important;
        border-radius:11px !important;
        font-size:11.5px !important;
    }
    .top-actions a.logout{
        min-width:74px !important;
        max-width:80px !important;
    }
    .top-actions form .logout{
        min-width:58px !important;
        max-width:64px !important;
    }
    .top-actions .top-add-btn{
        min-width:50px !important;
        max-width:58px !important;
    }
}
</style>
<style id="premium-page-transition-style">
/* Premium Page Transition */
html{
    background:#f3f6fb;
}

body{
    opacity:0;
    transform:translateY(8px);
    filter:blur(2px);
    transition:
        opacity .28s ease,
        transform .28s ease,
        filter .28s ease;
}

body.page-ready{
    opacity:1;
    transform:translateY(0);
    filter:blur(0);
}

body.page-leaving{
    opacity:.35;
    transform:translateY(-6px);
    filter:blur(3px);
    pointer-events:none;
}

/* Top progress premium */
.neo-page-progress{
    position:fixed;
    top:0;
    left:0;
    width:0;
    height:3px;
    z-index:99999;
    background:linear-gradient(90deg,#2563eb,#0ea5e9,#22c55e);
    box-shadow:0 0 18px rgba(37,99,235,.45);
    opacity:0;
    transition:
        width .35s ease,
        opacity .2s ease;
}

body.page-leaving .neo-page-progress,
body.page-submitting .neo-page-progress{
    opacity:1;
    width:82%;
}

/* Soft overlay saat pindah halaman */
.neo-page-soft-overlay{
    position:fixed;
    inset:0;
    z-index:99998;
    pointer-events:none;
    opacity:0;
    background:
        radial-gradient(circle at 50% 10%, rgba(37,99,235,.10), transparent 35%),
        rgba(248,250,252,.40);
    backdrop-filter:blur(2px);
    transition:opacity .22s ease;
}

body.page-leaving .neo-page-soft-overlay,
body.page-submitting .neo-page-soft-overlay{
    opacity:1;
}

/* Card/menu terasa lebih premium */
.card,
.neo-xls,
.neo-search,
.dash-stat,
.year-card,
.month-card,
.invoice-card,
.pay-box,
.grid .card{
    transition:
        transform .18s ease,
        box-shadow .18s ease,
        border-color .18s ease;
}

.card:active,
.neo-xls:active,
.dash-stat:active,
.month-card:active,
.invoice-card:active{
    transform:scale(.995);
}

/* Bottom nav click feedback */
.bottomnav a,
.btn,
.logout{
    transition:
        transform .16s ease,
        opacity .16s ease,
        box-shadow .16s ease,
        background-color .16s ease;
}

.bottomnav a:active,
.btn:active,
.logout:active{
    transform:scale(.96);
}

/* Hormati user yang mematikan animasi */
@media (prefers-reduced-motion: reduce){
    body,
    .neo-page-progress,
    .neo-page-soft-overlay,
    .card,
    .neo-xls,
    .neo-search,
    .dash-stat,
    .year-card,
    .month-card,
    .invoice-card,
    .pay-box,
    .grid .card,
    .bottomnav a,
    .btn,
    .logout{
        transition:none !important;
        transform:none !important;
        filter:none !important;
    }

    body{
        opacity:1 !important;
    }
}
</style>
<style id="mobile-bottom-always-fixed">
/* Fix bottom nav agar selalu muncul */
body,
body.page-ready,
body.page-leaving,
body.page-submitting{
    transform:none !important;
    filter:none !important;
}

/* Tetap boleh fade, tapi tanpa transform agar fixed bottom tidak rusak */
body{
    opacity:1 !important;
}

/* Bottom bar selalu fixed di bawah */
.mobile-bottom{
    position:fixed !important;
    left:8px !important;
    right:8px !important;
    bottom:max(7px, env(safe-area-inset-bottom)) !important;
    z-index:99990 !important;
    display:grid !important;
    grid-template-columns:repeat(4,1fr) !important;
    gap:5px !important;
    height:54px !important;
    padding:5px !important;
    border-radius:20px !important;
    background:rgba(255,255,255,.97) !important;
    border:1px solid #dbe5f2 !important;
    box-shadow:0 14px 34px rgba(16,24,40,.16) !important;
    backdrop-filter:blur(18px) !important;
    -webkit-backdrop-filter:blur(18px) !important;
    transform:none !important;
    opacity:1 !important;
    pointer-events:auto !important;
}

/* Saat sidebar terbuka, bottom bar disembunyikan */
body.sidebar-open .mobile-bottom{
    opacity:0 !important;
    pointer-events:none !important;
    transform:translateY(120%) !important;
}

/* Beri ruang konten agar tidak ketutup bottom bar */
.main{
    padding-bottom:92px !important;
}

.content{
    padding-bottom:110px !important;
}

.neo-xls,
.year-card,
.pay-box,
.invoice-card-list,
.collector-dash{
    margin-bottom:120px !important;
}

/* Overlay sidebar tetap di atas bottom nav */
.overlay{
    z-index:100000 !important;
}

.sidebar{
    z-index:100001 !important;
}

/* Mobile only */
@media(max-width:760px){
    .mobile-bottom{
        display:grid !important;
    }

    .main{
        padding-bottom:96px !important;
    }

    .content{
        padding-bottom:115px !important;
    }
}

/* Desktop tetap sembunyikan bottom nav */
@media(min-width:761px){
    .mobile-bottom{
        display:none !important;
    }
}
</style>
<style id="bottom-single-active-fix">
/* Bottom nav: hanya item aktif yang terlihat selected */
.mobile-bottom a{
    background:transparent !important;
    color:#667085 !important;
}

.mobile-bottom a .ico{
    background:#f8fafc !important;
    color:#667085 !important;
    border:1px solid #e4eaf3 !important;
}

.mobile-bottom a.active{
    background:#eaf2ff !important;
    color:#175cd3 !important;
}

.mobile-bottom a.active .ico{
    background:linear-gradient(135deg,#2563eb,#06b6d4) !important;
    color:#fff !important;
    border-color:transparent !important;
    box-shadow:0 8px 18px rgba(37,99,235,.22) !important;
}

/* Saat ditekan tetap halus */
.mobile-bottom a:active{
    transform:scale(.96) !important;
}

/* Sidebar buka: bottom bar hilang */
body.sidebar-open .mobile-bottom{
    opacity:0 !important;
    pointer-events:none !important;
    transform:translateY(120%) !important;
}
</style>
</head>

<body>
@php
    $user = auth()->user();
    $role = $user?->role ?? 'admin';

    $isAdmin = $role === 'admin';
    $isCollector = in_array($role, ['collector', 'kasir'], true);
    $isTechnician = in_array($role, ['technician', 'teknisi'], true);

    $roleName = $isAdmin ? 'Admin' : ($isCollector ? 'Kasir' : 'Teknisi');

    $icon = function ($name) {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>',
            'box' => '<svg viewBox="0 0 24 24"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>',
            'users' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'wifi' => '<svg viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>',
            'map' => '<svg viewBox="0 0 24 24"><path d="M9 18l-6 3V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>',
            'file' => '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>',
            'pay' => '<svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M7 15h.01"/><path d="M11 15h2"/></svg>',
            'chart' => '<svg viewBox="0 0 24 24"><path d="M4 19V5"/><path d="M4 19h16"/><path d="M8 16v-5"/><path d="M12 16V8"/><path d="M16 16v-8"/></svg>',
            'cash' => '<svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 9h.01"/><path d="M18 15h.01"/></svg>',
            'clock' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
            'tool' => '<svg viewBox="0 0 24 24"><path d="M14.7 6.3a4 4 0 0 0-5 5L3 18v3h3l6.7-6.7a4 4 0 0 0 5-5l-2.4 2.4-3-3 2.4-2.4z"/></svg>',
            'settings' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1A2 2 0 1 1 7.1 4.2l.1.1A1.7 1.7 0 0 0 9 4.6 1.7 1.7 0 0 0 10 3V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>',
            'menu' => '<svg viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>',
        ];

        return $icons[$name] ?? $icons['file'];
    };

    $isActive = function ($patterns) {
        foreach ((array) $patterns as $pattern) {
            if (request()->is($pattern)) return 'active';
        }
        return '';
    };

    $adminMenus = [
        ['Dashboard','/admin/dashboard','home',['admin/dashboard']],
        ['Pelanggan','/admin/customers','users',['admin/customers*']],
        ['Paket Internet','/admin/packages','box',['admin/packages*']],
        ['ODP','/admin/odps','wifi',['admin/odps','admin/odps/*']],
        ['Peta ODP','/admin/odps-map','map',['admin/odps-map']],
        ['Tagihan','/admin/invoices','file',['admin/invoices','admin/invoices/*']],
        ['Tagihan Manual','/admin/invoices/preview','file',['admin/invoices/preview','admin/invoices/preview*']],
        ['Pengeluaran','/admin/expenses','cash',['admin/expenses*']],
        ['Laporan','/admin/reports/finance','chart',['admin/reports*']],
    ];

    $collectorMenus = [
        ['Dashboard','/collector/dashboard','home',['collector/dashboard']],
        ['Percobaan','/collector/percobaan','sparkles',['collector/percobaan','collector/percobaan/dashboard']],
        ['Daftar Pelanggan','/collector/customers','users',['collector/customers*']],
        ['Tagihan','/collector/invoices','file',['collector/invoices','collector/customer*']],
        ['Tagihan Manual','/collector/invoices/preview','file',['collector/invoices/preview','collector/invoices/preview*']],
        ['Riwayat Pembayaran','/collector/history','clock',['collector/history','collector/payments*']],
        ['Pengeluaran','/collector/expenses','cash',['collector/expenses*']],
    ];

    $technicianMenus = [
        ['Dashboard','/technician/dashboard','home',['technician/dashboard']],
        ['Pelanggan','/technician/customers','users',['technician/customers*']],
        ['ODP','/technician/odps','wifi',['technician/odps*']],
    ];

    $menus = $isAdmin ? $adminMenus : ($isCollector ? $collectorMenus : $technicianMenus);
    $bottomMenus = array_slice($menus, 0, 4);

    $settingsOpen = request()->is('admin/settings*') || request()->is('admin/system/health*');
@endphp

<div class="overlay" id="overlay"></div>

<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brandmark">M</div>
            <div class="brandtext">
                <b>{{ $appSettings['app_name'] ?? 'MAC Billing' }}</b>
                <span>{{ $roleName }} Panel</span>
            </div>
        </div>

        <div class="nav-title">Menu Utama</div>
        <nav class="nav">
            @foreach($menus as $item)
                <a class="{{ $isActive($item[3]) }}" href="{{ url($item[1]) }}">
                    <span class="ico">{!! $icon($item[2]) !!}</span>
                    <span>{{ $item[0] }}</span>
                </a>
            @endforeach

            @if($isAdmin)
                <div class="nav-title">Sistem</div>

                <a class="{{ $isActive(['admin/settings/users*']) }}" href="{{ url('/admin/settings/users') }}">
                    <span class="ico">{!! $icon('users') !!}</span>
                    <span>Pegawai</span>
                </a>

                  <details class="side-group" {{ request()->is('admin/mikrotik*') ? 'open' : '' }}>
                      <summary class="navitem {{ request()->is('admin/mikrotik*') ? 'active' : '' }}">
                          <span class="ico">{!! $icon('wifi') !!}</span>
                          <span>Mikrotik</span>
                          <span class="side-chev">›</span>
                      </summary>

                      <div class="side-sub">
                          <a class="{{ $isActive(['admin/mikrotik/integrasi*']) }}" href="{{ url('/admin/mikrotik/integrasi') }}">Integrasi Mikrotik</a>
<a class="{{ $isActive(['admin/mikrotik/pppoe-active*']) }}" href="{{ url('/admin/mikrotik/pppoe-active') }}">PPPoE Active</a>
                          <a class="{{ $isActive(['admin/mikrotik/pppoe-offline*']) }}" href="{{ url('/admin/mikrotik/pppoe-offline') }}">PPPoE Offline</a>
                          <a class="{{ $isActive(['admin/mikrotik/pppoe-secret*']) }}" href="{{ url('/admin/mikrotik/pppoe-secret') }}">PPPoE Secret</a>
                          <a class="{{ $isActive(['admin/mikrotik/pppoe-profile*']) }}" href="{{ url('/admin/mikrotik/pppoe-profile') }}">PPPoE Profile</a>
                      </div>
                  </details>




                <a class="{{ $isActive(['admin/system/health*']) }}" href="{{ url('/admin/system/health') }}">
                    <span class="ico">{!! $icon('tool') !!}</span>
                    <span>Status Sistem</span>
                </a>

                <details class="side-group" {{ $settingsOpen ? 'open' : '' }}>
                    <summary class="navitem {{ $settingsOpen ? 'active' : '' }}">
                        <span class="ico">{!! $icon('settings') !!}</span>
                        <span>Pengaturan</span>
                        <span class="side-chev">›</span>
                    </summary>

                    <div class="side-sub">
                        <a class="{{ $isActive(['admin/settings/general']) }}" href="{{ url('/admin/settings/general') }}">Umum</a>

                        <a class="{{ $isActive(['admin/settings/olt']) }}" href="{{ url('/admin/settings/olt') }}">OLT</a>
                        <a class="{{ $isActive(['admin/settings/reset-data']) }}" href="{{ url('/admin/settings/reset-data') }}">Reset Data</a>
                    </div>
                </details>
            @endif
        </nav>

        <div class="sidebar-foot">
            <div class="userbox">
                <div class="avatar">{{ strtoupper(substr($user->username ?? $user->name ?? 'U', 0, 1)) }}</div>
                <div>
                    <b>{{ $user->username ?? $user->name ?? 'User' }}</b>
                    <span>{{ $roleName }}</span>
                </div>
            </div>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="top-left">
                <button class="hamb" id="hamb" type="button" aria-label="Menu">
                    {!! $icon('menu') !!}
                </button>

                <div class="pagetitle">
                    <b>@yield('title', $appSettings['app_name'] ?? 'MAC Billing')</b>
                    <span>{{ $roleName }} · Billing ISP</span>
                </div>
            </div>

            <div class="top-actions" style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                @yield('top_actions')
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout" type="submit">Keluar</button>
                </form>
            </div>
        </header>

        <section class="content">
            @yield('content')
        </section>
    </main>
</div>

<nav class="mobile-bottom">
    @foreach($bottomMenus as $item)
        <a class="{{ $isActive($item[3]) }}" href="{{ url($item[1]) }}">
            <span class="ico">{!! $icon($item[2]) !!}</span>
            <span>{{ $item[0] }}</span>
        </a>
    @endforeach
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const hamb = document.getElementById('hamb');
    const overlay = document.getElementById('overlay');

    if (hamb) {
        hamb.addEventListener('click', function () {
            body.classList.toggle('sidebar-open');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            body.classList.remove('sidebar-open');
        });
    }

    document.querySelectorAll('.sidebar a').forEach(function (link) {
        link.addEventListener('click', function () {
            body.classList.remove('sidebar-open');
        });
    });
});

document.addEventListener('click', function (e) {
    document.querySelectorAll('.neo-dd[open]').forEach(function (dd) {
        if (!dd.contains(e.target)) {
            dd.removeAttribute('open');
        }
    });
});
</script>

@stack('scripts')
<script id="premium-page-transition-script">
(function(){
    function ensureTransitionElements(){
        if(!document.querySelector('.neo-page-progress')){
            var bar = document.createElement('div');
            bar.className = 'neo-page-progress';
            document.body.appendChild(bar);
        }

        if(!document.querySelector('.neo-page-soft-overlay')){
            var overlay = document.createElement('div');
            overlay.className = 'neo-page-soft-overlay';
            document.body.appendChild(overlay);
        }
    }

    function ready(){
        ensureTransitionElements();
        requestAnimationFrame(function(){
            document.body.classList.add('page-ready');
            document.body.classList.remove('page-leaving','page-submitting');
        });
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', ready);
    }else{
        ready();
    }

    window.addEventListener('pageshow', function(){
        document.body.classList.add('page-ready');
        document.body.classList.remove('page-leaving','page-submitting');
    });

    document.addEventListener('click', function(e){
        var link = e.target.closest('a');
        if(!link) return;

        var href = link.getAttribute('href');
        if(!href) return;

        if(
            href.startsWith('#') ||
            href.startsWith('javascript:') ||
            link.hasAttribute('download') ||
            link.target === '_blank' ||
            link.dataset.noTransition === 'true' ||
            e.ctrlKey || e.metaKey || e.shiftKey || e.altKey
        ){
            return;
        }

        var url;
        try{
            url = new URL(href, window.location.href);
        }catch(err){
            return;
        }

        if(url.origin !== window.location.origin){
            return;
        }

        if(e.defaultPrevented){
            return;
        }

        e.preventDefault();

        document.body.classList.add('page-leaving');

        setTimeout(function(){
            window.location.href = url.href;
        }, 170);
    }, false);

    document.addEventListener('submit', function(e){
        var form = e.target;
        if(!form || form.dataset.noTransition === 'true') return;

        document.body.classList.add('page-submitting');
    }, false);
})();
</script>
<script id="bottom-single-active-script">
(function(){
    function normalize(path){
        if(!path) return '/';
        path = path.replace(/\/+$/, '');
        return path || '/';
    }

    function fixBottomActive(){
        var nav = document.querySelector('.mobile-bottom');
        if(!nav) return;

        var links = Array.prototype.slice.call(nav.querySelectorAll('a[href]'));
        if(!links.length) return;

        var current = normalize(window.location.pathname);

        links.forEach(function(a){
            a.classList.remove('active');
            a.removeAttribute('aria-current');
        });

        var target = null;

        /* Collector rules supaya tidak dobel */
        if(current === '/collector/dashboard'){
            target = links.find(function(a){ return normalize(new URL(a.href).pathname) === '/collector/dashboard'; });
        }else if(current.startsWith('/collector/customers')){
            target = links.find(function(a){ return normalize(new URL(a.href).pathname) === '/collector/customers'; });
        }else if(current.startsWith('/collector/invoices/preview')){
            target = links.find(function(a){ return normalize(new URL(a.href).pathname) === '/collector/invoices/preview'; });
        }else if(current.startsWith('/collector/invoices') || current.startsWith('/collector/customer/')){
            target = links.find(function(a){ return normalize(new URL(a.href).pathname) === '/collector/invoices'; });
        }else if(current.startsWith('/collector/history') || current.startsWith('/collector/payments')){
            target = links.find(function(a){ return normalize(new URL(a.href).pathname) === '/collector/history'; });
        }

        /* Fallback umum: pilih href yang paling cocok */
        if(!target){
            var best = null;
            var bestLen = -1;

            links.forEach(function(a){
                var path = normalize(new URL(a.href).pathname);
                if(current === path || current.startsWith(path + '/')){
                    if(path.length > bestLen){
                        best = a;
                        bestLen = path.length;
                    }
                }
            });

            target = best;
        }

        if(target){
            target.classList.add('active');
            target.setAttribute('aria-current','page');
        }
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', fixBottomActive);
    }else{
        fixBottomActive();
    }

    window.addEventListener('pageshow', fixBottomActive);
})();
</script>


<style id="collector-bright-table-style">
/* Khusus Collector/Kasir: warna solid + garis kolom jelas */
body.collector-bright-table-page .neo-xls{
    background:#ffffff !important;
    border:1px solid #93c5fd !important;
    box-shadow:0 8px 22px rgba(37,99,235,.08) !important;
}

body.collector-bright-table-page .neo-xls-info{
    background:#dbeafe !important;
    color:#0f172a !important;
    border-bottom:1px solid #93c5fd !important;
    font-weight:850 !important;
}

body.collector-bright-table-page .neo-xls-table{
    border-collapse:separate !important;
    border-spacing:0 !important;
}

body.collector-bright-table-page .neo-xls-table thead th{
    background:#bfdbfe !important;
    color:#0f172a !important;
    border-right:1px solid #60a5fa !important;
    border-bottom:1px solid #3b82f6 !important;
    font-weight:950 !important;
}

body.collector-bright-table-page .neo-xls-table thead th:first-child,
body.collector-bright-table-page .neo-xls-table thead th.sticky-left{
    background:#93c5fd !important;
    color:#0f172a !important;
    border-right:2px solid #2563eb !important;
}

body.collector-bright-table-page .neo-xls-table tbody td{
    border-right:1px solid #d1d5db !important;
    border-bottom:1px solid #e5e7eb !important;
}

body.collector-bright-table-page .neo-xls-table tbody td:first-child,
body.collector-bright-table-page .neo-xls-table tbody td.sticky-left{
    border-right:2px solid #93c5fd !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr:nth-child(odd) td{
    background:#ffffff !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr:nth-child(even) td{
    background:#f3f8ff !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr:hover td{
    background:#e0f2fe !important;
    color:#0f172a !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td{
    background:#2563eb !important;
    color:#ffffff !important;
    border-right:1px solid #93c5fd !important;
    border-bottom:1px solid #93c5fd !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:first-child,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td.sticky-left{
    background:#1d4ed8 !important;
    color:#ffffff !important;
    border-right:2px solid #facc15 !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .neo-id,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .neo-strong,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .neo-money,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight a{
    color:#ffffff !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .badge{
    background:#ffffff !important;
    color:#1d4ed8 !important;
    border-color:#ffffff !important;
    font-weight:950 !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight{
    outline:2px solid #facc15 !important;
    outline-offset:-2px !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr{
    cursor:pointer !important;
}

@media(max-width:760px){
    body.collector-bright-table-page .neo-xls-table thead th{
        background:#bfdbfe !important;
        color:#0f172a !important;
        border-right:1px solid #60a5fa !important;
        border-bottom:1px solid #3b82f6 !important;
    }

    body.collector-bright-table-page .neo-xls-table thead th:first-child,
    body.collector-bright-table-page .neo-xls-table thead th.sticky-left{
        background:#93c5fd !important;
        border-right:2px solid #2563eb !important;
    }

    body.collector-bright-table-page .neo-xls-table tbody td{
        border-right:1px solid #d1d5db !important;
        border-bottom:1px solid #e5e7eb !important;
    }

    body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td{
        background:#2563eb !important;
        color:#ffffff !important;
    }

    body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:first-child,
    body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td.sticky-left{
        background:#1d4ed8 !important;
        border-right:2px solid #facc15 !important;
    }
}
</style>


<script id="collector-bright-table-js">
document.addEventListener('DOMContentLoaded', function () {
    const path = window.location.pathname || '';

    const isCollectorListPage =
        path === '/collector/invoices' ||
        path === '/collector/customers' ||
        path.startsWith('/collector/customer');

    const isManualPreview = path.startsWith('/collector/invoices/preview');

    if (!isCollectorListPage || isManualPreview) return;

    document.body.classList.add('collector-bright-table-page');

    document.querySelectorAll('.neo-xls-table').forEach(function (table) {
        table.addEventListener('click', function (e) {
            if (
                e.target.matches('input, button, a, select, textarea') ||
                e.target.closest('input, button, a, select, textarea')
            ) {
                return;
            }

            const row = e.target.closest('tbody tr');
            if (!row || row.querySelector('[colspan]')) return;

            const active = row.classList.contains('collector-row-highlight');

            table.querySelectorAll('tbody tr.collector-row-highlight').forEach(function (r) {
                r.classList.remove('collector-row-highlight');
            });

            if (!active) {
                row.classList.add('collector-row-highlight');
            }
        });
    });
});
</script>


<style id="collector-no-bottombar-fullscreen-table-css">
/* Khusus collector/kasir: hilangkan bottom bar */
body.collector-no-bottombar-page .collector-force-hide-bottombar,
body.collector-no-bottombar-page .neo-mobilebar,
body.collector-no-bottombar-page .neo-mobile-bar,
body.collector-no-bottombar-page .neo-bottomnav,
body.collector-no-bottombar-page .neo-bottom-nav,
body.collector-no-bottombar-page .bottom-nav,
body.collector-no-bottombar-page .bottom-menu,
body.collector-no-bottombar-page .mobile-bottom-nav,
body.collector-no-bottombar-page .mobile-nav-bottom,
body.collector-no-bottombar-page .app-bottom-nav{
    display:none !important;
    visibility:hidden !important;
    opacity:0 !important;
    pointer-events:none !important;
    height:0 !important;
    min-height:0 !important;
    max-height:0 !important;
}

/* Jangan sembunyikan tombol generate Tagihan Manual */
body.collector-no-bottombar-page .preview-submitbar,
body.collector-no-bottombar-page .preview-submitbar.tm-active,
body.collector-no-bottombar-page .preview-submitbar[data-active="1"]{
    visibility:visible !important;
    opacity:1 !important;
    pointer-events:auto !important;
}

/* Hilangkan ruang kosong yang sebelumnya disiapkan untuk bottom bar */
body.collector-no-bottombar-page{
    padding-bottom:0 !important;
}

body.collector-no-bottombar-page main,
body.collector-no-bottombar-page .main,
body.collector-no-bottombar-page .content,
body.collector-no-bottombar-page .neo-main,
body.collector-no-bottombar-page .neo-content,
body.collector-no-bottombar-page .app-main,
body.collector-no-bottombar-page .page-content{
    padding-bottom:8px !important;
    margin-bottom:0 !important;
}

@media(max-width:760px){
    /* Area tabel dibuat full layar HP */
    body.collector-no-bottombar-page .neo-xls{
        width:100% !important;
        margin-bottom:4px !important;
        border-radius:10px !important;
        overflow:hidden !important;
    }

    body.collector-no-bottombar-page .neo-xls-info{
        min-height:24px !important;
        height:24px !important;
        padding:3px 6px !important;
        font-size:9px !important;
        line-height:1 !important;
    }

    body.collector-no-bottombar-page .neo-xls-scroll{
        height:calc(100dvh - 145px) !important;
        max-height:calc(100dvh - 145px) !important;
        min-height:calc(100dvh - 145px) !important;
        overflow:auto !important;
        padding-bottom:8px !important;
    }

    /* Tagihan Manual punya LiveFind + periode, jadi offset dibuat khusus */
    body.collector-no-bottombar-page.collector-manual-preview-page .neo-xls-scroll{
        height:calc(100dvh - 210px) !important;
        max-height:calc(100dvh - 210px) !important;
        min-height:calc(100dvh - 210px) !important;
        padding-bottom:72px !important;
    }

    /* Kalau ada search/filter di atas tabel, dipadatkan */
    body.collector-no-bottombar-page .neo-search,
    body.collector-no-bottombar-page .tm-livefind-card,
    body.collector-no-bottombar-page .tm-period-auto-form{
        margin-bottom:5px !important;
        padding:6px !important;
        border-radius:10px !important;
    }

    body.collector-no-bottombar-page .neo-search input,
    body.collector-no-bottombar-page .neo-search select,
    body.collector-no-bottombar-page .neo-search .input,
    body.collector-no-bottombar-page .neo-search .select,
    body.collector-no-bottombar-page .tm-livefind-input,
    body.collector-no-bottombar-page .tm-livefind-clear{
        min-height:34px !important;
        height:34px !important;
        border-radius:9px !important;
        font-size:11px !important;
        padding:0 7px !important;
    }

    body.collector-no-bottombar-page .tm-livefind-count{
        margin-top:3px !important;
        font-size:9px !important;
    }

    /* Kolom tabel lebih padat agar muat lebih banyak dalam 1 layar HP */
    body.collector-no-bottombar-page .neo-xls-table{
        width:max-content !important;
        min-width:100% !important;
        border-collapse:separate !important;
        border-spacing:0 !important;
    }

    body.collector-no-bottombar-page .neo-xls-table thead,
    body.collector-no-bottombar-page .neo-xls-table thead tr{
        height:22px !important;
        min-height:22px !important;
        max-height:22px !important;
    }

    body.collector-no-bottombar-page .neo-xls-table thead th{
        height:22px !important;
        min-height:22px !important;
        max-height:22px !important;
        padding:2px 4px !important;
        font-size:8px !important;
        line-height:1 !important;
        white-space:nowrap !important;
        vertical-align:middle !important;
        border-right:1px solid #60a5fa !important;
        border-bottom:1px solid #2563eb !important;
    }

    body.collector-no-bottombar-page .neo-xls-table tbody tr{
        height:28px !important;
        min-height:28px !important;
        max-height:28px !important;
    }

    body.collector-no-bottombar-page .neo-xls-table tbody td{
        height:28px !important;
        min-height:28px !important;
        max-height:28px !important;
        padding:2px 4px !important;
        font-size:9px !important;
        line-height:1.05 !important;
        white-space:nowrap !important;
        vertical-align:middle !important;
        border-right:1px solid #d1d5db !important;
        border-bottom:1px solid #e5e7eb !important;
    }

    body.collector-no-bottombar-page .neo-xls-table .badge{
        padding:2px 5px !important;
        border-radius:999px !important;
        font-size:8px !important;
        line-height:1 !important;
        white-space:nowrap !important;
    }

    body.collector-no-bottombar-page .neo-row-actions{
        gap:3px !important;
        flex-wrap:nowrap !important;
    }

    body.collector-no-bottombar-page .neo-row-actions .btn,
    body.collector-no-bottombar-page .btn.icon{
        width:25px !important;
        height:25px !important;
        min-width:25px !important;
        min-height:25px !important;
        padding:0 !important;
        border-radius:8px !important;
    }

    body.collector-no-bottombar-page .pagination{
        margin:4px 0 0 !important;
        padding-bottom:0 !important;
    }
}
</style>


<script id="collector-no-bottombar-fullscreen-table-js">
document.addEventListener('DOMContentLoaded', function () {
    const path = window.location.pathname || '';

    if (!path.startsWith('/collector/')) return;

    document.body.classList.add('collector-no-bottombar-page');

    if (path.startsWith('/collector/invoices/preview')) {
        document.body.classList.add('collector-manual-preview-page');
    }

    if (
        path === '/collector/customers' ||
        path.startsWith('/collector/customer') ||
        path === '/collector/invoices'
    ) {
        document.body.classList.add('collector-list-table-page');
    }

    /*
      Sembunyikan bottom navigation khusus collector.
      Dibuat berbasis posisi + isi link agar tidak mengenai tombol floating Tagihan Manual.
    */
    Array.from(document.querySelectorAll('nav, footer, div, section')).forEach(function (el) {
        if (!el || el.closest('.preview-submitbar')) return;

        const css = window.getComputedStyle(el);
        const isBottomFixed =
            (css.position === 'fixed' || css.position === 'sticky') &&
            css.bottom !== 'auto';

        if (!isBottomFixed) return;

        const text = (el.innerText || '').toLowerCase();
        const html = (el.innerHTML || '').toLowerCase();

        const looksCollectorBottom =
            html.includes('/collector/dashboard') ||
            html.includes('/collector/customers') ||
            html.includes('/collector/invoices') ||
            (
                text.includes('dashboard') &&
                text.includes('tagihan') &&
                (text.includes('pelanggan') || text.includes('manual'))
            );

        if (looksCollectorBottom) {
            el.classList.add('collector-force-hide-bottombar');
        }
    });

    // Jika masih ada padding bawah dari layout lama, paksa nol.
    document.documentElement.style.setProperty('--collector-bottom-space', '0px');
});
</script>


<style id="collector-highlight-action-visible-css">
/* Tombol aksi tetap terlihat saat baris collector/kasir di-highlight */
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:last-child,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:nth-last-child(1){
    background:#1d4ed8 !important;
    color:#ffffff !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:last-child a,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:last-child button,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions a,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions button,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight a.btn,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight button.btn{
    background:#ffffff !important;
    color:#1d4ed8 !important;
    border:1px solid #facc15 !important;
    box-shadow:0 2px 8px rgba(15,23,42,.18) !important;
    font-weight:950 !important;
    position:relative !important;
    z-index:50 !important;
    opacity:1 !important;
    visibility:visible !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:last-child a *,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:last-child button *,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions a *,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions button *{
    color:#1d4ed8 !important;
    stroke:#1d4ed8 !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:last-child a:hover,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:last-child button:hover,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions a:hover,
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions button:hover{
    background:#fef3c7 !important;
    color:#0f172a !important;
}

/* Supaya kolom aksi tidak tertutup outline highlight */
body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td{
    position:relative !important;
}

body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:last-child{
    z-index:40 !important;
}

@media(max-width:760px){
    body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:last-child a,
    body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight td:last-child button,
    body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions a,
    body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions button,
    body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight a.btn,
    body.collector-bright-table-page .neo-xls-table tbody tr.collector-row-highlight button.btn{
        background:#ffffff !important;
        color:#1d4ed8 !important;
        border:1px solid #facc15 !important;
        min-height:24px !important;
        height:24px !important;
        border-radius:8px !important;
        padding:0 6px !important;
        font-size:9px !important;
        line-height:1 !important;
    }
}
</style>


<style id="collector-view-simple-card-css">
/* Khusus halaman Collector > Lihat Tagihan */
@media(max-width:760px){
    body.collector-view-simple-page .neo-xls{
        background:transparent !important;
        border:0 !important;
        box-shadow:none !important;
        overflow:visible !important;
        margin:0 0 10px !important;
        border-radius:0 !important;
    }

    body.collector-view-simple-page .neo-xls-info{
        background:#dbeafe !important;
        color:#0f172a !important;
        border:1px solid #93c5fd !important;
        border-radius:12px !important;
        padding:8px 10px !important;
        margin:0 0 8px !important;
        min-height:0 !important;
        height:auto !important;
        font-size:11px !important;
        line-height:1.25 !important;
        font-weight:900 !important;
    }

    body.collector-view-simple-page .neo-xls-scroll{
        overflow:visible !important;
        overflow-x:visible !important;
        overflow-y:visible !important;
        max-height:none !important;
        min-height:0 !important;
        height:auto !important;
        padding:0 !important;
        margin:0 !important;
    }

    body.collector-view-simple-page .neo-xls-table{
        display:block !important;
        width:100% !important;
        min-width:0 !important;
        max-width:100% !important;
        border:0 !important;
        border-collapse:separate !important;
        border-spacing:0 !important;
        background:transparent !important;
    }

    body.collector-view-simple-page .neo-xls-table thead{
        display:none !important;
    }

    body.collector-view-simple-page .neo-xls-table tbody{
        display:block !important;
        width:100% !important;
    }

    body.collector-view-simple-page .neo-xls-table tbody tr{
        display:block !important;
        width:100% !important;
        height:auto !important;
        min-height:0 !important;
        max-height:none !important;
        margin:0 0 9px !important;
        padding:0 !important;
        background:#ffffff !important;
        border:1px solid #bfdbfe !important;
        border-radius:14px !important;
        overflow:hidden !important;
        box-shadow:0 6px 15px rgba(15,23,42,.06) !important;
        outline:0 !important;
    }

    body.collector-view-simple-page .neo-xls-table tbody tr:nth-child(even) td,
    body.collector-view-simple-page .neo-xls-table tbody tr:nth-child(odd) td{
        background:#ffffff !important;
    }

    body.collector-view-simple-page .neo-xls-table tbody td{
        display:flex !important;
        align-items:flex-start !important;
        justify-content:space-between !important;
        gap:10px !important;
        width:100% !important;
        min-width:0 !important;
        max-width:100% !important;
        height:auto !important;
        min-height:0 !important;
        max-height:none !important;
        padding:8px 10px !important;
        border-right:0 !important;
        border-bottom:1px solid #e5e7eb !important;
        color:#0f172a !important;
        font-size:12px !important;
        line-height:1.25 !important;
        white-space:normal !important;
        overflow:visible !important;
        text-overflow:clip !important;
        vertical-align:top !important;
        text-align:right !important;
    }

    body.collector-view-simple-page .neo-xls-table tbody td::before{
        content:attr(data-label);
        flex:0 0 42%;
        max-width:42%;
        color:#64748b;
        font-size:10px;
        font-weight:950;
        line-height:1.2;
        text-align:left;
        text-transform:uppercase;
        letter-spacing:.02em;
    }

    body.collector-view-simple-page .neo-xls-table tbody td:last-child{
        border-bottom:0 !important;
    }

    body.collector-view-simple-page .neo-xls-table tbody td:last-child::before{
        display:none !important;
        content:"" !important;
    }

    body.collector-view-simple-page .neo-xls-table .neo-id,
    body.collector-view-simple-page .neo-xls-table .neo-strong,
    body.collector-view-simple-page .neo-xls-table .neo-money{
        color:#0f172a !important;
        font-weight:950 !important;
    }

    body.collector-view-simple-page .neo-xls-table .badge{
        display:inline-flex !important;
        align-items:center !important;
        justify-content:center !important;
        padding:4px 8px !important;
        border-radius:999px !important;
        font-size:10px !important;
        line-height:1 !important;
        white-space:nowrap !important;
    }

    body.collector-view-simple-page .neo-row-actions{
        width:100% !important;
        display:flex !important;
        justify-content:flex-end !important;
        gap:6px !important;
        flex-wrap:wrap !important;
    }

    body.collector-view-simple-page .neo-row-actions .btn,
    body.collector-view-simple-page td:last-child .btn,
    body.collector-view-simple-page td:last-child a,
    body.collector-view-simple-page td:last-child button{
        min-height:32px !important;
        height:32px !important;
        padding:0 10px !important;
        border-radius:10px !important;
        font-size:11px !important;
        font-weight:950 !important;
        background:#2563eb !important;
        color:#ffffff !important;
        border:1px solid #1d4ed8 !important;
        box-shadow:none !important;
    }

    body.collector-view-simple-page .neo-xls-table tbody tr.collector-row-highlight{
        border-color:#facc15 !important;
        box-shadow:0 0 0 2px #facc15 inset, 0 8px 18px rgba(37,99,235,.16) !important;
    }

    body.collector-view-simple-page .neo-xls-table tbody tr.collector-row-highlight td{
        background:#eff6ff !important;
        color:#0f172a !important;
    }

    body.collector-view-simple-page .neo-xls-table tbody tr.collector-row-highlight td::before{
        color:#1d4ed8 !important;
    }

    body.collector-view-simple-page .neo-xls-table tbody tr.collector-row-highlight td:last-child a,
    body.collector-view-simple-page .neo-xls-table tbody tr.collector-row-highlight td:last-child button,
    body.collector-view-simple-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions a,
    body.collector-view-simple-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions button{
        background:#ffffff !important;
        color:#1d4ed8 !important;
        border:1px solid #facc15 !important;
    }
}
</style>


<script id="collector-view-simple-card-js">
document.addEventListener('DOMContentLoaded', function () {
    const path = window.location.pathname || '';

    const isCollectorView =
        path.startsWith('/collector/customer/') &&
        path.endsWith('/view');

    if (!isCollectorView) return;

    document.body.classList.add('collector-view-simple-page');

    document.querySelectorAll('.neo-xls-table').forEach(function (table) {
        const headers = Array.from(table.querySelectorAll('thead th')).map(function (th) {
            return (th.innerText || th.textContent || '').replace(/\s+/g, ' ').trim();
        });

        table.querySelectorAll('tbody tr').forEach(function (row) {
            if (row.querySelector('[colspan]')) return;

            Array.from(row.children).forEach(function (cell, index) {
                if (!cell.hasAttribute('data-label')) {
                    cell.setAttribute('data-label', headers[index] || '');
                }
            });
        });

        table.addEventListener('click', function (e) {
            if (
                e.target.matches('input, button, a, select, textarea') ||
                e.target.closest('input, button, a, select, textarea')
            ) {
                return;
            }

            const row = e.target.closest('tbody tr');
            if (!row || row.querySelector('[colspan]')) return;

            const active = row.classList.contains('collector-row-highlight');

            table.querySelectorAll('tbody tr.collector-row-highlight').forEach(function (r) {
                r.classList.remove('collector-row-highlight');
            });

            if (!active) {
                row.classList.add('collector-row-highlight');
            }
        });
    });
});
</script>



<style id="collector-action-icon-only-css">
body.collector-action-icon-page .neo-xls-table tbody td:last-child a.btn,
body.collector-action-icon-page .neo-xls-table tbody td:last-child button.btn,
body.collector-action-icon-page .neo-xls-table tbody td:last-child .action-text-btn,
body.collector-action-icon-page .neo-row-actions a.btn,
body.collector-action-icon-page .neo-row-actions button.btn,
body.collector-action-icon-page a.action-text-btn,
body.collector-action-icon-page button.action-text-btn{
    width:30px !important;
    min-width:30px !important;
    max-width:30px !important;
    height:30px !important;
    min-height:30px !important;
    max-height:30px !important;
    padding:0 !important;
    border-radius:10px !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    gap:0 !important;
    font-size:0 !important;
    line-height:1 !important;
    overflow:hidden !important;
}

body.collector-action-icon-page .collector-action-icon,
body.collector-action-icon-page .collector-action-icon svg{
    width:16px !important;
    height:16px !important;
    min-width:16px !important;
    min-height:16px !important;
    display:block !important;
    pointer-events:none !important;
}

body.collector-action-icon-page .collector-action-icon svg{
    stroke:currentColor !important;
    fill:none !important;
    stroke-width:2.2 !important;
    stroke-linecap:round !important;
    stroke-linejoin:round !important;
}

/* Warna ikon dibedakan jelas */
body.collector-action-icon-page [data-action="lihat"]{
    background:#eff6ff !important;
    color:#1d4ed8 !important;
    border:1px solid #bfdbfe !important;
}

body.collector-action-icon-page [data-action="bayar"]{
    background:#ecfdf5 !important;
    color:#047857 !important;
    border:1px solid #86efac !important;
}

body.collector-action-icon-page [data-action="nota"]{
    background:#faf5ff !important;
    color:#7e22ce !important;
    border:1px solid #d8b4fe !important;
}

body.collector-action-icon-page [data-action="cetak"]{
    background:#fff7ed !important;
    color:#c2410c !important;
    border:1px solid #fed7aa !important;
}

body.collector-action-icon-page [data-action="detail"]{
    background:#f8fafc !important;
    color:#334155 !important;
    border:1px solid #cbd5e1 !important;
}

/* Saat row highlight, tetap terlihat */
body.collector-action-icon-page .neo-xls-table tbody tr.collector-row-highlight td:last-child a.btn,
body.collector-action-icon-page .neo-xls-table tbody tr.collector-row-highlight td:last-child button.btn,
body.collector-action-icon-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions a,
body.collector-action-icon-page .neo-xls-table tbody tr.collector-row-highlight .neo-row-actions button,
body.collector-action-icon-page .neo-xls-table tbody tr.collector-row-highlight a.action-text-btn,
body.collector-action-icon-page .neo-xls-table tbody tr.collector-row-highlight button.action-text-btn{
    background:#ffffff !important;
    border:1px solid #facc15 !important;
    box-shadow:0 2px 8px rgba(15,23,42,.18) !important;
    opacity:1 !important;
    visibility:visible !important;
}

@media(max-width:760px){
    body.collector-action-icon-page .neo-xls-table tbody td:last-child a.btn,
    body.collector-action-icon-page .neo-xls-table tbody td:last-child button.btn,
    body.collector-action-icon-page .neo-xls-table tbody td:last-child .action-text-btn,
    body.collector-action-icon-page .neo-row-actions a.btn,
    body.collector-action-icon-page .neo-row-actions button.btn,
    body.collector-action-icon-page a.action-text-btn,
    body.collector-action-icon-page button.action-text-btn{
        width:28px !important;
        min-width:28px !important;
        max-width:28px !important;
        height:28px !important;
        min-height:28px !important;
        max-height:28px !important;
        border-radius:9px !important;
    }

    body.collector-action-icon-page .collector-action-icon,
    body.collector-action-icon-page .collector-action-icon svg{
        width:15px !important;
        height:15px !important;
    }
}
</style>


<script id="collector-action-icon-only-js">
document.addEventListener('DOMContentLoaded', function () {
    const path = window.location.pathname || '';
    if (!path.startsWith('/collector/')) return;

    document.body.classList.add('collector-action-icon-page');

    const icons = {
        lihat: '<svg viewBox="0 0 24 24"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>',
        bayar: '<svg viewBox="0 0 24 24"><path d="M4 7h13a3 3 0 0 1 3 3v7a3 3 0 0 1-3 3H5a2 2 0 0 1-2-2V8a1 1 0 0 1 1-1Z"/><path d="M16 12h5v4h-5a2 2 0 0 1 0-4Z"/><path d="M7 7V5h9v2"/><circle cx="17" cy="14" r=".8"/></svg>',
        nota: '<svg viewBox="0 0 24 24"><path d="M7 3h10a2 2 0 0 1 2 2v16l-2-1-2 1-2-1-2 1-2-1-2 1V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6"/><path d="M9 12h6"/><path d="M9 16h4"/></svg>',
        detail: '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>',
        cetak: '<svg viewBox="0 0 24 24"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>',
        default: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>'
    };

    function detectAction(el) {
        const raw = [
            el.innerText || '',
            el.textContent || '',
            el.getAttribute('title') || '',
            el.getAttribute('aria-label') || '',
            el.getAttribute('href') || '',
            el.className || ''
        ].join(' ').toLowerCase();

        // Urutan penting: "Lihat Nota" harus jadi nota, bukan lihat.
        if (raw.includes('nota') || raw.includes('receipt') || raw.includes('struk')) return 'nota';
        if (raw.includes('bayar') || raw.includes('pay') || raw.includes('payment')) return 'bayar';
        if (raw.includes('cetak') || raw.includes('print')) return 'cetak';
        if (raw.includes('detail')) return 'detail';
        if (raw.includes('lihat') || raw.includes('view')) return 'lihat';

        return 'default';
    }

    function labelFor(action) {
        if (action === 'bayar') return 'Bayar';
        if (action === 'nota') return 'Lihat Nota';
        if (action === 'lihat') return 'Lihat';
        if (action === 'detail') return 'Detail';
        if (action === 'cetak') return 'Cetak';
        return 'Aksi';
    }

    const selectors = [
        '.neo-xls-table tbody td:last-child a.btn',
        '.neo-xls-table tbody td:last-child button.btn',
        '.neo-xls-table tbody td:last-child .action-text-btn',
        '.neo-row-actions a.btn',
        '.neo-row-actions button.btn',
        'a.action-text-btn',
        'button.action-text-btn'
    ].join(',');

    document.querySelectorAll(selectors).forEach(function (el) {
        const action = detectAction(el);
        const label = labelFor(action);

        el.dataset.iconified = '1';
        el.dataset.action = action;

        el.setAttribute('title', label);
        el.setAttribute('aria-label', label);

        el.innerHTML = '<span class="collector-action-icon" aria-hidden="true">' + (icons[action] || icons.default) + '</span>';
    });
});
</script>



<!-- genieacs-sidebar-force-v1-start -->
<style id="genieacs-sidebar-force-style-v1">
#genieacs-sidebar-forced-link{
    display:flex;
    align-items:center;
    gap:10px;
}
#genieacs-sidebar-forced-link.genieacs-active,
#genieacs-sidebar-forced-link.active{
    font-weight:900;
}
</style>

<script id="genieacs-sidebar-force-script-v1">
(function(){
    function ready(fn){
        if(document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function(){
        if(document.getElementById('genieacs-sidebar-forced-link')) return;

        var href = @json(url('/admin/genieacs'));
        var isActive = window.location.pathname.indexOf('/admin/genieacs') === 0;

        function makeLink(ref){
            var a = document.createElement('a');
            a.href = href;
            a.id = 'genieacs-sidebar-forced-link';
            a.innerHTML = '<span>GenieACS</span>';

            if(ref && ref.className){
                a.className = ref.className;
            }

            if(isActive){
                a.className = (a.className ? a.className + ' ' : '') + 'active genieacs-active';
            }

            return a;
        }

        function visibleText(el){
            return (el && el.textContent ? el.textContent : '').replace(/\s+/g,' ').trim();
        }

        var sidebarSelectors = [
            '.admin-sidebar',
            '.main-sidebar',
            '.sidebar',
            '.app-sidebar',
            '.side-menu',
            '.sidenav',
            'aside',
            'nav'
        ];

        var sidebars = [];

        sidebarSelectors.forEach(function(sel){
            document.querySelectorAll(sel).forEach(function(el){
                if(sidebars.indexOf(el) === -1) sidebars.push(el);
            });
        });

        if(!sidebars.length) return;

        var sidebar = sidebars.find(function(el){
            var txt = visibleText(el);
            return el.querySelector('a[href*="/admin/dashboard"]') ||
                   el.querySelector('a[href*="/admin/system"]') ||
                   /Sistem|System|Dashboard|Pelanggan|Tagihan|MikroTik|Mikrotik/i.test(txt);
        }) || sidebars[0];

        if(!sidebar) return;

        var anchors = Array.prototype.slice.call(sidebar.querySelectorAll('a'));

        if(anchors.some(function(a){
            return (a.getAttribute('href') || '').indexOf('/admin/genieacs') !== -1;
        })){
            return;
        }

        var systemTextNode = Array.prototype.slice.call(sidebar.querySelectorAll('a,button,div,span,li')).find(function(el){
            return /(^|\s)(Sistem|System)(\s|$)/i.test(visibleText(el));
        });

        if(systemTextNode){
            var group = systemTextNode.closest('li,.nav-item,.menu-item,.dropdown,.has-submenu,.collapse-item') || systemTextNode.parentElement;
            if(group){
                var submenu = group.querySelector('ul,.submenu,.sub-menu,.dropdown-menu,.collapse,.nav-treeview,.children');
                if(submenu){
                    var li = document.createElement('li');
                    li.appendChild(makeLink(anchors[0]));
                    submenu.appendChild(li);
                    return;
                }

                var refA = group.querySelector('a') || anchors[0];
                var link = makeLink(refA);

                if(group.tagName && group.tagName.toLowerCase() === 'li'){
                    var li2 = document.createElement('li');
                    li2.className = group.className || '';
                    li2.appendChild(link);
                    group.parentNode.insertBefore(li2, group.nextSibling);
                    return;
                }

                group.parentNode.insertBefore(link, group.nextSibling);
                return;
            }
        }

        var ref = anchors.find(function(a){
            var h = a.getAttribute('href') || '';
            var t = visibleText(a);
            return h.indexOf('/admin/system') !== -1 ||
                   /Backup|Health|Kesehatan|Reset Data|Sistem|System/i.test(t);
        }) || anchors[anchors.length - 1];

        var linkFinal = makeLink(ref);

        if(ref){
            var parentLi = ref.closest('li');
            if(parentLi && parentLi.parentNode){
                var li3 = document.createElement('li');
                li3.className = parentLi.className || '';
                li3.appendChild(linkFinal);
                parentLi.parentNode.insertBefore(li3, parentLi.nextSibling);
                return;
            }

            ref.parentNode.insertBefore(linkFinal, ref.nextSibling);
            return;
        }

        sidebar.appendChild(linkFinal);
    });
})();
</script>
<!-- genieacs-sidebar-force-v1-end -->

</body>
</html>
