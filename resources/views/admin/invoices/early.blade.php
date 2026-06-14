@extends('layouts.neo')
@section('title','Tagihan Awal')
@section('content')
@php
    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');
    $invoiceBase = request()->is('collector/*') ? '/collector/invoices' : '/admin/invoices';
    $dashboardUrl = request()->is('collector/*') ? '/collector/dashboard' : '/admin/dashboard';
@endphp

<div class="pagehead">
    <div>
        <h1>Tagihan Awal</h1>
        <p>Buat tagihan lebih awal untuk pelanggan yang due_date-nya belum tiba. Ini dipakai untuk Bayar Awal.</p>
    </div>
    <div class="actions">
        <a class="btn light" href="{{ url($dashboardUrl) }}">⌂</a>
        <a class="btn light" href="{{ url($invoiceBase.'/preview?period='.$period) }}">Cek Biasa</a>
        <a class="btn light" href="{{ url($invoiceBase.'?period='.$period) }}">Daftar Tagihan</a>
    </div>
</div>

@if(session('success'))<div id="flash" class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div id="flash" class="alert err">{{ session('error') }}</div>@endif

<div class="hero">
    <span>Early tagihan periode</span>
    <b>{{ $period }}</b>
</div>

<form class="searchbar" method="GET" action="{{ url($invoiceBase.'/early') }}">
    <input class="input" type="month" name="period" value="{{ $period }}">
    <button class="btn" type="submit">Ganti Periode</button>
</form>

<div class="grid" style="margin-bottom:10px">
    <div class="card">
        <div class="label">Siap Tagihan Awal</div>
        <div class="val">{{ number_format($stats['ready']) }}</div>
    </div>
    <div class="card">
        <div class="label">Total Cek</div>
        <div class="val">{{ $money($stats['total_preview']) }}</div>
    </div>
    <div class="card">
        <div class="label">Status Awal</div>
        <div class="val">Belum Bayar</div>
    </div>
    <div class="card">
        <div class="label">Jika Dibayar</div>
        <div class="val">Bayar Awal</div>
    </div>
</div>

<div class="card">
    <div class="main">Aturan Tagihan Awal</div>
    <div class="muted">
        Tagihan yang dibuat lebih awal tetap berstatus Belum Bayar sampai dibayar. Jika kasir menerima pembayaran sebelum due_date, status menjadi Bayar Awal. Saat due_date tiba, cron sync dapat mengubah Bayar Awal menjadi Lunas. Cron generate akan skip karena tagihan periode itu sudah ada.
    </div>
</div>

<div class="card">
    <div class="main">Cek yang dicentang</div>
    <div class="muted" id="selectedCount">Belum ada pelanggan dicentang.</div>
    <div class="val" id="selectedTotal" style="margin-top:6px">Rp 0</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
        <button class="btn light" type="button" id="earlySelectAllBtn">Pilih Semua</button>
        <button class="btn light" type="button" id="earlyClearAllBtn">Kosongkan</button>
    </div>
    <div id="selectedList" style="margin-top:8px;font-size:12px;color:#334155;display:grid;gap:5px"></div>
</div>

<input class="input" id="searchBox" style="margin-bottom:10px" placeholder="Cari nama, HP, tagihan, due date...">

<form method="POST" action="{{ url($invoiceBase.'/early-generate-selected') }}" data-force-generate-form="1">
    @csrf
    <input type="hidden" name="period" value="{{ $period }}">

    <div style="display:grid;gap:8px">
        @forelse($rows as $row)
            @php
                $customer = $row->customer;
                $search = strtolower($customer->name.' '.$customer->phone.' '.$row->invoice_number.' '.$row->amount.' '.$row->due_date);
            @endphp

            <div class="card tagihan-card" data-search="{{ $search }}" style="margin-bottom:0">
                <div style="display:grid;grid-template-columns:34px 1fr;gap:10px;align-items:flex-start">
                    <div>
                        <input class="tagihan-check" type="checkbox"
                               name="customer_ids[]"
                               value="{{ $customer->id }}"
                               data-name="{{ $customer->name }}"
                               data-tagihan="{{ $row->invoice_number }}"
                               data-amount="{{ $row->amount }}"
                               style="width:20px;height:20px;margin-top:3px">
                    </div>

                    <div>
                        <div class="main">{{ $customer->name }}</div>
                        <div class="muted">#{{ $customer->id }} · {{ $customer->phone ?: '-' }} · {{ $customer->package?->name ?: '-' }}</div>

                        <div class="grid" style="grid-template-columns:repeat(4,1fr);gap:6px;margin-top:8px">
                            <div class="card" style="padding:8px;margin:0"><div class="label">Tagihan</div><div class="main">{{ $row->invoice_number }}</div></div>
                            <div class="card" style="padding:8px;margin:0"><div class="label">Nominal</div><div class="main">{{ $money($row->amount) }}</div></div>
                            <div class="card" style="padding:8px;margin:0"><div class="label">Due Date</div><div class="main">{{ $row->due_date }}</div></div>
                            <div class="card" style="padding:8px;margin:0"><div class="label">Tanggal Tagih</div><div class="main">{{ $customer->billing_day }}</div></div>
                        </div>

                        <div style="margin-top:8px">
                            <span class="badge blue">Siap Tagihan Awal</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                Tidak ada pelanggan yang siap early tagihan untuk periode ini. Kemungkinan due_date sudah lewat/hari ini, tagihan sudah ada, pelanggan nonaktif, atau nominal kosong.
            </div>
        @endforelse
    </div>

    @if($rows->count())
    <div class="card" style="position:sticky;bottom:10px;z-index:10;margin-top:10px">
        <div style="display:grid;grid-template-columns:1fr 170px 210px;gap:8px;align-items:center">
            <div>
                <div class="label">Total dicentang</div>
                <div class="val" id="bottomTotal">Rp 0</div>
            </div>
            <button class="btn light" type="button" id="previewBtn">Cek Dicentang</button>
            <button class="btn" type="submit">Generate Tagihan Awal</button>
        </div>
    </div>
    @endif
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checks = document.querySelectorAll('.tagihan-check');
    const selectedCount = document.getElementById('selectedCount');
    const selectedTotal = document.getElementById('selectedTotal');
    const bottomTotal = document.getElementById('bottomTotal');
    const selectedList = document.getElementById('selectedList');
    const searchBox = document.getElementById('searchBox');

    function rupiah(n) {
        return 'Rp ' + Math.round(n).toLocaleString('id-ID');
    }

    function renderSelected() {
        let total = 0;
        let count = 0;
        let lines = [];

        checks.forEach(function (cb) {
            if (cb.checked) {
                count++;
                total += parseFloat(cb.dataset.amount || 0);
                lines.push('<div>• ' + cb.dataset.name + ' — ' + cb.dataset.tagihan + ' — ' + rupiah(cb.dataset.amount || 0) + '</div>');
            }
        });

        selectedCount.textContent = count ? count + ' pelanggan dicentang.' : 'Belum ada pelanggan dicentang.';
        selectedTotal.textContent = rupiah(total);
        if (bottomTotal) bottomTotal.textContent = rupiah(total);
        selectedList.innerHTML = lines.join('');
    }

    checks.forEach(cb => cb.addEventListener('change', renderSelected));

    const previewBtn = document.getElementById('previewBtn');
    if (previewBtn) {
        previewBtn.addEventListener('click', function () {
            renderSelected();

            if (!document.querySelectorAll('.tagihan-check:checked').length) {
                alert('Belum ada pelanggan yang dicentang.');
                return;
            }

            selectedList.scrollIntoView({behavior:'smooth', block:'center'});
        });
    }

    if (searchBox) {
        searchBox.addEventListener('input', function () {
            const q = searchBox.value.trim().toLowerCase();

            document.querySelectorAll('.tagihan-card').forEach(function (card) {
                card.style.display = (card.dataset.search || '').includes(q) ? 'block' : 'none';
            });
        });
    }

    setTimeout(function () {
        const flash = document.getElementById('flash');
        if (flash) flash.style.display = 'none';
    }, 3500);

    renderSelected();
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAllBtn = document.getElementById('earlySelectAllBtn');
    const clearAllBtn = document.getElementById('earlyClearAllBtn');

    function checks() {
        return Array.from(document.querySelectorAll('.tagihan-check'));
    }

    function visibleChecks() {
        return checks().filter(function (cb) {
            const card = cb.closest('.tagihan-card');
            return !card || card.style.display !== 'none';
        });
    }

    function triggerUpdate() {
        const first = checks()[0];
        if (first) {
            first.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function () {
            visibleChecks().forEach(function (cb) {
                cb.checked = true;
            });
            triggerUpdate();
        });
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function () {
            checks().forEach(function (cb) {
                cb.checked = false;
            });
            triggerUpdate();
        });
    }
});
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
            <button type="button" class="btn" id="forceGenerateSubmitV3">Ya, Generate Tagihan</button>
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
