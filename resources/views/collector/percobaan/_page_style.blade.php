<style>
    .page-head{
        border-radius:22px;
        padding:16px;
        background:
            radial-gradient(circle at 0% 0%,rgba(28,184,166,.14),transparent 32%),
            linear-gradient(145deg,rgba(8,22,44,.96),rgba(3,8,23,.98));
        border:1px solid rgba(240,210,122,.36);
        box-shadow:0 18px 36px rgba(0,0,0,.34), inset 0 1px 0 rgba(255,255,255,.05);
    }

    .page-head h1{
        margin:0;
        color:#fff1c7;
        font-size:26px;
        line-height:1.1;
        font-weight:950;
        letter-spacing:-.035em;
    }

    .page-head p{
        margin:6px 0 0;
        color:#c9c7bd;
        font-size:13px;
        font-weight:750;
    }

    .trial-card-page{
        margin-top:12px;
        border-radius:22px;
        background:linear-gradient(145deg,#111d31,#071123);
        border:1px solid rgba(240,210,122,.34);
        box-shadow:0 18px 36px rgba(0,0,0,.30), inset 0 1px 0 rgba(255,255,255,.05);
        overflow:hidden;
    }

    .trial-card-body{
        padding:14px;
    }

    .trial-form{
        display:grid;
        gap:10px;
    }

    .trial-form label{
        display:grid;
        gap:6px;
        color:#c9c7bd;
        font-size:12px;
        font-weight:900;
    }

    .trial-form input,
    .trial-form select,
    .trial-form textarea{
        width:100%;
        min-height:44px;
        border-radius:15px;
        border:1px solid rgba(240,210,122,.32);
        background:#06111f;
        color:#f7f2dc;
        padding:0 12px;
        font-size:14px;
        font-weight:750;
        outline:none;
    }

    .trial-form textarea{
        min-height:84px;
        padding:12px;
        resize:vertical;
    }

    .trial-btn{
        min-height:46px;
        border-radius:16px;
        border:1px solid rgba(255,239,180,.58);
        background:linear-gradient(145deg,#ffe9a3,#b98928);
        color:#201606;
        font-size:14px;
        font-weight:950;
        cursor:pointer;
    }

    .trial-filter{
        display:grid;
        grid-template-columns:1fr auto;
        gap:9px;
        margin-top:12px;
    }

    .trial-filter input,
    .trial-filter select{
        min-height:44px;
        border-radius:15px;
        border:1px solid rgba(240,210,122,.32);
        background:#06111f;
        color:#f7f2dc;
        padding:0 12px;
        font-size:14px;
        font-weight:750;
    }

    .trial-filter button{
        min-width:92px;
        border-radius:15px;
        border:1px solid rgba(255,239,180,.58);
        background:linear-gradient(145deg,#ffe9a3,#b98928);
        color:#201606;
        font-size:13px;
        font-weight:950;
    }

    .trial-table-wrap{
        overflow:auto;
    }

    .trial-table{
        width:100%;
        border-collapse:separate;
        border-spacing:0;
        min-width:720px;
    }

    .trial-table th{
        text-align:left;
        padding:12px 11px;
        background:#0b1730;
        color:#f0d27a;
        font-size:11px;
        letter-spacing:.12em;
        text-transform:uppercase;
        border-bottom:1px solid rgba(240,210,122,.28);
        white-space:nowrap;
    }

    .trial-table td{
        padding:12px 11px;
        color:#e8e5db;
        font-size:13px;
        font-weight:750;
        border-bottom:1px solid rgba(255,255,255,.08);
        white-space:nowrap;
    }

    .trial-table tr:nth-child(even) td{
        background:rgba(255,255,255,.025);
    }

    .money{
        color:#35d579 !important;
        font-weight:950 !important;
    }

    .badge{
        display:inline-flex;
        align-items:center;
        min-height:28px;
        padding:0 10px;
        border-radius:999px;
        background:rgba(240,210,122,.10);
        color:#f0d27a;
        border:1px solid rgba(240,210,122,.32);
        font-size:12px;
        font-weight:900;
    }

    .alert-ok,
    .alert-error{
        margin-top:12px;
        border-radius:16px;
        padding:12px;
        font-size:13px;
        font-weight:850;
    }

    .alert-ok{
        background:rgba(53,213,121,.12);
        color:#75f0a8;
        border:1px solid rgba(53,213,121,.34);
    }

    .alert-error{
        background:rgba(255,66,87,.12);
        color:#ff9aa5;
        border:1px solid rgba(255,66,87,.34);
    }

    .profile-box{
        display:grid;
        grid-template-columns:86px 1fr;
        gap:14px;
        align-items:center;
    }

    .profile-avatar{
        width:86px;
        height:86px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#201606;
        background:linear-gradient(145deg,#ffe9a3,#b98928);
        border:1px solid rgba(255,239,180,.62);
        box-shadow:0 0 24px rgba(240,210,122,.20);
    }

    .profile-avatar svg{
        width:42px;
        height:42px;
        stroke:currentColor;
        fill:none;
        stroke-width:2.1;
    }

    .profile-meta b{
        display:block;
        color:#fff1c7;
        font-size:22px;
        font-weight:950;
    }

    .profile-meta span{
        display:block;
        margin-top:4px;
        color:#c9c7bd;
        font-size:13px;
        font-weight:800;
    }

    @media(max-width:560px){
        .page-head h1{
            font-size:23px;
        }

        .trial-filter{
            grid-template-columns:1fr;
        }

        .trial-card-body{
            padding:12px;
        }
    }
</style>

<style id="tagihan-trial-column-action-v1">
    .tagihan-table th,
    .tagihan-table td{
        vertical-align:middle;
    }

    .customer-cell{
        display:grid;
        gap:3px;
        min-width:160px;
    }

    .customer-cell b{
        color:#f7f2dc;
        font-size:13px;
        font-weight:950;
    }

    .customer-cell small{
        color:#9aa7b8;
        font-size:11px;
        font-weight:750;
    }

    .action-icons{
        display:flex;
        align-items:center;
        gap:8px;
        min-width:82px;
    }

    .action-icon{
        width:34px;
        height:34px;
        border-radius:12px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(255,255,255,.14);
        background:rgba(255,255,255,.06);
        box-shadow:inset 0 1px 0 rgba(255,255,255,.08);
    }

    .action-icon svg{
        width:18px;
        height:18px;
        fill:none;
        stroke:currentColor;
        stroke-width:2;
        stroke-linecap:round;
        stroke-linejoin:round;
    }

    .action-icon.pay{
        color:#35d579;
        border-color:rgba(53,213,121,.35);
        background:rgba(53,213,121,.10);
    }

    .action-icon.view{
        color:#74a8ff;
        border-color:rgba(116,168,255,.35);
        background:rgba(116,168,255,.10);
    }

    /* Status warna lama, tidak ikut gold theme baru */
    .status-old{
        min-height:30px;
        padding:0 11px;
        border-radius:999px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:12px;
        font-weight:950;
        white-space:nowrap;
        border:1px solid transparent;
    }

    .status-old-warning{
        color:#b88a00;
        background:#fff8dc;
        border-color:#ead48a;
    }

    .status-old-danger{
        color:#d64040;
        background:#fff0f0;
        border-color:#f3b5b5;
    }

    .status-old-success{
        color:#16824b;
        background:#eafaf1;
        border-color:#9fe1bd;
    }

    @media(max-width:560px){
        .tagihan-table{
            min-width:860px;
        }

        .action-icon{
            width:32px;
            height:32px;
        }
    }
</style>

<style id="tagihan-popup-bayar-detail-v2">
    .tagihan-table th,
    .tagihan-table td{
        vertical-align:middle;
    }

    .customer-cell{
        display:grid;
        gap:3px;
        min-width:160px;
    }

    .customer-cell b{
        color:#f7f2dc;
        font-size:13px;
        font-weight:950;
    }

    .customer-cell small{
        color:#9aa7b8;
        font-size:11px;
        font-weight:750;
    }

    .action-icons{
        display:flex;
        align-items:center;
        gap:8px;
        min-width:82px;
    }

    .action-icon{
        width:34px;
        height:34px;
        border-radius:12px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(255,255,255,.14);
        background:rgba(255,255,255,.06);
        box-shadow:inset 0 1px 0 rgba(255,255,255,.08);
        padding:0;
        cursor:pointer;
    }

    .action-icon svg{
        width:18px;
        height:18px;
        fill:none;
        stroke:currentColor;
        stroke-width:2;
        stroke-linecap:round;
        stroke-linejoin:round;
    }

    .action-icon.pay{
        color:#35d579;
        border-color:rgba(53,213,121,.35);
        background:rgba(53,213,121,.10);
    }

    .action-icon.view{
        color:#74a8ff;
        border-color:rgba(116,168,255,.35);
        background:rgba(116,168,255,.10);
    }

    .status-old{
        min-height:30px;
        padding:0 11px;
        border-radius:999px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:12px;
        font-weight:950;
        white-space:nowrap;
        border:1px solid transparent;
    }

    .status-old-warning{
        color:#b88a00;
        background:#fff8dc;
        border-color:#ead48a;
    }

    .status-old-danger{
        color:#d64040;
        background:#fff0f0;
        border-color:#f3b5b5;
    }

    .status-old-success{
        color:#16824b;
        background:#eafaf1;
        border-color:#9fe1bd;
    }

    .trial-modal{
        position:fixed;
        inset:0;
        z-index:200;
        display:none;
        align-items:center;
        justify-content:center;
        padding:18px;
    }

    .trial-modal.open{
        display:flex;
    }

    .trial-modal-backdrop{
        position:absolute;
        inset:0;
        background:rgba(0,0,0,.58);
        backdrop-filter:blur(4px);
    }

    .trial-modal-box{
        position:relative;
        z-index:2;
        width:min(440px,100%);
        border-radius:24px;
        background:
            radial-gradient(circle at 0% 0%,rgba(28,184,166,.14),transparent 32%),
            linear-gradient(145deg,#111d31,#071123);
        border:1px solid rgba(240,210,122,.40);
        box-shadow:0 26px 56px rgba(0,0,0,.52), inset 0 1px 0 rgba(255,255,255,.06);
        overflow:hidden;
    }

    .trial-modal-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        padding:15px 16px;
        border-bottom:1px solid rgba(240,210,122,.18);
    }

    .trial-modal-head h2{
        margin:0;
        font-size:20px;
        font-weight:950;
        color:#fff1c7;
    }

    .trial-modal-close{
        width:36px;
        height:36px;
        border-radius:12px;
        border:1px solid rgba(240,210,122,.28);
        background:rgba(255,255,255,.04);
        color:#fff1c7;
        font-size:24px;
        line-height:1;
        cursor:pointer;
    }

    .detail-grid{
        display:grid;
        gap:9px;
        padding:15px 16px 16px;
    }

    .detail-grid div{
        display:grid;
        gap:4px;
        padding:10px 11px;
        border-radius:15px;
        background:rgba(255,255,255,.04);
        border:1px solid rgba(255,255,255,.08);
    }

    .detail-grid span{
        color:#9aa7b8;
        font-size:11px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.08em;
    }

    .detail-grid b{
        color:#f7f2dc;
        font-size:14px;
        font-weight:950;
        word-break:break-word;
    }

    .pay-confirm-form{
        margin:0;
    }

    .pay-submit{
        width:calc(100% - 32px);
        margin:0 16px 16px;
    }

    @media(max-width:560px){
        .tagihan-table{
            min-width:900px;
        }

        .action-icon{
            width:32px;
            height:32px;
        }
    }
</style>

<style id="fix-419-bayar-link-v1">
    .pay-submit-link{
        display:flex !important;
        align-items:center !important;
        justify-content:center !important;
        text-decoration:none !important;
        width:calc(100% - 32px) !important;
        margin:0 16px 16px !important;
    }
</style>
<style id="tagihan-row-highlight-style-v1">
    .tagihan-row{
        transition:
            background .18s ease,
            box-shadow .18s ease,
            transform .12s ease;
        cursor:pointer;
    }

    .tagihan-row:active td{
        background:rgba(240,210,122,.10) !important;
    }

    .tagihan-row.row-selected td{
        background:
            linear-gradient(90deg, rgba(240,210,122,.18), rgba(53,213,121,.08)) !important;
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.06),
            inset 0 -1px 0 rgba(240,210,122,.22) !important;
    }

    .tagihan-row.row-selected td:first-child{
        color:#fff1c7 !important;
        border-left:4px solid #f0d27a !important;
    }

    .tagihan-row.row-selected .customer-cell b{
        color:#fff1c7 !important;
        text-shadow:0 0 12px rgba(240,210,122,.18);
    }

    .tagihan-row.row-selected .money{
        color:#62df8a !important;
        text-shadow:0 0 10px rgba(98,223,138,.16);
    }

    @media(max-width:560px){
        .tagihan-row.row-selected td:first-child{
            border-left-width:3px !important;
        }
    }
</style>









<style id="manual-period-autofill-final-style-v2">
    .livefind-label{
        position:relative;
    }

    .livefind-box{
        position:relative;
    }

    .livefind-results{
        position:absolute;
        z-index:120;
        left:0;
        right:0;
        top:calc(100% + 8px);
        display:none;
        max-height:320px;
        overflow:auto;
        padding:8px;
        border-radius:18px;
        background:
            radial-gradient(circle at 0% 0%,rgba(28,184,166,.16),transparent 32%),
            linear-gradient(145deg,#111d31,#071123);
        border:1px solid rgba(240,210,122,.36);
        box-shadow:0 24px 52px rgba(0,0,0,.48), inset 0 1px 0 rgba(255,255,255,.05);
    }

    .livefind-results.open{
        display:grid;
        gap:8px;
    }

    .livefind-item{
        width:100%;
        min-height:58px;
        border-radius:15px;
        border:1px solid rgba(255,255,255,.09);
        background:rgba(255,255,255,.04);
        color:#f7f2dc;
        display:grid;
        grid-template-columns:1fr auto;
        gap:10px;
        align-items:center;
        text-align:left;
        padding:9px 11px;
        cursor:pointer;
    }

    .livefind-item:active,
    .livefind-item:hover{
        background:rgba(240,210,122,.12);
        border-color:rgba(240,210,122,.36);
    }

    .livefind-item b{
        display:block;
        font-size:14px;
        font-weight:950;
        color:#fff1c7;
    }

    .livefind-item small{
        display:block;
        margin-top:2px;
        font-size:11px;
        color:#9aa7b8;
        font-weight:800;
    }

    .livefind-item em{
        font-style:normal;
        text-align:right;
        color:#35d579;
        font-size:11px;
        font-weight:950;
        white-space:nowrap;
    }

    .livefind-empty{
        padding:12px;
        color:#ffb4bd;
        font-size:13px;
        font-weight:850;
    }

    .field-note{
        display:block;
        margin-top:4px;
        color:#9aa7b8;
        font-size:11px;
        font-weight:800;
    }

    input[readonly]{
        opacity:1;
        color:#fff1c7 !important;
        background:#07101b !important;
    }

    .autofilled{
        animation: autofillPulse .7s ease;
    }

    @keyframes autofillPulse{
        0%{ box-shadow:0 0 0 0 rgba(240,210,122,.0); }
        35%{
            box-shadow:0 0 0 4px rgba(240,210,122,.20);
            border-color:rgba(240,210,122,.62);
        }
        100%{ box-shadow:0 0 0 0 rgba(240,210,122,.0); }
    }

    @media(max-width:560px){
        .livefind-item{
            grid-template-columns:1fr;
            min-height:60px;
        }

        .livefind-item em{
            text-align:left;
        }
    }
</style>

<style id="tagihan-gabungan-style-v1">
    .grouped-billing-table .customer-cell b{
        display:block;
        color:#fff1c7;
        font-size:14px;
        font-weight:950;
    }

    .grouped-billing-table .customer-cell small,
    .period-mini{
        display:block;
        margin-top:3px;
        color:#9aa7b8;
        font-size:11px;
        font-weight:800;
    }

    .trial-modal-box.wide{
        width:min(520px,100%);
    }

    .group-period-list{
        display:grid;
        gap:9px;
        margin:14px 16px 16px;
    }

    .group-period-item{
        display:grid;
        grid-template-columns:1fr auto;
        gap:10px;
        align-items:center;
        min-height:62px;
        padding:10px 12px;
        border-radius:16px;
        background:rgba(255,255,255,.045);
        border:1px solid rgba(255,255,255,.08);
    }

    .group-period-item.can-check{
        grid-template-columns:24px 1fr auto;
        cursor:pointer;
    }

    .group-period-item input{
        width:18px;
        height:18px;
        accent-color:#f0d27a;
    }

    .group-period-item b{
        display:block;
        color:#fff1c7;
        font-size:14px;
        font-weight:950;
    }

    .group-period-item small{
        display:block;
        margin-top:3px;
        color:#9aa7b8;
        font-size:11px;
        font-weight:800;
    }

    .group-period-item em{
        font-style:normal;
        text-align:right;
    }

    .group-period-item strong{
        display:block;
        color:#35d579;
        font-size:13px;
        font-weight:950;
    }

    .group-period-item i{
        display:inline-flex;
        margin-top:4px;
        padding:3px 7px;
        border-radius:999px;
        font-style:normal;
        font-size:10px;
        font-weight:950;
    }

    .group-period-item i.danger{
        color:#ffd5da;
        background:rgba(255,66,87,.18);
        border:1px solid rgba(255,66,87,.26);
    }

    .group-period-item i.warning{
        color:#fff1c7;
        background:rgba(240,210,122,.16);
        border:1px solid rgba(240,210,122,.28);
    }

    .pay-total-box{
        margin:0 16px 14px;
        padding:13px 14px;
        border-radius:16px;
        background:rgba(240,210,122,.10);
        border:1px solid rgba(240,210,122,.22);
        display:flex;
        justify-content:space-between;
        align-items:center;
    }

    .pay-total-box span{
        color:#9aa7b8;
        font-size:12px;
        font-weight:850;
    }

    .pay-total-box b{
        color:#fff1c7;
        font-size:18px;
        font-weight:950;
    }

    .pay-submit-link.disabled{
        opacity:.45;
        pointer-events:none;
    }

    @media(max-width:560px){
        .group-period-item,
        .group-period-item.can-check{
            grid-template-columns:1fr;
        }

        .group-period-item.can-check{
            position:relative;
            padding-left:42px;
        }

        .group-period-item.can-check input{
            position:absolute;
            top:18px;
            left:14px;
        }

        .group-period-item em{
            text-align:left;
        }
    }
</style>

<style id="status-pelanggan-style-v1">
    .status-customer-table .customer-cell b{
        display:block;
        color:#fff1c7;
        font-size:14px;
        font-weight:950;
    }

    .status-customer-table .customer-cell small{
        display:block;
        margin-top:3px;
        color:#9aa7b8;
        font-size:11px;
        font-weight:800;
    }

    .address-cell{
        max-width:240px;
        white-space:normal !important;
        line-height:1.35;
    }

    .connection-pill{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:74px;
        min-height:27px;
        padding:5px 10px;
        border-radius:999px;
        font-size:11px;
        font-weight:950;
        letter-spacing:.02em;
    }

    .conn-active{
        color:#bbffd1;
        background:rgba(53,213,121,.16);
        border:1px solid rgba(53,213,121,.30);
    }

    .conn-offline{
        color:#ffd5da;
        background:rgba(255,66,87,.16);
        border:1px solid rgba(255,66,87,.30);
    }

    .conn-muted,
    .status-old-muted{
        color:#c8ccd4;
        background:rgba(255,255,255,.08);
        border:1px solid rgba(255,255,255,.12);
    }

    .trial-modal-box.wide{
        width:min(560px,100%);
    }

    .customer-detail-box{
        max-height:86vh;
        overflow:auto;
    }

    .detail-section{
        margin:14px 16px 16px;
        padding:14px;
        border-radius:18px;
        background:rgba(255,255,255,.045);
        border:1px solid rgba(255,255,255,.08);
    }

    .detail-section h3{
        margin:0 0 10px;
        color:#fff1c7;
        font-size:15px;
        font-weight:950;
    }

    .detail-section p{
        margin:7px 0;
        color:#cfd6df;
        font-size:13px;
        line-height:1.35;
        font-weight:750;
    }

    .detail-section p b{
        color:#9aa7b8;
        font-weight:900;
    }

    @media(max-width:560px){
        .address-cell{
            max-width:180px;
        }

        .connection-pill{
            min-width:62px;
            font-size:10px;
        }
    }
</style>


<style id="global-payment-status-style-v1">
    /* GLOBAL STATUS PEMBAYARAN PORTAL PERCOBAAN */
    .status-pill,
    .status-old-danger,
    .status-old-warning,
    .status-old-success,
    .status-old-muted,
    .status-nunggak,
    .status-belum-bayar,
    .status-bayar-awal,
    .status-lunas,
    .status-muted{
        display:inline-flex !important;
        align-items:center !important;
        justify-content:center !important;
        min-width:74px !important;
        min-height:27px !important;
        padding:5px 10px !important;
        border-radius:999px !important;
        font-size:11px !important;
        font-weight:950 !important;
        letter-spacing:.02em !important;
        line-height:1.1 !important;
        white-space:nowrap !important;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.06) !important;
    }

    /* NUNGGAK = MERAH TRANSPARAN */
    .status-nunggak,
    .status-old-danger,
    .status-pill.status-nunggak,
    .status-pill.status-old-danger{
        color:#ffd5da !important;
        background:rgba(255,66,87,.16) !important;
        border:1px solid rgba(255,66,87,.30) !important;
    }

    /* BELUM BAYAR = KUNING TRANSPARAN */
    .status-belum-bayar,
    .status-old-warning,
    .status-pill.status-belum-bayar,
    .status-pill.status-old-warning{
        color:#fff1c7 !important;
        background:rgba(240,210,122,.16) !important;
        border:1px solid rgba(240,210,122,.30) !important;
    }

    /* BAYAR AWAL = BIRU TRANSPARAN */
    .status-bayar-awal,
    .status-pill.status-bayar-awal{
        color:#bdd8ff !important;
        background:rgba(76,145,255,.16) !important;
        border:1px solid rgba(76,145,255,.32) !important;
    }

    /* LUNAS = HIJAU TRANSPARAN */
    .status-lunas,
    .status-old-success,
    .status-pill.status-lunas,
    .status-pill.status-old-success{
        color:#bbffd1 !important;
        background:rgba(53,213,121,.16) !important;
        border:1px solid rgba(53,213,121,.30) !important;
    }

    /* KOSONG / - = ABU TRANSPARAN */
    .status-muted,
    .status-old-muted,
    .status-pill.status-muted,
    .status-pill.status-old-muted{
        color:#c8ccd4 !important;
        background:rgba(255,255,255,.08) !important;
        border:1px solid rgba(255,255,255,.12) !important;
    }

    /* Hilangkan efek putih kotak dari style lama */
    .trial-table .status-pill,
    .trial-table span[class*="status-"]{
        text-shadow:none !important;
    }

    @media(max-width:560px){
        .status-pill,
        .status-old-danger,
        .status-old-warning,
        .status-old-success,
        .status-old-muted,
        .status-nunggak,
        .status-belum-bayar,
        .status-bayar-awal,
        .status-lunas,
        .status-muted{
            min-width:66px !important;
            min-height:27px !important;
            padding:5px 9px !important;
            font-size:10.5px !important;
        }
    }
</style>

<style id="profile-active-style-v1">
    .profile-hero-card{
        display:flex;
        align-items:center;
        gap:16px;
        padding:18px;
        margin-bottom:16px;
        border-radius:28px;
        background:
            radial-gradient(circle at 0% 0%,rgba(240,210,122,.18),transparent 32%),
            linear-gradient(145deg,#111d31,#071123);
        border:1px solid rgba(240,210,122,.30);
        box-shadow:0 18px 46px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.05);
    }

    .profile-avatar-big{
        width:76px;
        height:76px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        flex:0 0 auto;
        color:#071123;
        font-size:32px;
        font-weight:950;
        background:linear-gradient(135deg,#fff1c7,#d7ad32);
        box-shadow:0 12px 28px rgba(240,210,122,.22);
    }

    .profile-hero-info h2{
        margin:0;
        color:#fff1c7;
        font-size:26px;
        line-height:1.1;
        font-weight:950;
    }

    .profile-hero-info p{
        margin:6px 0 9px;
        color:#cfd6df;
        font-size:13px;
        font-weight:800;
    }

    .profile-hero-info span{
        display:inline-flex;
        padding:5px 10px;
        border-radius:999px;
        color:#bbffd1;
        background:rgba(53,213,121,.16);
        border:1px solid rgba(53,213,121,.30);
        font-size:11px;
        font-weight:950;
    }

    .profile-stat-grid{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:12px;
        margin-bottom:16px;
    }

    .profile-stat-card{
        padding:15px 14px;
        border-radius:20px;
        background:rgba(255,255,255,.045);
        border:1px solid rgba(255,255,255,.08);
    }

    .profile-stat-card span{
        display:block;
        color:#9aa7b8;
        font-size:12px;
        font-weight:900;
    }

    .profile-stat-card b{
        display:block;
        margin-top:7px;
        color:#35d579;
        font-size:20px;
        font-weight:950;
        line-height:1.1;
    }

    .profile-stat-card b.small-value{
        color:#fff1c7;
        font-size:15px;
    }

    .profile-stat-card small{
        display:block;
        margin-top:7px;
        color:#c8ccd4;
        font-size:11px;
        font-weight:800;
    }

    .profile-grid{
        display:grid;
        grid-template-columns:minmax(0,1.4fr) minmax(280px,.6fr);
        gap:14px;
    }

    .profile-form h3,
    .profile-side-card h3{
        margin:0 0 12px;
        color:#fff1c7;
        font-size:20px;
        font-weight:950;
    }

    .profile-note{
        margin:-4px 0 12px;
        color:#9aa7b8;
        font-size:12px;
        line-height:1.35;
        font-weight:800;
    }

    .profile-info-list{
        display:grid;
        gap:10px;
    }

    .profile-info-list div{
        padding:12px 13px;
        border-radius:16px;
        background:rgba(255,255,255,.045);
        border:1px solid rgba(255,255,255,.08);
    }

    .profile-info-list span{
        display:block;
        color:#9aa7b8;
        font-size:11px;
        font-weight:900;
    }

    .profile-info-list b{
        display:block;
        margin-top:5px;
        color:#fff1c7;
        font-size:13px;
        line-height:1.35;
        font-weight:950;
        word-break:break-word;
    }

    .profile-logout-form{
        margin-top:14px;
    }

    .profile-logout-btn{
        width:100%;
        height:46px;
        border:1px solid rgba(255,66,87,.34);
        border-radius:16px;
        background:rgba(255,66,87,.14);
        color:#ffd5da;
        font-size:14px;
        font-weight:950;
        cursor:pointer;
    }

    .profile-btn{
        text-decoration:none !important;
    }

    @media(max-width:780px){
        .profile-stat-grid,
        .profile-grid{
            grid-template-columns:1fr;
        }

        .profile-hero-card{
            align-items:flex-start;
        }

        .profile-avatar-big{
            width:64px;
            height:64px;
            font-size:28px;
        }

        .profile-hero-info h2{
            font-size:22px;
        }
    }
</style>
