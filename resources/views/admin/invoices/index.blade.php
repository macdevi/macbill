@extends('layouts.neo')
@section('title','Tagihan')
@section('content')


<style id="admin-invoice-native-action-fix">
/* Khusus Admin > Tagihan: dropdown Aksi native dan rapi di mobile */
.pagehead,
.pagehead .neo-actions,
.admin-invoice-top-actions{
    overflow:visible !important;
}

.admin-invoice-top-actions{
    position:relative;
    z-index:80;
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.invoice-action-native{
    position:relative;
    display:inline-flex;
    z-index:100000;
}

.invoice-action-native summary{
    list-style:none;
    cursor:pointer;
    user-select:none;
}

.invoice-action-native summary::-webkit-details-marker{
    display:none;
}

.invoice-action-menu{
    display:none;
    position:absolute;
    top:calc(100% + 10px);
    left:0;
    right:auto;
    width:280px;
    max-width:calc(100vw - 32px);
    padding:8px;
    border-radius:20px;
    background:#fff;
    border:1px solid #e2e8f0;
    box-shadow:0 24px 70px rgba(15,23,42,.18);
    z-index:100001;
    overflow:hidden;
}

.invoice-action-native[open] .invoice-action-menu{
    display:grid;
    gap:4px;
}

.invoice-action-menu a,
.invoice-action-menu button{
    width:100%;
    min-height:44px;
    border:0;
    border-radius:13px;
    padding:10px 12px;
    display:flex;
    align-items:center;
    justify-content:flex-start;
    gap:9px;
    background:transparent;
    color:#0f172a;
    font:inherit;
    font-size:13px;
    font-weight:850;
    line-height:1.25;
    text-align:left;
    text-decoration:none;
    cursor:pointer;
    white-space:normal;
}

.invoice-action-menu a:hover,
.invoice-action-menu button:hover{
    background:#f1f5f9;
}

.invoice-action-menu form{
    margin:0;
    padding:0;
}

.invoice-action-menu .neo-mini-ico{
    flex:0 0 auto;
    width:18px;
    height:18px;
}

.invoice-action-menu .neo-mini-ico svg{
    width:18px;
    height:18px;
}

@media(max-width:760px){
    .admin-invoice-top-actions{
        overflow:visible !important;
    }

    .invoice-action-menu{
        left:0 !important;
        right:auto !important;
        width:260px !important;
        max-width:calc(100vw - 24px) !important;
        border-radius:18px;
    }
}
</style>




<style id="admin-invoice-reset-button-css">
.admin-invoice-reset-form{
    display:inline-flex !important;
    margin:0 !important;
    padding:0 !important;
}

.admin-invoice-reset-btn{
    background:#fff7ed !important;
    color:#c2410c !important;
    border:1px solid #fed7aa !important;
}

.admin-invoice-reset-btn:hover{
    background:#ffedd5 !important;
    color:#9a3412 !important;
}

.admin-invoice-reset-btn svg{
    stroke:#c2410c !important;
}

@media(max-width:760px){
    .admin-invoice-reset-btn{
        width:42px !important;
        min-width:42px !important;
        height:42px !important;
        min-height:42px !important;
        padding:0 !important;
        border-radius:14px !important;
    }
}
</style>


<style id="admin-customer-invoice-reset-css">
.admin-customer-invoice-reset-form{
    display:inline-flex !important;
    margin:0 !important;
    padding:0 !important;
}

.admin-customer-invoice-reset-btn{
    background:#fff7ed !important;
    color:#c2410c !important;
    border:1px solid #fed7aa !important;
    font-size:20px !important;
    line-height:1 !important;
}

.admin-customer-invoice-reset-btn:hover{
    background:#ffedd5 !important;
    color:#9a3412 !important;
}

@media(max-width:760px){
    .admin-customer-invoice-reset-btn{
        width:42px !important;
        min-width:42px !important;
        height:42px !important;
        min-height:42px !important;
        padding:0 !important;
        border-radius:14px !important;
    }
}
</style>

@php
    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $periodValue = $period ?? request('period', now()->format('Y-m'));
    $statusValue = $status ?? request('status', '');

    $badge = fn($s) => match($s) {
        'Lunas' => 'green',
        'Bayar Awal' => 'blue',
        'Nunggak' => 'red',
        'Belum Bayar' => 'yellow',
        default => 'blue',
    };

    $i = function ($name) {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>',
            'menu' => '<svg viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>',
            'file' => '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>',
            'clock' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
            'refresh' => '<svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 1-9 9 9.7 9.7 0 0 1-6.7-2.7"/><path d="M3 12a9 9 0 0 1 9-9 9.7 9.7 0 0 1 6.7 2.7"/><path d="M3 21v-6h6"/><path d="M21 3v6h-6"/></svg>',
            'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
            'eye' => '<svg viewBox="0 0 24 24"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>',
            'print' => '<svg viewBox="0 0 24 24"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp


@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif


<form class="neo-search" method="GET" action="{{ url('/admin/invoices') }}">
    <input class="input" type="month" name="period" value="{{ $periodValue }}">

    <select class="select" name="status">
        <option value="">Semua Status</option>
        <option value="Belum Bayar" @selected($statusValue === 'Belum Bayar')>Belum Bayar</option>
        <option value="Nunggak" @selected($statusValue === 'Nunggak')>Nunggak</option>
        <option value="Bayar Awal" @selected($statusValue === 'Bayar Awal')>Bayar Awal</option>
        <option value="Lunas" @selected($statusValue === 'Lunas')>Lunas</option>
    </select>

    <button class="btn" type="submit">{!! $i('search') !!}Filter</button>
</form>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total halaman ini: <b>{{ $invoices->count() }}</b></span>
        <span>Geser kanan untuk aksi</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th class="sticky-left">ID</th>
                    <th>No Tagihan</th>
                    <th>Pelanggan</th>
                    <th>ODP / Port</th>
                    <th>Periode</th>
                    <th>Jatuh Tempo</th>
                    <th>Nominal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td class="neo-id sticky-left">#{{ $invoice->id }}</td>
                        <td class="neo-strong">{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->customer?->name ?: '-' }}</td>
                        <td>
                            {{ $invoice->customer?->odp ?: '-' }}
                            {{ $invoice->customer?->port_number ? 'Port '.$invoice->customer?->port_number : '' }}
                        </td>
                        <td>{{ $invoice->period }}</td>
                        <td>{{ $invoice->due_date }}</td>
                        <td class="neo-money">{{ $money($invoice->amount) }}</td>
                        <td><span class="badge {{ $badge($invoice->status) }}">{{ $invoice->status }}</span></td>
                        <td>
                            <div class="neo-row-actions">
                                <a class="btn light icon" title="Detail" aria-label="Detail" href="{{ url('/admin/invoices/'.$invoice->id.'/detail') }}">
                                    {!! $i('eye') !!}
                                </a>
                                <a class="btn light icon" title="Cetak" aria-label="Cetak" href="{{ url('/admin/invoices/'.$invoice->id.'/print') }}">
                                    {!! $i('print') !!}
                                </a>
                                <form class="admin-customer-invoice-reset-form" method="POST" action="{{ url('/admin/invoices/customer/'.$invoice->customer_id.'/reset-data') }}" onsubmit="return confirm('Reset SEMUA invoice dan payment milik pelanggan ini? Data pelanggan tetap ada, hanya data tagihan/pembayaran pelanggan ini yang dikosongkan.');">
                                    @csrf
                                    <input type="hidden" name="confirm_reset_customer" value="RESET TAGIHAN PELANGGAN">
                                    <button class="btn light icon admin-customer-invoice-reset-btn" type="submit" title="Reset Data Tagihan Pelanggan" aria-label="Reset Data Tagihan Pelanggan">
                                        ↺
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">Belum ada tagihan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">{{ $invoices->links() }}</div>

<style>
.generate-preview-backdrop{
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.58);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:99999;
    padding:18px;
}
.generate-preview-backdrop.show{
    display:flex;
}
.generate-preview-modal{
    width:min(520px,100%);
    max-height:88vh;
    overflow:auto;
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:26px;
    box-shadow:0 30px 80px rgba(15,23,42,.24);
    padding:18px;
}
.generate-preview-modal h3{
    margin:0;
    color:#101828;
    font-size:22px;
    letter-spacing:-.055em;
}
.generate-preview-modal p{
    margin:7px 0 0;
    color:#667085;
    font-size:13px;
    font-weight:700;
    line-height:1.45;
}
.generate-preview-summary{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:10px;
    margin-top:14px;
}
.generate-preview-stat{
    border:1px solid #eef2f7;
    background:#f8fafc;
    border-radius:18px;
    padding:12px;
}
.generate-preview-stat span{
    display:block;
    color:#667085;
    font-size:11px;
    font-weight:800;
}
.generate-preview-stat b{
    display:block;
    margin-top:5px;
    color:#101828;
    font-size:18px;
    font-weight:950;
    letter-spacing:-.04em;
}
.generate-preview-list{
    margin-top:14px;
    border:1px solid #eef2f7;
    border-radius:18px;
    overflow:hidden;
}
.generate-preview-row{
    display:grid;
    grid-template-columns:42px 1fr;
    gap:10px;
    padding:10px 12px;
    border-bottom:1px solid #eef2f7;
}
.generate-preview-row:last-child{
    border-bottom:0;
}
.generate-preview-row .no{
    color:#667085;
    font-size:12px;
    font-weight:900;
}
.generate-preview-row .name{
    color:#101828;
    font-size:13px;
    font-weight:950;
}
.generate-preview-row .meta{
    margin-top:2px;
    color:#667085;
    font-size:11px;
    font-weight:700;
    line-height:1.35;
}
.generate-preview-note{
    margin-top:12px;
    border:1px solid #fedf89;
    background:#fffaeb;
    color:#93370d;
    border-radius:16px;
    padding:10px 12px;
    font-size:12px;
    font-weight:800;
    line-height:1.45;
}
.generate-preview-actions{
    display:flex;
    gap:8px;
    margin-top:15px;
}
.generate-preview-actions .btn{
    flex:1;
    justify-content:center;
    min-height:46px;
}
@media(max-width:620px){
    .generate-preview-modal{
        border-radius:22px;
        padding:15px;
    }
    .generate-preview-summary{
        grid-template-columns:1fr;
    }
}
</style>

<div class="generate-preview-backdrop" id="generatePreviewModal">
    <div class="generate-preview-modal">
        <h3>Preview Generate Tagihan</h3>
        <p>Periksa data pelanggan sebelum tagihan dibuat. Tagihan baru dibuat setelah tombol konfirmasi ditekan.</p>

        <div class="generate-preview-summary">
            <div class="generate-preview-stat">
                <span>Pelanggan Dipilih</span>
                <b id="generatePreviewCount">0</b>
            </div>

            <div class="generate-preview-stat">
                <span>Periode</span>
                <b id="generatePreviewPeriod">-</b>
            </div>
        </div>

        <div class="generate-preview-list" id="generatePreviewList"></div>

        <div class="generate-preview-note">
            Sistem hanya akan membuat tagihan untuk pelanggan yang dicentang dan belum memiliki tagihan pada periode tersebut.
        </div>

        <div class="generate-preview-actions">
            <button type="button" class="btn light" id="generatePreviewCancel">Batal</button>
            <button data-force-generate-open="1" type="button" class="btn" id="generatePreviewConfirm">Ya, Generate Tagihan</button>
        </div>
    </div>
</div>

<script>
(function(){
    const modal = document.getElementById('generatePreviewModal');
    const countEl = document.getElementById('generatePreviewCount');
    const periodEl = document.getElementById('generatePreviewPeriod');
    const listEl = document.getElementById('generatePreviewList');
    const cancelBtn = document.getElementById('generatePreviewCancel');
    const confirmBtn = document.getElementById('generatePreviewConfirm');

    if (!modal || !countEl || !periodEl || !listEl || !confirmBtn) return;

    let activeForm = null;
    let confirmed = false;

    function findGenerateForm(){
        const forms = Array.from(document.querySelectorAll('form'));

        return forms.find(function(form){
            const text = (form.innerText || '').toLowerCase();
            const action = (form.getAttribute('action') || '').toLowerCase();

            return text.includes('buat tagihan terpilih')
                || text.includes('generate tagihan')
                || action.includes('generate-selected')
                || action.includes('generate');
        });
    }

    function findSelectedRows(form){
        const checked = Array.from(form.querySelectorAll('input[type="checkbox"]:checked'));

        return checked.filter(function(cb){
            return cb.name && cb.name !== '_token';
        });
    }

    function detectPeriod(){
        const bodyText = document.body.innerText || '';
        const match = bodyText.match(/20\d{2}-\d{2}/);
        return match ? match[0] : '-';
    }

    function cleanText(value){
        return String(value || '').replace(/\s+/g, ' ').trim();
    }

    function rowDataFromCheckbox(cb, index){
        const tr = cb.closest('tr');
        const row = cb.closest('.card') || tr;

        if (!row) {
            return {
                no: index + 1,
                name: 'Pelanggan #' + cb.value,
                meta: 'ID ' + cb.value
            };
        }

        if (tr) {
            const cells = Array.from(tr.querySelectorAll('td')).map(function(td){
                return cleanText(td.innerText);
            }).filter(Boolean);

            const id = cells.find(function(x){ return x.startsWith('#'); }) || ('#' + cb.value);
            const name = cells.find(function(x){
                return !x.startsWith('#')
                    && !x.match(/^[0-9+\-\s]+$/)
                    && !x.toLowerCase().includes('pilih');
            }) || ('Pelanggan #' + cb.value);

            return {
                no: index + 1,
                name: name,
                meta: cells.slice(0, 5).join(' · ') || id
            };
        }

        return {
            no: index + 1,
            name: cleanText(row.innerText).slice(0, 70) || ('Pelanggan #' + cb.value),
            meta: 'ID ' + cb.value
        };
    }

    function openPreview(form){
        const selected = findSelectedRows(form);

        if (selected.length < 1) {
            alert('Pilih minimal satu pelanggan.');
            return;
        }

        const rows = selected.slice(0, 10).map(rowDataFromCheckbox);

        countEl.textContent = selected.length;
        periodEl.textContent = detectPeriod();

        listEl.innerHTML = rows.map(function(row){
            return `
                <div class="generate-preview-row">
                    <div class="no">#${row.no}</div>
                    <div>
                        <div class="name">${row.name}</div>
                        <div class="meta">${row.meta}</div>
                    </div>
                </div>
            `;
        }).join('');

        if (selected.length > 10) {
            listEl.innerHTML += `
                <div class="generate-preview-row">
                    <div class="no">...</div>
                    <div>
                        <div class="name">Dan ${selected.length - 10} pelanggan lainnya</div>
                        <div class="meta">Tidak semua data ditampilkan di preview.</div>
                    </div>
                </div>
            `;
        }

        modal.classList.add('show');
    }

    const form = findGenerateForm();

    if (!form) return;

    form.addEventListener('submit', function(e){
        if (confirmed) return;

        e.preventDefault();
        activeForm = form;
        openPreview(form);
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(){
            modal.classList.remove('show');
        });
    }

    confirmBtn.addEventListener('click', function(){
        if (!activeForm) return;

        confirmed = true;
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Membuat Tagihan...';
        modal.classList.remove('show');

        activeForm.submit();
    });
})();
</script>


{{-- FORCE GENERATE PREVIEW V3 START --}}
<style>
.force-generate-preview-backdrop{
    position:fixed;
    inset:0;
    display:none;
    align-items:center;
    justify-content:center;
    padding:18px;
    background:rgba(15,23,42,.58);
    z-index:999999;
}
.force-generate-preview-backdrop.show{
    display:flex;
}
.force-generate-preview-modal{
    width:min(540px,100%);
    max-height:88vh;
    overflow:auto;
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:26px;
    padding:18px;
    box-shadow:0 30px 80px rgba(15,23,42,.25);
}
.force-generate-preview-modal h3{
    margin:0;
    color:#101828;
    font-size:22px;
    letter-spacing:-.055em;
}
.force-generate-preview-modal p{
    margin:7px 0 0;
    color:#667085;
    font-size:13px;
    font-weight:700;
    line-height:1.45;
}
.force-generate-preview-summary{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:10px;
    margin-top:14px;
}
.force-generate-preview-stat{
    border:1px solid #eef2f7;
    background:#f8fafc;
    border-radius:18px;
    padding:12px;
}
.force-generate-preview-stat span{
    display:block;
    color:#667085;
    font-size:11px;
    font-weight:800;
}
.force-generate-preview-stat b{
    display:block;
    margin-top:5px;
    color:#101828;
    font-size:18px;
    font-weight:950;
    letter-spacing:-.04em;
}
.force-generate-preview-list{
    margin-top:14px;
    border:1px solid #eef2f7;
    border-radius:18px;
    overflow:hidden;
}
.force-generate-preview-row{
    display:grid;
    grid-template-columns:42px 1fr;
    gap:10px;
    padding:10px 12px;
    border-bottom:1px solid #eef2f7;
}
.force-generate-preview-row:last-child{
    border-bottom:0;
}
.force-generate-preview-row .no{
    color:#667085;
    font-size:12px;
    font-weight:900;
}
.force-generate-preview-row .name{
    color:#101828;
    font-size:13px;
    font-weight:950;
}
.force-generate-preview-row .meta{
    margin-top:2px;
    color:#667085;
    font-size:11px;
    font-weight:700;
    line-height:1.35;
}
.force-generate-preview-note{
    margin-top:12px;
    border:1px solid #fedf89;
    background:#fffaeb;
    color:#93370d;
    border-radius:16px;
    padding:10px 12px;
    font-size:12px;
    font-weight:800;
    line-height:1.45;
}
.force-generate-preview-actions{
    display:flex;
    gap:8px;
    margin-top:15px;
}
.force-generate-preview-actions .btn{
    flex:1;
    justify-content:center;
    min-height:46px;
}
@media(max-width:620px){
    .force-generate-preview-modal{
        border-radius:22px;
        padding:15px;
    }
    .force-generate-preview-summary{
        grid-template-columns:1fr;
    }
}
</style>

<div class="force-generate-preview-backdrop" id="forceGeneratePreviewModalV3">
    <div class="force-generate-preview-modal">
        <h3>Preview Generate Tagihan</h3>
        <p>Periksa pelanggan yang dipilih sebelum tagihan dibuat.</p>

        <div class="force-generate-preview-summary">
            <div class="force-generate-preview-stat">
                <span>Pelanggan Dipilih</span>
                <b id="forceGenerateCountV3">0</b>
            </div>

            <div class="force-generate-preview-stat">
                <span>Periode</span>
                <b id="forceGeneratePeriodV3">-</b>
            </div>
        </div>

        <div class="force-generate-preview-list" id="forceGenerateListV3"></div>

        <div class="force-generate-preview-note">
            Yang dibuat hanya pelanggan yang dicentang dan belum punya tagihan pada periode tersebut.
        </div>

        <div class="force-generate-preview-actions">
            <button type="button" class="btn light" id="forceGenerateCancelV3">Batal</button>
            <button data-force-generate-open="1" type="button" class="btn" id="forceGenerateSubmitV3">Ya, Generate Tagihan</button>
        </div>
    </div>
</div>

<script>
(function(){
    const modal = document.getElementById('forceGeneratePreviewModalV3');
    const countEl = document.getElementById('forceGenerateCountV3');
    const periodEl = document.getElementById('forceGeneratePeriodV3');
    const listEl = document.getElementById('forceGenerateListV3');
    const cancelBtn = document.getElementById('forceGenerateCancelV3');
    const submitBtn = document.getElementById('forceGenerateSubmitV3');

    if (!modal || !countEl || !periodEl || !listEl || !submitBtn) return;

    let activeForm = null;

    function cleanText(v){
        return String(v || '').replace(/\s+/g, ' ').trim();
    }

    function getGenerateForm(button){
        let form = button.closest('form');

        if (!form && button.getAttribute('form')) {
            form = document.getElementById(button.getAttribute('form'));
        }

        if (!form) {
            form = document.querySelector('form[data-force-generate-form="1"]');
        }

        return form;
    }

    function selectedCheckboxes(form){
        return Array.from(form.querySelectorAll('input[type="checkbox"]:checked')).filter(function(cb){
            const name = String(cb.name || '').toLowerCase();
            const id = String(cb.id || '').toLowerCase();

            if (!name) return false;
            if (name.includes('_token')) return false;
            if (name.includes('select_all')) return false;
            if (id.includes('select_all')) return false;
            if (id.includes('check_all')) return false;

            return true;
        });
    }

    function detectPeriod(){
        const text = document.body.innerText || '';
        const match = text.match(/20\d{2}-\d{2}/);
        return match ? match[0] : '-';
    }

    function rowData(cb, index){
        const tr = cb.closest('tr');

        if (!tr) {
            return {
                no: index + 1,
                name: 'Pelanggan #' + (cb.value || '-'),
                meta: 'ID ' + (cb.value || '-')
            };
        }

        const cells = Array.from(tr.querySelectorAll('td')).map(function(td){
            return cleanText(td.innerText);
        }).filter(Boolean);

        const id = cells.find(function(x){ return x.startsWith('#'); }) || ('#' + (cb.value || '-'));

        const name = cells.find(function(x){
            return !x.startsWith('#')
                && !/^[0-9+\-\s.]+$/.test(x)
                && !x.toLowerCase().includes('pilih');
        }) || ('Pelanggan ' + id);

        return {
            no: index + 1,
            name: name,
            meta: cells.slice(0, 5).join(' · ') || id
        };
    }

    function openPreview(form){
        const selected = selectedCheckboxes(form);

        if (selected.length < 1) {
            alert('Pilih minimal satu pelanggan.');
            return;
        }

        activeForm = form;

        countEl.textContent = selected.length;
        periodEl.textContent = detectPeriod();

        const rows = selected.slice(0, 10).map(rowData);

        listEl.innerHTML = rows.map(function(row){
            return `
                <div class="force-generate-preview-row">
                    <div class="no">#${row.no}</div>
                    <div>
                        <div class="name">${row.name}</div>
                        <div class="meta">${row.meta}</div>
                    </div>
                </div>
            `;
        }).join('');

        if (selected.length > 10) {
            listEl.innerHTML += `
                <div class="force-generate-preview-row">
                    <div class="no">...</div>
                    <div>
                        <div class="name">Dan ${selected.length - 10} pelanggan lainnya</div>
                        <div class="meta">Tidak semua pelanggan ditampilkan di preview.</div>
                    </div>
                </div>
            `;
        }

        modal.classList.add('show');
    }

    document.querySelectorAll('[data-force-generate-open="1"]').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            e.stopPropagation();

            const form = getGenerateForm(btn);

            if (!form) {
                alert('Form generate tidak ditemukan.');
                return;
            }

            openPreview(form);
        }, true);
    });

    document.querySelectorAll('form[data-force-generate-form="1"]').forEach(function(form){
        form.addEventListener('submit', function(e){
            if (form.getAttribute('data-force-confirmed') === '1') {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            openPreview(form);
        }, true);
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(){
            modal.classList.remove('show');
            activeForm = null;
        });
    }

    submitBtn.addEventListener('click', function(){
        if (!activeForm) return;

        activeForm.setAttribute('data-force-confirmed', '1');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Membuat Tagihan...';

        modal.classList.remove('show');

        HTMLFormElement.prototype.submit.call(activeForm);
    });
})();
</script>
{{-- FORCE GENERATE PREVIEW V3 END --}}


@endsection
