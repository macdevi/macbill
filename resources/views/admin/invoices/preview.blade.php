@extends('layouts.neo')
@section('title','Tagihan Manual')
@section('content')
@php
    use Carbon\Carbon;
    use App\Models\Customer;
    use App\Models\Invoice;

    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $period = request('period', now()->format('Y-m'));
    $invoiceBase = request()->is('collector/*') ? '/collector/invoices' : '/admin/invoices';

    try {
        $baseDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
    } catch (\Throwable $e) {
        $period = now()->format('Y-m');
        $baseDate = now()->startOfMonth();
    }

    $lastDay = $baseDate->copy()->endOfMonth()->day;

    $customers = Customer::with('package')
        ->orderBy('id')
        ->get();

    $rows = $customers->map(function ($customer) use ($period, $baseDate, $lastDay) {
        $billingDay = (int) ($customer->billing_day ?: 1);
        $billingDay = max(1, min($billingDay, $lastDay));

        $dueDate = $baseDate->copy()->day($billingDay)->format('Y-m-d');

        $existingInvoice = Invoice::where('customer_id', $customer->id)
            ->where('period', $period)
            ->first();

        $ready = !$existingInvoice
            && $customer->status === 'active'
            && (float) $customer->monthly_price > 0;

        $reason = 'Siap Dibuat';

        if ($existingInvoice) {
            $reason = 'Sudah Ada - ' . $existingInvoice->status;
        } elseif ($customer->status !== 'active') {
            $reason = 'Nonaktif';
        } elseif ((float) $customer->monthly_price <= 0) {
            $reason = 'Harga Kosong';
        }

        return [
            'customer' => $customer,
            'due_date' => $dueDate,
            'existing_invoice' => $existingInvoice,
            'ready' => $ready,
            'reason' => $reason,
        ];
    });

    $readyCount = $rows->where('ready', true)->count();
    $existingCount = $rows->filter(fn($r) => $r['existing_invoice'])->count();

    $i = function ($name) {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>',
            'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
            'file' => '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>',
            'check' => '<svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>',
            'box' => '<svg viewBox="0 0 24 24"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>',
            'money' => '<svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>',
            'calendar' => '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.preview-note{
    background:#eff6ff;
    border:1px solid #b2ddff;
    color:#175cd3;
    border-radius:18px;
    padding:11px 13px;
    font-size:13px;
    line-height:1.45;
    margin-bottom:10px;
}

.preview-check{
    width:18px;
    height:18px;
    accent-color:#2563eb;
}

.preview-submitbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-top:10px;
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:18px;
    padding:10px;
    box-shadow:0 8px 22px rgba(16,24,40,.055);
}

.preview-submitbar .small{
    font-size:12px;
    color:#667085;
}

@media(max-width:760px){
    .preview-note{
        font-size:12px;
        padding:10px;
        border-radius:15px;
    }

    .preview-submitbar{
        align-items:stretch;
        flex-direction:column;
        padding:9px;
        border-radius:16px;
    }

    .preview-submitbar .btn{
        width:100%;
        justify-content:center;
    }
}
</style>

<div class="pagehead">
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif


<style id="tagihan-manual-livefind-css">
.tm-livefind-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:16px;
    padding:9px 10px;
    margin:0 0 8px;
    box-shadow:0 6px 16px rgba(16,24,40,.045);
}
.tm-livefind-card label{
    display:block;
    color:#667085;
    font-size:11px;
    font-weight:900;
    margin:0 0 6px;
}
.tm-livefind-row{
    display:grid;
    grid-template-columns:1fr 72px;
    align-items:center;
    gap:7px;
}
.tm-livefind-input{
    width:100%;
    min-height:42px;
    border:1px solid #d7deea;
    border-radius:13px;
    padding:0 11px;
    color:#101828;
    font-size:14px;
    font-weight:800;
    outline:none;
    background:#fff;
}
.tm-livefind-input::placeholder{
    color:#7b8494;
    font-weight:800;
}
.tm-livefind-input:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.09);
}
.tm-livefind-clear{
    min-height:42px;
    border:0;
    border-radius:13px;
    padding:0 10px;
    background:#eef2ff;
    color:#1d4ed8;
    font-size:13px;
    font-weight:950;
    cursor:pointer;
}
.tm-livefind-count{
    margin-top:5px;
    color:#667085;
    font-size:11px;
    font-weight:800;
}
@media(max-width:520px){
    .tm-livefind-card{
        padding:8px 9px;
        border-radius:15px;
        margin-bottom:7px;
    }
    .tm-livefind-row{
        grid-template-columns:1fr 64px;
        gap:6px;
    }
    .tm-livefind-input{
        min-height:39px;
        border-radius:12px;
        font-size:13px;
        padding:0 10px;
    }
    .tm-livefind-clear{
        min-height:39px;
        border-radius:12px;
        font-size:12px;
        padding:0 8px;
    }
    .tm-livefind-count{
        font-size:10.5px;
    }
}
</style>


<div class="tm-livefind-card">
    <label for="tagihanManualLiveFind">LiveFind Tagihan Manual</label>
    <div class="tm-livefind-row">
        <input id="tagihanManualLiveFind" class="tm-livefind-input" type="search" autocomplete="off" placeholder="Cari pelanggan, HP, paket, ODP...">
        <button id="tagihanManualLiveFindClear" class="tm-livefind-clear" type="button">Reset</button>
    </div>
    <div id="tagihanManualLiveFindCount" class="tm-livefind-count">Ketik untuk mencari data pada tabel.</div>
</div>

<form id="tagihanManualPeriodForm" class="neo-search tm-period-auto-form" method="GET" action="{{ url($invoiceBase.'/preview') }}">
    <input id="tagihanManualPeriodInput" class="input" type="month" name="period" value="{{ $period }}">
</form>

<form method="POST" action="{{ url($invoiceBase.'/generate-selected') }}" id="previewGenerateForm" data-force-generate-form="1">
    @csrf
    <input type="hidden" name="period" value="{{ $period }}">

    <div class="neo-xls">
        <div class="neo-xls-info">
            <span>
                Periode: <b>{{ $period }}</b> ·
                Siap: <b>{{ $readyCount }}</b> ·
                Sudah Ada: <b>{{ $existingCount }}</b>
            </span>
            <span>Centang lalu generate manual</span>
        </div>

        <div class="neo-xls-scroll">
            <table class="neo-xls-table">
                <thead>
                    <tr>
                        <th class="sticky-left" style="min-width:115px">
                            <label style="display:flex;align-items:center;gap:6px;justify-content:center;white-space:nowrap">
                                <input class="preview-check" type="checkbox" id="previewSelectAll">
                                <span>Semua</span>
                            </label>
                        </th>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>HP</th>
                        <th>Paket</th>
                        <th>ODP / Port</th>
                        <th>Nominal</th>
                        <th>Tempo</th>
                        <th>Tgl</th>
                        <th>Tagihan</th>
                        <th>Status Cek</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($rows as $row)
                        @php
                            $customer = $row['customer'];
                            $invoice = $row['existing_invoice'];
                            $ready = $row['ready'];
                            $reason = $row['reason'];

                            $badgeClass = $ready ? 'green' : ($invoice ? 'yellow' : 'red');
                        @endphp

                        <tr>
                            <td class="sticky-left" style="text-align:center">
                                @if($ready)
                                    <input class="preview-check js-customer-check" type="checkbox" name="customer_ids[]" value="{{ $customer->id }}">
                                @else
                                    <input class="preview-check" type="checkbox" disabled>
                                @endif
                            </td>

                            <td class="neo-id">#{{ $customer->id }}</td>

                            <td class="neo-strong">{{ $customer->name }}</td>

                            <td>{{ $customer->phone ?: '-' }}</td>

                            <td>
                                {{ $customer->package?->name ?: '-' }}
                                @if($customer->package?->speed)
                                    · {{ $customer->package?->speed }}
                                @endif
                            </td>

                            <td>
                                {{ $customer->odp ?: '-' }}
                                {{ $customer->port_number ? 'Port '.$customer->port_number : '' }}
                            </td>

                            <td class="neo-money">{{ $money($customer->monthly_price) }}</td>

                            <td>{{ $row['due_date'] }}</td>

                            <td>{{ $customer->billing_day ?: '-' }}</td>

                            <td>
                                {{ $invoice?->invoice_number ?: '-' }}
                            </td>

                            <td>
                                <span class="badge {{ $badgeClass }}">{{ $reason }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">Belum ada pelanggan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="preview-submitbar">
        <div class="small">
            <b id="previewSelectedCount">0 pelanggan dicentang.</b><br>
            Yang dibuat hanya pelanggan yang dicentang dan belum punya tagihan di periode {{ $period }}.
        </div>

        <button class="btn light" type="button" id="previewClearAll">Kosongkan</button>

        <button class="btn" type="submit" @disabled($readyCount <= 0)>
            {!! $i('check') !!}Buat Tagihan Terpilih
        </button>
    </div>
</form>


<style id="tagihan-manual-submitbar-button-only">
@media(max-width:760px){
    .preview-submitbar{
        grid-template-columns:1fr !important;
        padding:7px !important;
        min-height:auto !important;
    }

    .preview-submitbar .small{
        display:none !important;
    }

    .preview-submitbar .btn.light{
        display:none !important;
    }

    .preview-submitbar .btn:not(.light){
        width:100% !important;
        min-height:42px !important;
        height:42px !important;
        border-radius:13px !important;
        font-size:13px !important;
        font-weight:950 !important;
        justify-content:center !important;
    }
}
</style>


<style id="tagihan-manual-small-button-freeze-first-col">
@media(max-width:760px){
    /* Freeze kolom pertama: checkbox / Semua */
    .neo-xls-table th:nth-child(1),
    .neo-xls-table td:nth-child(1){
        position:sticky !important;
        left:0 !important;
        z-index:20 !important;
        background:#fff !important;
        box-shadow:5px 0 10px rgba(15,23,42,.06) !important;
    }

    .neo-xls-table th:nth-child(1){
        z-index:35 !important;
        background:#f8fafc !important;
    }

    .neo-xls-table th:nth-child(1) label{
        display:flex !important;
        align-items:center !important;
        justify-content:center !important;
        gap:4px !important;
        white-space:nowrap !important;
    }

    .neo-xls-table th:nth-child(1) span{
        display:inline !important;
        font-size:8.5px !important;
        font-weight:950 !important;
    }

    .neo-xls-table td:nth-child(1){
        text-align:center !important;
    }

    /* Bar bawah dibuat kecil dan tidak menutupi layar */
    .preview-submitbar{
        left:auto !important;
        right:14px !important;
        bottom:82px !important;
        width:auto !important;
        max-width:230px !important;
        min-width:190px !important;
        padding:6px !important;
        border-radius:14px !important;
        grid-template-columns:1fr !important;
        background:rgba(255,255,255,.97) !important;
        box-shadow:0 10px 24px rgba(15,23,42,.16) !important;
    }

    .preview-submitbar .small{
        display:none !important;
    }

    .preview-submitbar .btn.light{
        display:none !important;
    }

    .preview-submitbar .btn:not(.light){
        width:100% !important;
        min-height:34px !important;
        height:34px !important;
        border-radius:11px !important;
        padding:0 12px !important;
        font-size:11.5px !important;
        font-weight:950 !important;
        line-height:1 !important;
        white-space:nowrap !important;
        justify-content:center !important;
    }
}
</style>


<style id="tagihan-manual-dense-table-v2">
@media(max-width:760px){
    /* Area tabel dibuat lebih tinggi agar lebih banyak baris terlihat */
    .neo-xls-scroll{
        max-height:calc(100dvh - 295px) !important;
        padding-bottom:92px !important;
    }

    .neo-xls-info{
        padding:6px 8px !important;
        font-size:9.8px !important;
        line-height:1.15 !important;
        gap:4px !important;
    }

    /* Paksa tabel menjadi sangat padat */
    .neo-xls-table{
        width:820px !important;
        min-width:820px !important;
        table-layout:fixed !important;
        border-collapse:collapse !important;
    }

    .neo-xls-table tr,
    .neo-xls-table tbody tr{
        height:30px !important;
        min-height:30px !important;
        max-height:30px !important;
    }

    .neo-xls-table th,
    .neo-xls-table td{
        height:30px !important;
        min-height:30px !important;
        max-height:30px !important;
        padding:2px 4px !important;
        font-size:9.7px !important;
        line-height:1.05 !important;
        vertical-align:middle !important;
        white-space:nowrap !important;
        overflow:hidden !important;
        text-overflow:ellipsis !important;
    }

    .neo-xls-table th{
        height:27px !important;
        min-height:27px !important;
        max-height:27px !important;
        padding:2px 4px !important;
        font-size:8px !important;
        line-height:1 !important;
        letter-spacing:.02em !important;
    }

    .neo-xls-table .neo-id{
        font-size:9.2px !important;
        font-weight:850 !important;
    }

    .neo-xls-table .neo-strong{
        font-size:10px !important;
        font-weight:900 !important;
    }

    .neo-xls-table .neo-money{
        font-size:10px !important;
        font-weight:900 !important;
    }

    .preview-check{
        width:14px !important;
        height:14px !important;
        min-width:14px !important;
        min-height:14px !important;
        margin:0 !important;
    }

    .neo-xls-table th:nth-child(1),
    .neo-xls-table td:nth-child(1){
        width:52px !important;
        min-width:52px !important;
        max-width:52px !important;
    }

    .neo-xls-table th:nth-child(1) label{
        gap:3px !important;
        font-size:7.8px !important;
        line-height:1 !important;
    }

    .neo-xls-table th:nth-child(1) span{
        font-size:7.8px !important;
    }

    .neo-xls-table th:nth-child(2),
    .neo-xls-table td:nth-child(2){
        width:40px !important;
        min-width:40px !important;
        max-width:40px !important;
    }

    .neo-xls-table th:nth-child(3),
    .neo-xls-table td:nth-child(3){
        width:125px !important;
        min-width:125px !important;
        max-width:125px !important;
    }

    .neo-xls-table th:nth-child(4),
    .neo-xls-table td:nth-child(4){
        width:100px !important;
        min-width:100px !important;
        max-width:100px !important;
    }

    .neo-xls-table th:nth-child(5),
    .neo-xls-table td:nth-child(5){
        width:82px !important;
        min-width:82px !important;
        max-width:82px !important;
    }

    .neo-xls-table th:nth-child(6),
    .neo-xls-table td:nth-child(6){
        width:82px !important;
        min-width:82px !important;
        max-width:82px !important;
    }

    .neo-xls-table th:nth-child(7),
    .neo-xls-table td:nth-child(7){
        width:84px !important;
        min-width:84px !important;
        max-width:84px !important;
    }

    .neo-xls-table th:nth-child(8),
    .neo-xls-table td:nth-child(8){
        width:76px !important;
        min-width:76px !important;
        max-width:76px !important;
    }

    .neo-xls-table th:nth-child(9),
    .neo-xls-table td:nth-child(9){
        width:38px !important;
        min-width:38px !important;
        max-width:38px !important;
    }

    .neo-xls-table th:nth-child(10),
    .neo-xls-table td:nth-child(10){
        width:72px !important;
        min-width:72px !important;
        max-width:72px !important;
    }

    .neo-xls-table th:nth-child(11),
    .neo-xls-table td:nth-child(11){
        width:100px !important;
        min-width:100px !important;
        max-width:100px !important;
    }

    .neo-xls-table .badge{
        min-height:16px !important;
        height:16px !important;
        padding:1px 5px !important;
        font-size:7.8px !important;
        line-height:14px !important;
        max-width:92px !important;
    }

    /* Tombol bawah tetap kecil */
    .preview-submitbar{
        max-width:205px !important;
        min-width:170px !important;
        padding:5px !important;
        border-radius:12px !important;
        bottom:82px !important;
    }

    .preview-submitbar .btn:not(.light){
        min-height:31px !important;
        height:31px !important;
        border-radius:10px !important;
        font-size:10.5px !important;
        padding:0 9px !important;
    }
}
</style>



<style id="tagihan-manual-livefind-hard-hide-v3">
/* Paksa baris hasil LiveFind tersembunyi meskipun table CSS memakai !important */
#previewGenerateForm table.neo-xls-table tbody tr.tm-livefind-hidden,
#previewGenerateForm .neo-xls-table tbody tr.tm-livefind-hidden,
html body #previewGenerateForm .neo-xls-table tbody tr.tm-livefind-hidden{
    display:none !important;
    visibility:collapse !important;
    height:0 !important;
    min-height:0 !important;
    max-height:0 !important;
    overflow:hidden !important;
}
</style>


<script id="tagihan-manual-livefind-hardfix-js">
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('tagihanManualLiveFind');
    const clearBtn = document.getElementById('tagihanManualLiveFindClear');
    const countBox = document.getElementById('tagihanManualLiveFindCount');
    const selectAll = document.getElementById('previewSelectAll');

    if (!input) return;

    const rows = Array.from(document.querySelectorAll('#previewGenerateForm table tbody tr'))
        .filter(function (row) {
            return !row.querySelector('[colspan]');
        });

    function norm(value) {
        return (value || '')
            .toString()
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .trim();
    }

    function updateSelectedText() {
        const selectedCount = document.getElementById('previewSelectedCount');
        if (!selectedCount) return;

        const checked = document.querySelectorAll('.js-customer-check:checked').length;
        selectedCount.textContent = checked + ' pelanggan dicentang.';
    }

    function updateActionBar() {
        const bar = document.querySelector('.preview-submitbar');
        if (!bar) return;

        const checked = document.querySelectorAll('.js-customer-check:checked').length;
        if (checked > 0) {
            bar.classList.add('tm-active');
        } else {
            bar.classList.remove('tm-active');
        }
    }

    function hideRow(row) {
        row.classList.add('tm-livefind-hidden');
        row.style.setProperty('display', 'none', 'important');

        const cb = row.querySelector('.js-customer-check');
        if (cb) cb.checked = false;
    }

    function showRow(row) {
        row.classList.remove('tm-livefind-hidden');
        row.style.removeProperty('display');
        row.style.removeProperty('visibility');
        row.style.removeProperty('height');
        row.style.removeProperty('min-height');
        row.style.removeProperty('max-height');
    }

    function runLiveFind() {
        const keyword = norm(input.value);
        let shown = 0;

        rows.forEach(function (row) {
            const text = norm(row.innerText || row.textContent);
            const match = !keyword || text.includes(keyword);

            if (match) {
                showRow(row);
                shown++;
            } else {
                hideRow(row);
            }
        });

        if (selectAll) selectAll.checked = false;

        if (countBox) {
            countBox.textContent = keyword
                ? shown + ' data cocok dari ' + rows.length + ' data halaman ini'
                : rows.length + ' data tampil pada halaman ini';
        }

        updateSelectedText();
        updateActionBar();
    }

    input.addEventListener('input', runLiveFind);

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            input.focus();
            runLiveFind();
        });
    }

    runLiveFind();
});
</script>


<style id="tagihan-manual-auto-period-style">
.tm-period-auto-form{
    grid-template-columns:1fr !important;
}

.tm-period-auto-form .input[type="month"]{
    width:100% !important;
}

@media(max-width:760px){
    .tm-period-auto-form{
        padding:8px !important;
        margin-bottom:8px !important;
        border-radius:15px !important;
    }

    .tm-period-auto-form .input[type="month"]{
        min-height:40px !important;
        height:40px !important;
        border-radius:12px !important;
        font-size:13px !important;
        padding:0 10px !important;
    }
}
</style>


<script id="tagihan-manual-auto-period-js">
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('tagihanManualPeriodForm');
    const input = document.getElementById('tagihanManualPeriodInput');

    if (!form || !input) return;

    input.addEventListener('change', function () {
        if (input.value) {
            form.submit();
        }
    });
});
</script>


<style id="tagihan-manual-header-direct-clean">
@media(max-width:760px){
    /* Hilangkan semua ikon/SVG di header tabel agar tidak jadi spot hitam */
    .neo-xls-table thead th svg,
    .neo-xls-table thead th .neo-mini-ico,
    .neo-xls-table th svg,
    .neo-xls-table th .neo-mini-ico{
        display:none !important;
        width:0 !important;
        height:0 !important;
        min-width:0 !important;
        min-height:0 !important;
        max-width:0 !important;
        max-height:0 !important;
        opacity:0 !important;
        visibility:hidden !important;
    }

    /* Header tabel dibuat lebih pendek */
    .neo-xls-table thead,
    .neo-xls-table thead tr{
        height:23px !important;
        min-height:23px !important;
        max-height:23px !important;
    }

    .neo-xls-table thead th,
    .neo-xls-table th{
        height:23px !important;
        min-height:23px !important;
        max-height:23px !important;
        padding:1px 4px !important;
        font-size:7.8px !important;
        line-height:1 !important;
        vertical-align:middle !important;
        white-space:nowrap !important;
        overflow:hidden !important;
        text-overflow:ellipsis !important;
    }

    .neo-xls-table thead th label{
        height:20px !important;
        min-height:20px !important;
        max-height:20px !important;
        line-height:1 !important;
        gap:3px !important;
    }

    .neo-xls-table thead th label span{
        font-size:7.5px !important;
        line-height:1 !important;
    }

    .neo-xls-table thead .preview-check{
        width:13px !important;
        height:13px !important;
        min-width:13px !important;
        min-height:13px !important;
    }

    /* Body tetap padat */
    .neo-xls-table tbody tr{
        height:29px !important;
        min-height:29px !important;
        max-height:29px !important;
    }

    .neo-xls-table tbody td{
        height:29px !important;
        min-height:29px !important;
        max-height:29px !important;
        padding:2px 4px !important;
        font-size:9.5px !important;
        line-height:1.05 !important;
    }

    .neo-xls-scroll{
        max-height:calc(100dvh - 285px) !important;
    }
}
</style>



<style id="tagihan-manual-row-highlight-only">
#previewGenerateForm .neo-xls-table tbody tr.tm-row-highlight td{
    background:#1d4ed8 !important;
    color:#ffffff !important;
}

#previewGenerateForm .neo-xls-table tbody tr.tm-row-highlight td:first-child{
    background:#1e40af !important;
    color:#ffffff !important;
}

#previewGenerateForm .neo-xls-table tbody tr.tm-row-highlight td.sticky-left,
#previewGenerateForm .neo-xls-table tbody tr.tm-row-highlight .sticky-left{
    background:#1e40af !important;
    color:#ffffff !important;
}

#previewGenerateForm .neo-xls-table tbody tr.tm-row-highlight .neo-id,
#previewGenerateForm .neo-xls-table tbody tr.tm-row-highlight .neo-strong,
#previewGenerateForm .neo-xls-table tbody tr.tm-row-highlight .neo-money{
    color:#ffffff !important;
}

#previewGenerateForm .neo-xls-table tbody tr.tm-row-highlight .badge{
    background:#ffffff !important;
    color:#1d4ed8 !important;
    border-color:#ffffff !important;
    font-weight:950 !important;
}

#previewGenerateForm .neo-xls-table tbody tr{
    cursor:pointer !important;
}

#previewGenerateForm .neo-xls-table tbody tr.tm-row-highlight{
    outline:2px solid #facc15 !important;
    outline-offset:-2px !important;
}

@media(max-width:760px){
    #previewGenerateForm .neo-xls-table tbody tr.tm-row-highlight td{
        background:#1d4ed8 !important;
        color:#ffffff !important;
    }

    #previewGenerateForm .neo-xls-table tbody tr.tm-row-highlight td:first-child{
        background:#1e40af !important;
        color:#ffffff !important;
    }
}
</style>


<script id="tagihan-manual-row-highlight-only-js">
document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('#previewGenerateForm .neo-xls-table');
    if (!table) return;

    function clearHighlight() {
        table.querySelectorAll('tbody tr.tm-row-highlight').forEach(function (row) {
            row.classList.remove('tm-row-highlight');
        });
    }

    table.addEventListener('click', function (e) {
        // Klik checkbox tetap hanya untuk checkbox, bukan highlight.
        if (
            e.target.matches('input, button, a, select, textarea') ||
            e.target.closest('button, a, label')
        ) {
            return;
        }

        const row = e.target.closest('tbody tr');
        if (!row || row.querySelector('[colspan]')) return;

        const alreadyActive = row.classList.contains('tm-row-highlight');

        clearHighlight();

        if (!alreadyActive) {
            row.classList.add('tm-row-highlight');
        }
    });

    // Setelah LiveFind berjalan, jangan hapus pilihan highlight kalau barisnya masih tampil.
    const liveFind = document.getElementById('tagihanManualLiveFind');
    if (liveFind) {
        liveFind.addEventListener('input', function () {
            setTimeout(function () {
                table.querySelectorAll('tbody tr.tm-row-highlight.tm-livefind-hidden').forEach(function (row) {
                    row.classList.remove('tm-row-highlight');
                });
            }, 30);
        });
    }
});
</script>


<style id="tagihan-manual-table-header-color">
#previewGenerateForm .neo-xls-table thead th{
    background:linear-gradient(135deg,#0f172a,#1d4ed8) !important;
    color:#ffffff !important;
    border-bottom:1px solid rgba(255,255,255,.18) !important;
    border-right:1px solid rgba(255,255,255,.10) !important;
    font-weight:950 !important;
}

#previewGenerateForm .neo-xls-table thead th:first-child{
    background:linear-gradient(135deg,#0b1220,#1e40af) !important;
    color:#ffffff !important;
}

#previewGenerateForm .neo-xls-table thead th.sticky-left,
#previewGenerateForm .neo-xls-table thead .sticky-left{
    background:linear-gradient(135deg,#0b1220,#1e40af) !important;
    color:#ffffff !important;
}

#previewGenerateForm .neo-xls-table thead th label,
#previewGenerateForm .neo-xls-table thead th span{
    color:#ffffff !important;
}

#previewGenerateForm .neo-xls-table thead .preview-check{
    accent-color:#ffffff !important;
}

#previewGenerateForm .neo-xls-table thead th:nth-child(3){
    background:linear-gradient(135deg,#1e3a8a,#2563eb) !important;
}

#previewGenerateForm .neo-xls-table thead th:nth-child(7),
#previewGenerateForm .neo-xls-table thead th:nth-child(8),
#previewGenerateForm .neo-xls-table thead th:nth-child(9),
#previewGenerateForm .neo-xls-table thead th:nth-child(10),
#previewGenerateForm .neo-xls-table thead th:nth-child(11){
    background:linear-gradient(135deg,#164e63,#0284c7) !important;
}

@media(max-width:760px){
    #previewGenerateForm .neo-xls-table thead th{
        background:linear-gradient(135deg,#0f172a,#1d4ed8) !important;
        color:#ffffff !important;
        border-right:1px solid rgba(255,255,255,.12) !important;
    }

    #previewGenerateForm .neo-xls-table thead th:first-child,
    #previewGenerateForm .neo-xls-table thead th.sticky-left{
        background:linear-gradient(135deg,#0b1220,#1e40af) !important;
        color:#ffffff !important;
    }
}
</style>


<style id="tagihan-manual-checkbox-final-css">
#previewGenerateForm .preview-check{
    pointer-events:auto !important;
    cursor:pointer !important;
    position:relative !important;
    z-index:80 !important;
    accent-color:#2563eb !important;
}

#previewGenerateForm thead .preview-check{
    accent-color:#facc15 !important;
}

#previewGenerateForm td:first-child,
#previewGenerateForm th:first-child{
    pointer-events:auto !important;
}

@media(max-width:760px){
    #previewGenerateForm .preview-check{
        width:16px !important;
        height:16px !important;
        min-width:16px !important;
        min-height:16px !important;
    }

    #previewGenerateForm thead .preview-check{
        width:15px !important;
        height:15px !important;
        min-width:15px !important;
        min-height:15px !important;
    }
}
</style>


<script id="tagihan-manual-checkbox-final-js">
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('previewGenerateForm');
    if (!form) return;

    const selectAll = document.getElementById('previewSelectAll');
    const selectedCount = document.getElementById('previewSelectedCount');
    const bar = document.querySelector('.preview-submitbar');

    function visibleCustomerChecks() {
        return Array.from(form.querySelectorAll('.js-customer-check')).filter(function (cb) {
            const row = cb.closest('tr');
            return !cb.disabled && row && !row.classList.contains('tm-livefind-hidden');
        });
    }

    function allCustomerChecks() {
        return Array.from(form.querySelectorAll('.js-customer-check')).filter(function (cb) {
            return !cb.disabled;
        });
    }

    function updateState() {
        const visible = visibleCustomerChecks();
        const visibleChecked = visible.filter(function (cb) {
            return cb.checked;
        });

        const totalChecked = allCustomerChecks().filter(function (cb) {
            return cb.checked;
        }).length;

        if (selectedCount) {
            selectedCount.textContent = totalChecked + ' pelanggan dicentang.';
        }

        if (bar) {
            if (totalChecked > 0) {
                bar.classList.add('tm-active');
            } else {
                bar.classList.remove('tm-active');
            }
        }

        if (selectAll) {
            if (visible.length === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else if (visibleChecked.length === visible.length) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else if (visibleChecked.length > 0) {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            const targetState = selectAll.checked;

            visibleCustomerChecks().forEach(function (cb) {
                cb.checked = targetState;
            });

            updateState();
        });
    }

    form.addEventListener('change', function (e) {
        if (e.target.matches('.js-customer-check')) {
            updateState();
        }
    });

    // Supaya klik di area kolom checkbox tidak hanya highlight baris, tapi benar-benar toggle checkbox.
    form.addEventListener('click', function (e) {
        const firstCell = e.target.closest('tbody td:first-child');
        if (!firstCell) return;

        const cb = firstCell.querySelector('.js-customer-check');
        if (!cb || cb.disabled) return;

        if (e.target !== cb) {
            e.preventDefault();
            cb.checked = !cb.checked;
            cb.dispatchEvent(new Event('change', { bubbles:true }));
        }
    });

    const liveFind = document.getElementById('tagihanManualLiveFind');
    if (liveFind) {
        liveFind.addEventListener('input', function () {
            setTimeout(updateState, 40);
        });
    }

    const clearBtn = document.getElementById('tagihanManualLiveFindClear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            setTimeout(updateState, 40);
        });
    }

    updateState();
});
</script>


<style id="tagihan-manual-floating-submitbar-final-css">
/* Default: tombol generate disembunyikan total */
#previewGenerateForm .preview-submitbar{
    display:none !important;
}

/* Saat ada checkbox terpilih: muncul floating */
#previewGenerateForm .preview-submitbar.tm-active,
#previewGenerateForm .preview-submitbar[data-active="1"]{
    display:flex !important;
    position:fixed !important;
    left:12px !important;
    right:12px !important;
    bottom:calc(78px + env(safe-area-inset-bottom)) !important;
    z-index:999999 !important;
    align-items:center !important;
    justify-content:center !important;
    gap:8px !important;
    padding:8px !important;
    margin:0 !important;
    border-radius:18px !important;
    background:rgba(255,255,255,.96) !important;
    border:1px solid #bfdbfe !important;
    box-shadow:0 18px 45px rgba(15,23,42,.24) !important;
    backdrop-filter:blur(10px) !important;
}

/* Keterangan kecil disembunyikan agar yang tampil hanya tombol */
#previewGenerateForm .preview-submitbar .small{
    display:none !important;
}

#previewGenerateForm .preview-submitbar .btn.light{
    display:none !important;
}

#previewGenerateForm .preview-submitbar .btn:not(.light){
    width:100% !important;
    min-height:44px !important;
    height:44px !important;
    border-radius:14px !important;
    justify-content:center !important;
    font-size:13px !important;
    font-weight:950 !important;
    background:linear-gradient(135deg,#1d4ed8,#2563eb) !important;
    color:#ffffff !important;
    box-shadow:0 10px 24px rgba(37,99,235,.32) !important;
}

/* Tambah ruang bawah agar tombol floating tidak menutup baris terakhir */
#previewGenerateForm .neo-xls-scroll{
    padding-bottom:95px !important;
}

@media(min-width:761px){
    #previewGenerateForm .preview-submitbar.tm-active,
    #previewGenerateForm .preview-submitbar[data-active="1"]{
        left:auto !important;
        right:24px !important;
        bottom:24px !important;
        width:360px !important;
    }
}

@media(max-width:760px){
    #previewGenerateForm .preview-submitbar.tm-active,
    #previewGenerateForm .preview-submitbar[data-active="1"]{
        left:10px !important;
        right:10px !important;
        bottom:calc(74px + env(safe-area-inset-bottom)) !important;
        padding:7px !important;
        border-radius:16px !important;
    }

    #previewGenerateForm .preview-submitbar .btn:not(.light){
        min-height:42px !important;
        height:42px !important;
        font-size:12.5px !important;
    }
}
</style>


<script id="tagihan-manual-floating-submitbar-final-js">
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('previewGenerateForm');
    if (!form) return;

    const bar = form.querySelector('.preview-submitbar');
    const selectedCount = document.getElementById('previewSelectedCount');

    if (!bar) return;

    function updateFloatingSubmitbar() {
        const checked = form.querySelectorAll('.js-customer-check:checked').length;

        if (selectedCount) {
            selectedCount.textContent = checked + ' pelanggan dicentang.';
        }

        if (checked > 0) {
            bar.classList.add('tm-active');
            bar.setAttribute('data-active', '1');
            bar.style.setProperty('display', 'flex', 'important');
        } else {
            bar.classList.remove('tm-active');
            bar.removeAttribute('data-active');
            bar.style.setProperty('display', 'none', 'important');
        }
    }

    form.addEventListener('change', function (e) {
        if (
            e.target.matches('.js-customer-check') ||
            e.target.matches('#previewSelectAll') ||
            e.target.matches('.preview-check')
        ) {
            setTimeout(updateFloatingSubmitbar, 20);
        }
    }, true);

    form.addEventListener('click', function (e) {
        if (
            e.target.matches('.js-customer-check') ||
            e.target.matches('#previewSelectAll') ||
            e.target.closest('td:first-child')
        ) {
            setTimeout(updateFloatingSubmitbar, 40);
        }
    }, true);

    const liveFind = document.getElementById('tagihanManualLiveFind');
    if (liveFind) {
        liveFind.addEventListener('input', function () {
            setTimeout(updateFloatingSubmitbar, 60);
        });
    }

    const clearBtn = document.getElementById('tagihanManualLiveFindClear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            setTimeout(updateFloatingSubmitbar, 60);
        });
    }

    updateFloatingSubmitbar();
});
</script>

@endsection


<style id="tagihan-manual-generate-modal-css">
body.tm-generate-modal-open #previewGenerateForm .preview-submitbar,
body.tm-generate-modal-open #previewGenerateForm .preview-submitbar.tm-active,
body.tm-generate-modal-open #previewGenerateForm .preview-submitbar[data-active="1"]{
    display:none !important;
    opacity:0 !important;
    visibility:hidden !important;
    pointer-events:none !important;
}

.tm-generate-modal-backdrop{
    position:fixed;
    inset:0;
    z-index:99999;
    display:none;
    align-items:flex-start;
    justify-content:center;
    padding:72px 18px 18px 18px;
    background:rgba(15,23,42,.46);
    backdrop-filter:blur(9px);
    -webkit-backdrop-filter:blur(9px);
    overflow-y:auto;
}

.tm-generate-modal-backdrop.tm-show{
    display:flex;
}

.tm-generate-modal-card{
    width:min(430px,100%);
    max-height:calc(100dvh - 96px);
    overflow:auto;
    background:#ffffff;
    border:1px solid rgba(226,232,240,.95);
    border-radius:26px;
    box-shadow:0 28px 80px rgba(15,23,42,.28);
    transform:translateY(-8px) scale(.98);
    opacity:0;
    transition:.18s ease;
}

.tm-generate-modal-backdrop.tm-show .tm-generate-modal-card{
    transform:translateY(0) scale(1);
    opacity:1;
}

.tm-generate-modal-head{
    padding:20px 20px 14px 20px;
    background:linear-gradient(135deg,#eef4ff 0%,#ffffff 55%,#f8fafc 100%);
    border-bottom:1px solid #eef2f7;
}

.tm-generate-modal-icon{
    width:48px;
    height:48px;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    box-shadow:0 14px 32px rgba(37,99,235,.28);
    margin-bottom:12px;
}

.tm-generate-modal-icon svg{
    width:25px;
    height:25px;
    stroke:currentColor;
    fill:none;
    stroke-width:2.2;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.tm-generate-modal-title{
    font-size:18px;
    line-height:1.25;
    font-weight:900;
    color:#0f172a;
    margin:0;
}

.tm-generate-modal-subtitle{
    margin-top:6px;
    font-size:13px;
    line-height:1.45;
    color:#64748b;
    font-weight:650;
}

.tm-generate-modal-body{
    padding:16px 20px 4px 20px;
}

.tm-generate-modal-grid{
    display:grid;
    gap:10px;
}

.tm-generate-modal-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    padding:12px 13px;
    border:1px solid #e5e7eb;
    border-radius:16px;
    background:#f8fafc;
}

.tm-generate-modal-label{
    font-size:12px;
    font-weight:800;
    color:#64748b;
}

.tm-generate-modal-value{
    font-size:14px;
    font-weight:900;
    color:#0f172a;
    text-align:right;
}

.tm-generate-modal-names{
    margin-top:10px;
    padding:12px 13px;
    border:1px solid #e5e7eb;
    border-radius:16px;
    background:#ffffff;
}

.tm-generate-modal-names-title{
    font-size:12px;
    font-weight:900;
    color:#64748b;
    margin-bottom:8px;
}

.tm-generate-modal-name-list{
    display:flex;
    flex-direction:column;
    gap:6px;
    max-height:128px;
    overflow:auto;
}

.tm-generate-modal-name-item{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:13px;
    font-weight:850;
    color:#0f172a;
    background:#f8fafc;
    border:1px solid #eef2f7;
    border-radius:12px;
    padding:8px 10px;
}

.tm-generate-modal-name-dot{
    width:7px;
    height:7px;
    border-radius:999px;
    background:#2563eb;
    flex:0 0 auto;
}

.tm-generate-modal-warning{
    margin-top:12px;
    padding:12px 13px;
    border-radius:16px;
    background:#fff7ed;
    border:1px solid #fed7aa;
    color:#9a3412;
    font-size:12.5px;
    font-weight:750;
    line-height:1.45;
}

.tm-generate-modal-actions{
    display:flex;
    gap:10px;
    padding:18px 20px 20px 20px;
}

.tm-generate-modal-btn{
    flex:1;
    height:46px;
    border-radius:16px;
    border:0;
    font-size:14px;
    font-weight:900;
    cursor:pointer;
}

.tm-generate-modal-cancel{
    background:#f1f5f9;
    color:#334155;
    border:1px solid #e2e8f0;
}

.tm-generate-modal-ok{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    box-shadow:0 14px 30px rgba(37,99,235,.25);
}

.tm-generate-modal-ok:disabled{
    opacity:.65;
    cursor:not-allowed;
}

@media(max-width:760px){
    .tm-generate-modal-backdrop{
        align-items:flex-start;
        padding:64px 14px 18px 14px;
    }

    .tm-generate-modal-card{
        border-radius:24px;
        max-height:calc(100dvh - 82px);
    }

    .tm-generate-modal-actions{
        padding-bottom:18px;
    }
}
</style>


<script id="tagihan-manual-generate-modal-js">
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('previewGenerateForm');

    if (!form || form.dataset.styledGenerateConfirmInstalled === '2') {
        return;
    }

    form.dataset.styledGenerateConfirmInstalled = '2';

    const modal = document.createElement('div');
    modal.className = 'tm-generate-modal-backdrop';
    modal.innerHTML = `
        <div class="tm-generate-modal-card" role="dialog" aria-modal="true" aria-labelledby="tmGenerateModalTitle">
            <div class="tm-generate-modal-head">
                <div class="tm-generate-modal-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-5"></path>
                        <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
                    </svg>
                </div>
                <h3 class="tm-generate-modal-title" id="tmGenerateModalTitle">Konfirmasi Generate Invoice</h3>
                <div class="tm-generate-modal-subtitle">
                    Pastikan pelanggan dan periode sudah benar sebelum invoice dibuat.
                </div>
            </div>

            <div class="tm-generate-modal-body">
                <div class="tm-generate-modal-grid">
                    <div class="tm-generate-modal-row">
                        <div class="tm-generate-modal-label">Pelanggan dipilih</div>
                        <div class="tm-generate-modal-value" id="tmGenerateCount">0</div>
                    </div>
                    <div class="tm-generate-modal-row">
                        <div class="tm-generate-modal-label">Periode invoice</div>
                        <div class="tm-generate-modal-value" id="tmGeneratePeriod">-</div>
                    </div>
                </div>

                <div class="tm-generate-modal-names" id="tmGenerateNamesBox">
                    <div class="tm-generate-modal-names-title">Nama pelanggan</div>
                    <div class="tm-generate-modal-name-list" id="tmGenerateNames"></div>
                </div>

                <div class="tm-generate-modal-warning" id="tmGenerateWarning">
                    Invoice akan dibuat untuk pelanggan yang dicentang. Data yang sudah ada pada periode yang sama akan dilewati oleh sistem.
                </div>
            </div>

            <div class="tm-generate-modal-actions">
                <button type="button" class="tm-generate-modal-btn tm-generate-modal-cancel" id="tmGenerateCancel">Batal</button>
                <button type="button" class="tm-generate-modal-btn tm-generate-modal-ok" id="tmGenerateOk">Oke, Generate</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    const countEl = modal.querySelector('#tmGenerateCount');
    const periodEl = modal.querySelector('#tmGeneratePeriod');
    const namesBox = modal.querySelector('#tmGenerateNamesBox');
    const namesEl = modal.querySelector('#tmGenerateNames');
    const cancelBtn = modal.querySelector('#tmGenerateCancel');
    const okBtn = modal.querySelector('#tmGenerateOk');
    const warningEl = modal.querySelector('#tmGenerateWarning');
    const titleEl = modal.querySelector('#tmGenerateModalTitle');

    function getSelectedChecks() {
        return Array.from(form.querySelectorAll('tbody input[type="checkbox"]:checked'))
            .filter(function (el) {
                return !el.disabled;
            });
    }

    function cleanText(text) {
        return (text || '').replace(/\s+/g, ' ').trim();
    }

    function looksLikeName(text) {
        const t = cleanText(text);

        if (!t) return false;
        if (/^#?\d+$/.test(t)) return false;
        if (/^[0-9+\-\s]{6,}$/.test(t)) return false;
        if (/^Rp\s?/i.test(t)) return false;
        if (/Belum Bayar|Bayar Awal|Lunas|Nunggak/i.test(t)) return false;
        if (/BRONZE|SILVER|GOLD|PLATINUM|PAKET/i.test(t)) return false;

        return true;
    }

    function getNameFromRow(row, checkbox) {
        if (!row) return 'Nama tidak terbaca';

        const direct =
            checkbox.getAttribute('data-name') ||
            row.getAttribute('data-name') ||
            row.getAttribute('data-customer-name');

        if (looksLikeName(direct)) {
            return cleanText(direct);
        }

        const labeled = Array.from(row.querySelectorAll('td')).find(function (td) {
            const label = (td.getAttribute('data-label') || '').toLowerCase();
            return label.includes('pelanggan') || label.includes('nama');
        });

        if (labeled && looksLikeName(labeled.textContent)) {
            return cleanText(labeled.textContent);
        }

        const cells = Array.from(row.querySelectorAll('td'));

        // Umumnya struktur: checkbox, ID, nama pelanggan, HP, paket.
        if (cells[2] && looksLikeName(cells[2].textContent)) {
            return cleanText(cells[2].textContent);
        }

        const strongs = Array.from(row.querySelectorAll('.neo-strong,strong,b')).map(function (el) {
            return cleanText(el.textContent);
        }).filter(looksLikeName);

        if (strongs.length) {
            return strongs[0];
        }

        for (const td of cells) {
            const t = cleanText(td.textContent);
            if (looksLikeName(t)) {
                return t;
            }
        }

        return 'Nama tidak terbaca';
    }

    function getSelectedCustomers() {
        return getSelectedChecks().map(function (check) {
            return getNameFromRow(check.closest('tr'), check);
        });
    }

    function renderNames(names) {
        namesEl.innerHTML = '';

        if (!names.length) {
            namesBox.style.display = 'none';
            return;
        }

        namesBox.style.display = '';

        const maxShow = 12;
        names.slice(0, maxShow).forEach(function (name) {
            const item = document.createElement('div');
            item.className = 'tm-generate-modal-name-item';

            const dot = document.createElement('span');
            dot.className = 'tm-generate-modal-name-dot';

            const text = document.createElement('span');
            text.textContent = name;

            item.appendChild(dot);
            item.appendChild(text);
            namesEl.appendChild(item);
        });

        if (names.length > maxShow) {
            const more = document.createElement('div');
            more.className = 'tm-generate-modal-name-item';
            more.textContent = '+' + (names.length - maxShow) + ' pelanggan lainnya';
            namesEl.appendChild(more);
        }
    }

    function getPeriodLabel() {
        const periodInput = form.querySelector('input[name="period"], select[name="period"]');

        if (periodInput && periodInput.value) {
            return periodInput.value;
        }

        const url = new URL(window.location.href);
        return url.searchParams.get('period') || '-';
    }

    function lockSubmitButtons() {
        const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');

        buttons.forEach(function (btn) {
            btn.disabled = true;

            if (btn.tagName.toLowerCase() === 'button') {
                btn.dataset.originalText = btn.innerText || '';
                btn.innerText = 'Memproses...';
            } else {
                btn.dataset.originalText = btn.value || '';
                btn.value = 'Memproses...';
            }
        });
    }

    function openModal(count, period, names) {
        titleEl.textContent = 'Konfirmasi Generate Invoice';
        countEl.textContent = count + ' pelanggan';
        periodEl.textContent = period;
        renderNames(names);
        warningEl.textContent = 'Invoice akan dibuat untuk pelanggan yang dicentang. Data yang sudah ada pada periode yang sama akan dilewati oleh sistem.';
        okBtn.style.display = '';
        okBtn.disabled = false;
        okBtn.textContent = 'Oke, Generate';

        document.body.classList.add('tm-generate-modal-open');
        modal.classList.add('tm-show');
    }

    function openWarning(message) {
        titleEl.textContent = 'Belum Ada Pelanggan Dipilih';
        countEl.textContent = '0 pelanggan';
        periodEl.textContent = getPeriodLabel();
        renderNames([]);
        warningEl.textContent = message;
        okBtn.style.display = 'none';

        document.body.classList.add('tm-generate-modal-open');
        modal.classList.add('tm-show');
    }

    function closeModal() {
        modal.classList.remove('tm-show');
        document.body.classList.remove('tm-generate-modal-open');
    }

    cancelBtn.addEventListener('click', function () {
        closeModal();
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('tm-show')) {
            closeModal();
        }
    });

    okBtn.addEventListener('click', function () {
        if (form.dataset.confirmedGenerate === '1') {
            return;
        }

        form.dataset.confirmedGenerate = '1';
        okBtn.disabled = true;
        okBtn.textContent = 'Memproses...';
        lockSubmitButtons();

        HTMLFormElement.prototype.submit.call(form);
    });

    form.addEventListener('submit', function (e) {
        if (form.dataset.confirmedGenerate === '1') {
            return true;
        }

        e.preventDefault();
        e.stopPropagation();

        const selected = getSelectedChecks();
        const count = selected.length;
        const period = getPeriodLabel();
        const names = getSelectedCustomers();

        if (count < 1) {
            openWarning('Pilih minimal 1 pelanggan terlebih dahulu sebelum membuat tagihan manual.');
            return false;
        }

        openModal(count, period, names);
        return false;
    }, true);
});
</script>

