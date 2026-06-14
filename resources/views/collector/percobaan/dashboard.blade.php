@extends('collector.percobaan.layout')
@section('title','Dashboard Percobaan Kasir')

@section('content')

@php
    $photoUrl = function ($path) {
        if (!$path) return null;
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }
        return asset('storage/'.$path);
    };
@endphp

@php
    $statsData = $stats ?? [];
    $annualData = $annualStats ?? [];
    $collectorName = auth()->user()->name ?? 'Kasir';

    $readStat = function ($keys, $default = 0) use ($statsData) {
        foreach ((array) $keys as $key) {
            $value = data_get($statsData, $key, null);
            if ($value !== null) return $value;
        }
        return $default;
    };

    $siapTagih = (int) $readStat(['siap_tagih','ready_to_bill','readyToBill','invoice_ready','tagihan_siap'], 0);
    $pendapatanTertunda = (float) $readStat(['pendapatan_tertunda','pending_revenue','pendingRevenue','unpaid_amount','tagihan_tertunda'], 0);
    $pemasukanBulanIni = (float) $readStat(['pemasukan_bulan_ini','monthly_income','month_income','income_this_month','pemasukan'], 0);
    $pengeluaranBulanIni = (float) $readStat(['pengeluaran_bulan_ini','monthly_expense','month_expense','expense_this_month','pengeluaran'], 0);
    $profitBulanIni = (float) $readStat(['profit_bulan_ini','monthly_profit','month_profit','profit_this_month','profit'], $pemasukanBulanIni - $pengeluaranBulanIni);
    $nunggak = (int) $readStat(['nunggak','overdue','overdue_count','customers_overdue'], 0);

    $money = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

    $monthNames = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];

    $annualRaw = collect(data_get($annualData, 'months', $annualData ?? []));
    $yearLabel = data_get($annualData, 'year', now()->year);

    $months = [];
    for ($i = 1; $i <= 12; $i++) {
        $months[$i] = [
            'month_no' => $i,
            'label' => $monthNames[$i],
            'income' => 0,
            'expense' => 0,
            'profit' => 0,
        ];
    }

    foreach ($annualRaw as $k => $row) {
        if (!is_array($row) && !is_object($row)) continue;

        $monthNo = data_get($row, 'month_no', data_get($row, 'month_number', data_get($row, 'month', data_get($row, 'bulan', $k))));
        $monthNo = is_numeric($monthNo) ? (int) $monthNo : null;
        if (!$monthNo || $monthNo < 1 || $monthNo > 12) continue;

        $income = (float) data_get($row, 'income', data_get($row, 'pemasukan', data_get($row, 'total_income', 0)));
        $expense = (float) data_get($row, 'expense', data_get($row, 'pengeluaran', data_get($row, 'total_expense', 0)));
        $profit = (float) data_get($row, 'profit', data_get($row, 'laba', data_get($row, 'total_profit', $income - $expense)));

        $months[$monthNo] = [
            'month_no' => $monthNo,
            'label' => $monthNames[$monthNo],
            'income' => $income,
            'expense' => $expense,
            'profit' => $profit,
        ];
    }

    $months = collect($months)->values();
    $bestMonth = $months->sortByDesc('income')->first();
    $bestMonthLabel = $bestMonth['label'] ?? now()->translatedFormat('M');
    $bestMonthIncome = (float) ($bestMonth['income'] ?? 0);
    $maxIncome = max(1, (float) $months->max('income'));
    $chartMonths = $months->take(6)->values();

    $iv = fn($name) => asset('assets/percobaan-icons/'.$name.'.webp').'?v=imgicon1';
@endphp

@push('styles')
<style>
    .ref-wrap{display:flex;flex-direction:column;gap:14px;}
    .ref-panel{position:relative;border-radius:28px;background:radial-gradient(circle at 0% 0%, rgba(24,185,158,.18), transparent 30%),radial-gradient(circle at 98% 0%, rgba(255,166,61,.13), transparent 26%),linear-gradient(145deg,rgba(8,22,44,.96),rgba(3,8,23,.98));border:1px solid rgba(240,210,122,.40);box-shadow:0 0 0 1px rgba(255,240,190,.08),0 0 28px rgba(240,210,122,.18),0 22px 46px rgba(0,0,0,.44),inset 0 1px 0 rgba(255,255,255,.05);overflow:hidden;}
    .ref-panel::before{content:"";position:absolute;inset:1px;border-radius:27px;border:1px solid rgba(255,238,177,.12);pointer-events:none;}
    .hero{min-height:215px;padding:23px 20px;display:grid;grid-template-columns:88px 1fr auto;gap:16px;align-items:start;}
    .crown-orb{width:76px;height:76px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:radial-gradient(circle at 32% 22%,rgba(255,255,255,.18),transparent 28%),rgba(255,255,255,.035);border:1px solid rgba(240,210,122,.38);box-shadow:0 0 28px rgba(240,210,122,.30),inset 0 2px 8px rgba(255,255,255,.12);}
    .crown-orb img{width:66px;height:66px;object-fit:contain;filter:drop-shadow(0 6px 9px rgba(0,0,0,.32));}
    .hero-kicker{font-size:13px;font-weight:950;letter-spacing:.20em;text-transform:uppercase;color:#cecabe;}
    .hero-hi{margin-top:9px;font-size:26px;line-height:1;font-weight:500;color:#f3f1e8;}
    .hero-name{margin-top:5px;display:inline-block;font-size:39px;line-height:1.02;font-weight:950;letter-spacing:-.045em;color:var(--gold);text-shadow:0 0 22px rgba(240,210,122,.22);}
    .hero-line{margin-top:14px;width:80px;height:4px;border-radius:999px;background:linear-gradient(90deg,var(--gold),transparent);}
    .hero-sub{margin-top:14px;max-width:455px;color:#d4d2ca;font-size:15px;line-height:1.45;font-weight:650;}
    .year-badge{min-width:132px;height:54px;border-radius:999px;display:flex;align-items:center;justify-content:center;gap:10px;color:var(--gold2);font-size:18px;font-weight:950;border:1px solid rgba(240,210,122,.45);background:rgba(255,255,255,.045);box-shadow:inset 0 1px 0 rgba(255,255,255,.07),0 0 18px rgba(240,210,122,.14);}
    .year-badge svg{width:23px;height:23px;stroke:currentColor;fill:none;stroke-width:2;}
    .metric-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;}
    .metric-card{min-height:270px;padding:18px 16px;text-align:center;text-decoration:none;color:#f7f2dc;display:flex;flex-direction:column;justify-content:space-between;}
    .big-icon{height:108px;display:flex;align-items:center;justify-content:center;}
    .asset-icon{display:block;object-fit:contain;filter:drop-shadow(0 14px 18px rgba(0,0,0,.36));}
    .asset-icon.doc{width:105px;height:100px;}
    .asset-icon.hour{width:100px;height:108px;}
    .asset-icon.coin{width:112px;height:112px;}
    .asset-icon.wallet{width:112px;height:108px;}
    .asset-icon.chart{width:110px;height:104px;}
    .asset-icon.alarm{width:108px;height:112px;}
    .metric-title{margin-top:6px;font-size:20px;line-height:1.15;font-weight:950;color:#eee9dd;text-shadow:0 0 12px rgba(255,255,255,.10);}
    .metric-value{margin-top:8px;font-size:39px;line-height:1;font-weight:950;letter-spacing:-.05em;white-space:nowrap;font-variant-numeric:tabular-nums;}
    .metric-note{margin-top:18px;color:#c7c4be;font-size:15px;font-weight:750;}
    .metric-note span{color:var(--gold);margin-left:6px;}
    .gold-text{color:var(--gold)}.purple-text{color:var(--purple)}.green-text{color:var(--green)}.red-text{color:var(--red)}.blue-text{color:#72a8ff}
    .recap{padding:20px 20px 18px;}
    .recap-head{display:flex;align-items:center;justify-content:space-between;gap:12px;}
    .recap-title{font-family:Georgia,"Times New Roman",serif;font-size:35px;line-height:1;font-weight:800;color:var(--gold2);letter-spacing:-.03em;text-shadow:0 0 16px rgba(240,210,122,.16);}
    .recap-badge{height:42px;min-width:106px;border-radius:999px;border:1px solid rgba(240,210,122,.42);display:flex;align-items:center;justify-content:center;gap:8px;color:var(--gold2);font-size:16px;font-weight:950;background:rgba(255,255,255,.04);}
    .recap-badge svg{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;}
    .best-row{margin-top:16px;min-height:68px;border-radius:18px;border:1px solid rgba(240,210,122,.35);display:grid;grid-template-columns:1fr 56px;align-items:center;padding:12px 14px;background:rgba(255,255,255,.035);}
    .best-row small{display:block;color:#ded6bd;font-size:14px;font-weight:800;}.best-row strong{display:block;margin-top:2px;color:#fff;font-size:21px;font-weight:900;letter-spacing:.04em;}
    .mini-crown{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--gold);border:1px solid rgba(240,210,122,.45);background:rgba(255,255,255,.04);}
    .mini-crown img{width:38px;height:38px;object-fit:contain;}
    .chart-box{margin-top:16px;height:195px;position:relative;border-bottom:1px solid rgba(240,210,122,.25);}.chart-svg{width:100%;height:150px;display:block;overflow:visible;}
    .axis-labels{position:absolute;left:0;top:2px;width:58px;height:145px;display:flex;flex-direction:column;justify-content:space-between;color:#d7ceb2;font-size:13px;font-weight:800;}
    .chart-months{margin-left:58px;display:grid;grid-template-columns:repeat(6,1fr);gap:0;color:#d7ceb2;font-size:13px;font-weight:800;transform:translateY(-4px);}
    .bottom-months{margin-top:18px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}
    .mini-month{min-height:78px;border-radius:18px;border:1px solid rgba(240,210,122,.32);background:rgba(255,255,255,.035);padding:12px 14px;position:relative;overflow:hidden;}
    .mini-month b{display:block;color:#fff;font-size:18px;line-height:1;font-weight:950;}.mini-lines{margin-top:8px;display:grid;grid-template-columns:1fr 1fr 38px;gap:8px;align-items:end;}.mini-lines span{display:block;color:#afaeb3;font-size:11px;font-weight:750;}.mini-lines strong{display:block;margin-top:2px;white-space:nowrap;font-size:13px;font-weight:950;}.mini-lines .in strong{color:var(--green)}.mini-lines .out strong{color:var(--red)}.growth{color:var(--green);text-align:right;font-size:18px;font-weight:950;}.growth small{display:block;font-size:8px;color:#a9b2a9;font-weight:750;}
    @media(max-width:560px){.hero{min-height:160px;grid-template-columns:54px 1fr auto;gap:10px;padding:15px 14px;border-radius:24px}.crown-orb{width:48px;height:48px}.crown-orb img{width:42px;height:42px}.hero-kicker{font-size:10px;letter-spacing:.16em}.hero-hi{font-size:20px;margin-top:8px}.hero-name{font-size:29px}.hero-line{width:58px;height:3px;margin-top:10px}.hero-sub{margin-top:10px;font-size:12px;max-width:100%}.year-badge{min-width:84px;height:38px;font-size:14px;gap:6px}.year-badge svg{width:17px;height:17px}.metric-grid{gap:12px}.metric-card{min-height:205px;padding:14px 10px;border-radius:18px}.big-icon{height:78px}.asset-icon.doc{width:80px;height:75px}.asset-icon.hour{width:75px;height:82px}.asset-icon.coin{width:84px;height:84px}.asset-icon.wallet{width:84px;height:80px}.asset-icon.chart{width:82px;height:78px}.asset-icon.alarm{width:82px;height:86px}.metric-title{font-size:16px}.metric-value{font-size:26px}.metric-note{margin-top:12px;font-size:12px}.recap{border-radius:22px;padding:16px 14px}.recap-title{font-size:25px}.recap-badge{height:34px;min-width:76px;font-size:13px}.best-row{min-height:58px}.best-row strong{font-size:16px;letter-spacing:.02em}.chart-box{height:175px}.chart-svg{height:132px}.axis-labels{height:128px;width:48px;font-size:11px}.chart-months{margin-left:48px;font-size:11px}.bottom-months{grid-template-columns:1fr}.mini-lines{grid-template-columns:1fr 1fr 44px}}

/* portal-clean-hero-v1 */
.hero.hero-clean{
    min-height:185px !important;
    display:flex !important;
    grid-template-columns:none !important;
    align-items:center !important;
    justify-content:flex-start !important;
    padding:34px 32px !important;
}

.hero.hero-clean::after{
    display:none !important;
}

.hero.hero-clean .hero-text-only{
    width:100%;
    position:relative;
    z-index:2;
}

.hero.hero-clean .hero-hi{
    margin:0 !important;
    font-size:33px !important;
    line-height:1.05 !important;
    font-weight:650 !important;
    color:#f3f1e8 !important;
    letter-spacing:-.025em !important;
}

.hero.hero-clean .hero-name{
    margin-top:10px !important;
    display:block !important;
    font-size:48px !important;
    line-height:1.02 !important;
    font-weight:950 !important;
    letter-spacing:-.055em !important;
    color:var(--gold) !important;
    text-shadow:0 0 22px rgba(240,210,122,.24) !important;
}

.hero.hero-clean .hero-line{
    margin-top:20px !important;
    width:88px !important;
    height:4px !important;
    border-radius:999px !important;
    background:linear-gradient(90deg,var(--gold),transparent) !important;
}

@media(max-width:560px){
    .hero.hero-clean{
        min-height:150px !important;
        padding:28px 26px !important;
        border-radius:24px !important;
    }

    .hero.hero-clean .hero-hi{
        font-size:30px !important;
    }

    .hero.hero-clean .hero-name{
        font-size:42px !important;
    }

    .hero.hero-clean .hero-line{
        margin-top:16px !important;
        width:74px !important;
        height:4px !important;
    }
}

@media(max-width:390px){
    .hero.hero-clean{
        min-height:142px !important;
        padding:24px 22px !important;
    }

    .hero.hero-clean .hero-hi{
        font-size:26px !important;
    }

    .hero.hero-clean .hero-name{
        font-size:37px !important;
    }
}
/* end portal-clean-hero-v1 */


/* portal-icon-size-match-cashier-v1 */

/* Samakan ukuran icon dengan rasa dashboard kasir aktif:
   tidak terlalu besar, tetap jelas, dan tidak mendominasi card. */
.metric-card{
    min-height:220px !important;
    padding:16px 14px !important;
}

.big-icon{
    height:76px !important;
    margin-top:2px !important;
    margin-bottom:8px !important;
}

/* Target icon berbasis gambar */
.big-icon img,
.metric-card img,
.icon-doc img,
.icon-hour img,
.icon-coin img,
.icon-wallet img,
.icon-chart img,
.icon-alarm img{
    width:74px !important;
    height:74px !important;
    max-width:74px !important;
    max-height:74px !important;
    object-fit:contain !important;
    display:block !important;
    margin:0 auto !important;
}

/* Target wrapper icon lama */
.icon-doc,
.icon-hour,
.icon-coin,
.icon-wallet,
.icon-chart,
.icon-alarm{
    width:74px !important;
    height:74px !important;
    max-width:74px !important;
    max-height:74px !important;
    margin:0 auto !important;
}

/* Jika masih ada SVG fallback, kecilkan juga */
.icon-doc svg,
.icon-hour svg,
.icon-coin svg,
.icon-wallet svg,
.icon-chart svg,
.icon-alarm svg,
.big-icon svg{
    width:74px !important;
    height:74px !important;
    max-width:74px !important;
    max-height:74px !important;
}

/* Typography disesuaikan agar card lebih lega */
.metric-title{
    margin-top:4px !important;
    font-size:18px !important;
    line-height:1.18 !important;
}

.metric-value{
    margin-top:7px !important;
    font-size:32px !important;
    line-height:1.05 !important;
}

.metric-note{
    margin-top:13px !important;
    font-size:13px !important;
}

/* Mobile */
@media(max-width:560px){
    .metric-card{
        min-height:190px !important;
        padding:13px 10px !important;
    }

    .big-icon{
        height:66px !important;
        margin-bottom:7px !important;
    }

    .big-icon img,
    .metric-card img,
    .icon-doc img,
    .icon-hour img,
    .icon-coin img,
    .icon-wallet img,
    .icon-chart img,
    .icon-alarm img,
    .icon-doc,
    .icon-hour,
    .icon-coin,
    .icon-wallet,
    .icon-chart,
    .icon-alarm,
    .icon-doc svg,
    .icon-hour svg,
    .icon-coin svg,
    .icon-wallet svg,
    .icon-chart svg,
    .icon-alarm svg,
    .big-icon svg{
        width:64px !important;
        height:64px !important;
        max-width:64px !important;
        max-height:64px !important;
    }

    .metric-title{
        font-size:15.5px !important;
    }

    .metric-value{
        font-size:25px !important;
    }

    .metric-note{
        font-size:11.5px !important;
    }
}

/* Layar kecil */
@media(max-width:390px){
    .metric-card{
        min-height:180px !important;
    }

    .big-icon{
        height:60px !important;
    }

    .big-icon img,
    .metric-card img,
    .icon-doc img,
    .icon-hour img,
    .icon-coin img,
    .icon-wallet img,
    .icon-chart img,
    .icon-alarm img,
    .icon-doc,
    .icon-hour,
    .icon-coin,
    .icon-wallet,
    .icon-chart,
    .icon-alarm,
    .icon-doc svg,
    .icon-hour svg,
    .icon-coin svg,
    .icon-wallet svg,
    .icon-chart svg,
    .icon-alarm svg,
    .big-icon svg{
        width:58px !important;
        height:58px !important;
        max-width:58px !important;
        max-height:58px !important;
    }

    .metric-value{
        font-size:23px !important;
    }
}

/* end portal-icon-size-match-cashier-v1 */


/* portal-remove-hero-crown-monitoring-v1 */

/* Pastikan icon mahkota di hero dan teks monitoring tidak tampil walau HTML lama masih tersisa */
.hero .crown-orb,
.hero .hero-sub{
    display:none !important;
}

/* Karena mahkota kiri dihapus, layout hero dibuat lebih rapi */
.hero{
    grid-template-columns:1fr auto !important;
    align-items:start !important;
}

/* Jika ingin lebih simpel: teks utama agak digeser normal ke kiri */
.hero > div:not(.year-badge){
    min-width:0 !important;
}

/* Jarak hero setelah teks monitoring dihapus */
.hero-line{
    margin-top:14px !important;
}

/* Mobile */
@media(max-width:560px){
    .hero{
        grid-template-columns:1fr auto !important;
        min-height:132px !important;
    }

    .hero-name{
        font-size:38px !important;
    }

    .hero-hi{
        font-size:25px !important;
    }
}

/* end portal-remove-hero-crown-monitoring-v1 */


/* portal-compact-dashboard-v1 */

/* Shell dan jarak antar elemen dibuat lebih padat */
.ref-wrap{
    gap:10px !important;
}

/* Hero dipadatkan */
.hero{
    min-height:132px !important;
    padding:20px 22px !important;
    border-radius:22px !important;
}

.hero-kicker{
    font-size:11px !important;
    letter-spacing:.16em !important;
}

.hero-hi{
    font-size:24px !important;
    margin-top:8px !important;
}

.hero-name{
    font-size:38px !important;
    line-height:1.02 !important;
    margin-top:6px !important;
}

.hero-line{
    width:70px !important;
    height:3px !important;
    margin-top:12px !important;
}

.year-badge{
    min-width:92px !important;
    height:38px !important;
    font-size:14px !important;
    gap:7px !important;
}

.year-badge svg{
    width:17px !important;
    height:17px !important;
}

/* Grid card lebih rapat */
.metric-grid{
    gap:10px !important;
}

/* Card dibuat compact */
.metric-card{
    min-height:158px !important;
    padding:12px 10px !important;
    border-radius:18px !important;
}

/* Icon mengikuti rasa kasir aktif: tidak terlalu dominan */
.big-icon{
    height:54px !important;
    margin:0 0 6px 0 !important;
}

.icon-doc,
.icon-hour,
.icon-coin,
.icon-wallet,
.icon-chart,
.icon-alarm,
.big-icon img,
.metric-card img,
.big-icon svg{
    width:54px !important;
    height:54px !important;
    max-width:54px !important;
    max-height:54px !important;
    object-fit:contain !important;
    margin:0 auto !important;
}

/* Judul dan nominal dibuat compact */
.metric-title{
    margin-top:2px !important;
    font-size:14px !important;
    line-height:1.18 !important;
    font-weight:950 !important;
}

.metric-value{
    margin-top:6px !important;
    font-size:24px !important;
    line-height:1.02 !important;
    white-space:nowrap !important;
}

.metric-note{
    margin-top:9px !important;
    font-size:10.8px !important;
    line-height:1.2 !important;
}

/* Rekap juga dipadatkan */
.recap{
    border-radius:22px !important;
    padding:15px 14px !important;
}

.recap-title{
    font-size:25px !important;
}

.recap-badge{
    height:34px !important;
    min-width:78px !important;
    font-size:13px !important;
}

.best-row{
    margin-top:12px !important;
    min-height:56px !important;
    border-radius:16px !important;
    padding:10px 12px !important;
}

.best-row small{
    font-size:12px !important;
}

.best-row strong{
    font-size:15px !important;
}

.chart-box{
    margin-top:12px !important;
    height:155px !important;
}

.chart-svg{
    height:116px !important;
}

.bottom-months{
    margin-top:12px !important;
    gap:10px !important;
}

/* Mobile */
@media(max-width:560px){
    .ref-wrap{
        gap:9px !important;
    }

    .hero{
        min-height:118px !important;
        padding:18px 20px !important;
        border-radius:21px !important;
    }

    .hero-hi{
        font-size:22px !important;
    }

    .hero-name{
        font-size:34px !important;
    }

    .hero-line{
        width:62px !important;
        margin-top:10px !important;
    }

    .year-badge{
        min-width:74px !important;
        height:32px !important;
        font-size:12px !important;
    }

    .metric-grid{
        gap:9px !important;
    }

    .metric-card{
        min-height:148px !important;
        padding:11px 8px !important;
        border-radius:17px !important;
    }

    .big-icon{
        height:48px !important;
        margin-bottom:5px !important;
    }

    .icon-doc,
    .icon-hour,
    .icon-coin,
    .icon-wallet,
    .icon-chart,
    .icon-alarm,
    .big-icon img,
    .metric-card img,
    .big-icon svg{
        width:48px !important;
        height:48px !important;
        max-width:48px !important;
        max-height:48px !important;
    }

    .metric-title{
        font-size:13px !important;
    }

    .metric-value{
        font-size:21px !important;
    }

    .metric-note{
        font-size:10px !important;
        margin-top:8px !important;
    }
}

/* Layar sangat kecil */
@media(max-width:390px){
    .metric-card{
        min-height:140px !important;
    }

    .big-icon{
        height:44px !important;
    }

    .icon-doc,
    .icon-hour,
    .icon-coin,
    .icon-wallet,
    .icon-chart,
    .icon-alarm,
    .big-icon img,
    .metric-card img,
    .big-icon svg{
        width:44px !important;
        height:44px !important;
        max-width:44px !important;
        max-height:44px !important;
    }

    .metric-value{
        font-size:19px !important;
    }

    .metric-title{
        font-size:12.5px !important;
    }
}

/* end portal-compact-dashboard-v1 */


<style id="hero-profile-photo-style-v1">
.hero-profile-box{
    width:168px;
    min-width:168px;
    height:168px;
    border-radius:26px;
    border:1px solid rgba(230,198,118,.38);
    background:
        radial-gradient(circle at 30% 20%, rgba(255,220,120,.12), rgba(255,220,120,0) 38%),
        linear-gradient(180deg, rgba(9,20,52,.82), rgba(4,12,34,.94));
    box-shadow:
        inset 0 0 0 1px rgba(255,255,255,.04),
        0 10px 26px rgba(0,0,0,.18);
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}
.hero-profile-photo{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}
.hero-profile-fallback{
    width:88px;
    height:88px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(180deg,#f3dd9b,#caa84b);
    color:#2e2305;
    box-shadow:
        inset 0 2px 10px rgba(255,255,255,.35),
        0 10px 22px rgba(0,0,0,.22);
}
.hero-profile-fallback svg{
    width:42px;
    height:42px;
}
@media(max-width:560px){
    .hero-profile-box{
        width:118px;
        min-width:118px;
        height:118px;
        border-radius:22px;
    }
    .hero-profile-fallback{
        width:64px;
        height:64px;
    }
    .hero-profile-fallback svg{
        width:32px;
        height:32px;
    }
}
</style>

</style>

<style id="riwayat-pembayaran-dashboard-style-v1">
    /* Sembunyikan daftar bulan lama yang sebelumnya dilingkari */
    .bottom-months{
        display:none !important;
    }

    .payment-history-panel{
        margin:18px 0 0;
        padding:18px 16px;
        border-radius:26px;
        background:
            radial-gradient(circle at 0% 0%, rgba(28,184,166,.13), transparent 34%),
            linear-gradient(145deg, rgba(16,28,48,.96), rgba(8,15,30,.98));
        border:1px solid rgba(240,210,122,.25);
        box-shadow:0 20px 52px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.05);
    }

    .payment-history-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        margin-bottom:14px;
    }

    .payment-history-head h2{
        margin:0;
        color:#fff1c7;
        font-size:27px;
        line-height:1.05;
        font-weight:950;
    }

    .payment-history-head p{
        margin:6px 0 0;
        color:#9aa7b8;
        font-size:13px;
        font-weight:850;
    }

    .payment-history-head a{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:36px;
        padding:0 14px;
        border-radius:999px;
        color:#071123;
        background:linear-gradient(135deg,#fff1c7,#f0d27a);
        text-decoration:none;
        font-size:12px;
        font-weight:950;
        white-space:nowrap;
    }

    .payment-history-list{
        display:grid;
        gap:10px;
    }

    .payment-history-item{
        display:grid;
        grid-template-columns:1fr auto;
        gap:12px;
        align-items:center;
        min-height:68px;
        padding:12px 13px;
        border-radius:18px;
        background:rgba(255,255,255,.045);
        border:1px solid rgba(255,255,255,.08);
        box-shadow:inset 0 1px 0 rgba(255,255,255,.04);
    }

    .payment-history-name{
        display:block;
        color:#fff1c7;
        font-size:15px;
        font-weight:950;
    }

    .payment-history-meta{
        display:block;
        margin-top:4px;
        color:#9aa7b8;
        font-size:11px;
        line-height:1.35;
        font-weight:800;
    }

    .payment-history-right{
        text-align:right;
    }

    .payment-history-amount{
        display:block;
        color:#35d579;
        font-size:15px;
        font-weight:950;
        white-space:nowrap;
    }

    .payment-history-status{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        margin-top:6px;
        min-width:68px;
        padding:4px 8px;
        border-radius:999px;
        font-size:10px;
        font-weight:950;
        color:#bbffd1;
        background:rgba(53,213,121,.16);
        border:1px solid rgba(53,213,121,.30);
    }

    .payment-history-status.early{
        color:#bdd8ff;
        background:rgba(76,145,255,.16);
        border-color:rgba(76,145,255,.32);
    }

    .payment-history-empty{
        padding:16px 14px;
        border-radius:18px;
        color:#9aa7b8;
        background:rgba(255,255,255,.04);
        border:1px solid rgba(255,255,255,.08);
        font-size:13px;
        font-weight:850;
        text-align:center;
    }

    @media(max-width:560px){
        .payment-history-panel{
            margin-top:16px;
            padding:16px 14px;
            border-radius:22px;
        }

        .payment-history-head h2{
            font-size:23px;
        }

        .payment-history-item{
            grid-template-columns:1fr;
        }

        .payment-history-right{
            text-align:left;
        }
    }
</style>

@endpush

<div class="ref-wrap">
    <section class="ref-panel hero">
<div><div class="hero-kicker">Dashboard Collector</div><div class="hero-hi">Selamat bekerja,</div><div class="hero-name">{{ $collectorName }}</div><div class="hero-line"></div>
</div>
        {{-- hero-profile-photo-v1-start --}}
@php $heroPhoto = auth()->user()->profile_photo_path ?? null; @endphp
<div class="hero-profile-box">
    @if($heroPhoto)
        <img src="{{ $photoUrl($heroPhoto) }}" alt="Foto Profile" class="hero-profile-photo">
    @else
        <div class="hero-profile-fallback" aria-label="Default Foto Profile">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21a8 8 0 0 0-16 0"></path>
                <circle cx="12" cy="8" r="4"></circle>
            </svg>
        </div>
    @endif
</div>
{{-- hero-profile-photo-v1-end --}}
    </section>

    <section class="metric-grid">
        <a class="ref-panel metric-card" href="#"><div class="big-icon"><img class="asset-icon doc" src="{{ $iv('doc') }}" alt="Siap Tagih"></div><div><div class="metric-title">Siap Tagih</div><div class="metric-value gold-text">{{ number_format($siapTagih,0,',','.') }}</div><div class="metric-note">Buka daftar tagihan <span>›</span></div></div></a>
        <a class="ref-panel metric-card" href="#"><div class="big-icon"><img class="asset-icon hour" src="{{ $iv('hour') }}" alt="Pendapatan Tertunda"></div><div><div class="metric-title">Pendapatan Tertunda</div><div class="metric-value purple-text">{{ $money($pendapatanTertunda) }}</div><div class="metric-note">Belum bayar + nunggak <span>›</span></div></div></a>
        <a class="ref-panel metric-card" href="#"><div class="big-icon"><img class="asset-icon coin" src="{{ $iv('coin') }}" alt="Pemasukan"></div><div><div class="metric-title">Pemasukan Bulan Ini</div><div class="metric-value green-text">{{ $money($pemasukanBulanIni) }}</div><div class="metric-note">Riwayat pembayaran <span>›</span></div></div></a>
        <a class="ref-panel metric-card" href="#"><div class="big-icon"><img class="asset-icon wallet" src="{{ $iv('wallet') }}" alt="Pengeluaran"></div><div><div class="metric-title">Pengeluaran</div><div class="metric-value red-text">{{ $money($pengeluaranBulanIni) }}</div><div class="metric-note">Buka pengeluaran <span>›</span></div></div></a>
        <a class="ref-panel metric-card" href="#"><div class="big-icon"><img class="asset-icon chart" src="{{ $iv('chart') }}" alt="Profit"></div><div><div class="metric-title">Profit</div><div class="metric-value green-text">{{ $money($profitBulanIni) }}</div><div class="metric-note">Lihat penerimaan <span>›</span></div></div></a>
        <a class="ref-panel metric-card" href="#"><div class="big-icon"><img class="asset-icon alarm" src="{{ $iv('alarm') }}" alt="Nunggak"></div><div><div class="metric-title">Nunggak</div><div class="metric-value red-text">{{ number_format($nunggak,0,',','.') }}</div><div class="metric-note">Buka tagihan nunggak <span>›</span></div></div></a>
    </section>

    <section class="ref-panel recap">
        <div class="recap-head"><div class="recap-title">Rekap Tahunan {{ $yearLabel }}</div><div class="recap-badge"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="3"></rect><path d="M8 2v4"></path><path d="M16 2v4"></path><path d="M3 9h18"></path></svg>{{ $yearLabel }}</div></div>
        <div class="best-row"><div><small>Bulan Tertinggi</small><strong>{{ $bestMonthLabel }} · {{ $money($bestMonthIncome) }}</strong></div><div class="mini-crown"><img src="{{ $iv('crown') }}" alt="Crown"></div></div>
        <div class="chart-box"><div class="axis-labels"><span>2M</span><span>1.5M</span><span>1M</span><span>500K</span><span>0</span></div><svg class="chart-svg" viewBox="0 0 620 150" preserveAspectRatio="none"><defs><linearGradient id="areaGold" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#f0d27a" stop-opacity=".55"/><stop offset="1" stop-color="#f0d27a" stop-opacity="0"/></linearGradient><filter id="goldGlow"><feGaussianBlur stdDeviation="3" result="blur"/><feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge></filter></defs><g transform="translate(58 5)"><path d="M0 125H548" stroke="rgba(240,210,122,.22)" stroke-width="1"/><path d="M0 94H548" stroke="rgba(240,210,122,.14)" stroke-width="1"/><path d="M0 63H548" stroke="rgba(240,210,122,.14)" stroke-width="1"/><path d="M0 32H548" stroke="rgba(240,210,122,.14)" stroke-width="1"/><path d="M0 122 L110 108 L220 96 L330 82 L440 63 L548 34 L548 125 L0 125 Z" fill="url(#areaGold)"/><path d="M0 122 L110 108 L220 96 L330 82 L440 63 L548 34" fill="none" stroke="#f0d27a" stroke-width="4" stroke-linecap="round" filter="url(#goldGlow)"/><circle cx="0" cy="122" r="5" fill="#fff0bb"/><circle cx="110" cy="108" r="5" fill="#fff0bb"/><circle cx="220" cy="96" r="5" fill="#fff0bb"/><circle cx="330" cy="82" r="5" fill="#fff0bb"/><circle cx="440" cy="63" r="5" fill="#fff0bb"/><circle cx="548" cy="34" r="6" fill="#fff0bb"/><rect x="454" y="12" width="84" height="26" rx="13" fill="rgba(240,210,122,.14)" stroke="rgba(240,210,122,.40)"/><text x="496" y="30" text-anchor="middle" fill="#fff0bb" font-size="13" font-weight="900">{{ $money($bestMonthIncome) }}</text></g></svg><div class="chart-months">@foreach($chartMonths as $m)<span>{{ $m['label'] }}</span>@endforeach</div></div>
        <div class="bottom-months">@foreach($months->take(2) as $m)<div class="mini-month"><b>{{ $m['label'] }}</b><div class="mini-lines"><div class="in"><span>Pemasukan</span><strong>{{ $money($m['income']) }}</strong></div><div class="out"><span>Pengeluaran</span><strong>{{ $money($m['expense']) }}</strong></div><div class="growth">↑<small>dari bulan lalu</small></div></div></div>@endforeach</div>
    </section>
</div>

{{-- riwayat-pembayaran-dashboard-v1-start --}}
@php
    $phMoney = fn($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
    $paymentHistory = collect();

    if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
        $amountCol = null;
        foreach (['amount', 'paid_amount', 'payment_amount', 'nominal', 'total'] as $col) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('payments', $col)) {
                $amountCol = $col;
                break;
            }
        }

        $dateCol = null;
        foreach (['paid_at', 'payment_date', 'paid_date', 'date', 'tanggal', 'created_at'] as $col) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('payments', $col)) {
                $dateCol = $col;
                break;
            }
        }

        if ($amountCol) {
            $q = \Illuminate\Support\Facades\DB::table('payments as p');

            if (\Illuminate\Support\Facades\Schema::hasColumn('payments', 'invoice_id') && \Illuminate\Support\Facades\Schema::hasTable('invoices')) {
                $q->leftJoin('invoices as i', 'i.id', '=', 'p.invoice_id');
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('payments', 'customer_id') && \Illuminate\Support\Facades\Schema::hasTable('customers')) {
                $q->leftJoin('customers as c', 'c.id', '=', 'p.customer_id');
            } elseif (\Illuminate\Support\Facades\Schema::hasTable('invoices') && \Illuminate\Support\Facades\Schema::hasTable('customers')) {
                $q->leftJoin('customers as c', 'c.id', '=', 'i.customer_id');
            }

            $q->selectRaw('p.'.$amountCol.' as paid_amount');

            if ($dateCol) {
                $q->selectRaw('p.'.$dateCol.' as paid_date');
            } else {
                $q->selectRaw('p.created_at as paid_date');
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('customers')) {
                $q->addSelect(\Illuminate\Support\Facades\DB::raw('COALESCE(c.name, "-") as customer_name'));
                $q->addSelect(\Illuminate\Support\Facades\DB::raw('COALESCE(c.phone, "-") as customer_phone'));
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('invoices')) {
                $q->addSelect(\Illuminate\Support\Facades\DB::raw('COALESCE(i.invoice_number, "-") as invoice_number'));
                $q->addSelect(\Illuminate\Support\Facades\DB::raw('COALESCE(i.period, "-") as period'));
                $q->addSelect(\Illuminate\Support\Facades\DB::raw('COALESCE(i.status, "-") as invoice_status'));
            }

            if ($dateCol) {
                $q->orderByDesc('p.'.$dateCol);
            } else {
                $q->orderByDesc('p.id');
            }

            $paymentHistory = $q->limit(6)->get();
        }
    }

    if ($paymentHistory->isEmpty() && \Illuminate\Support\Facades\Schema::hasTable('invoices')) {
        $paymentHistory = \Illuminate\Support\Facades\DB::table('invoices as i')
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->select(
                'i.amount as paid_amount',
                'i.paid_at as paid_date',
                'i.invoice_number',
                'i.period',
                'i.status as invoice_status',
                'c.name as customer_name',
                'c.phone as customer_phone'
            )
            ->whereIn('i.status', ['Lunas', 'Bayar Awal'])
            ->whereNotNull('i.paid_at')
            ->orderByDesc('i.paid_at')
            ->limit(6)
            ->get();
    }
@endphp

<section class="payment-history-panel">
    <div class="payment-history-head">
        <div>
            <h2>Riwayat Pembayaran</h2>
            <p>Pembayaran terbaru dari kasir dan portal percobaan.</p>
        </div>
        <a href="{{ url('/kasir/riwayat') }}">Lihat Semua</a>
    </div>

    <div class="payment-history-list">
        @forelse($paymentHistory as $pay)
            @php
                $status = $pay->invoice_status ?? '-';
                $statusClass = $status === 'Bayar Awal' ? 'early' : '';
                try {
                    $dateText = \Illuminate\Support\Carbon::parse($pay->paid_date)->format('d/m/Y H:i');
                } catch (\Throwable $e) {
                    $dateText = $pay->paid_date ?? '-';
                }
            @endphp

            <div class="payment-history-item">
                <div>
                    <span class="payment-history-name">{{ $pay->customer_name ?? '-' }}</span>
                    <span class="payment-history-meta">
                        {{ $pay->invoice_number ?? '-' }} · Periode {{ $pay->period ?? '-' }} · {{ $dateText }}
                    </span>
                </div>

                <div class="payment-history-right">
                    <span class="payment-history-amount">{{ $phMoney($pay->paid_amount ?? 0) }}</span>
                    <span class="payment-history-status {{ $statusClass }}">{{ $status }}</span>
                </div>
            </div>
        @empty
            <div class="payment-history-empty">Belum ada riwayat pembayaran.</div>
        @endforelse
    </div>
</section>
{{-- riwayat-pembayaran-dashboard-v1-end --}}

@endsection
