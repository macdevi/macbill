@extends('collector.percobaan.layout')
@section('title','Buat Tagihan Manual')

@section('content')
@include('collector.percobaan._page_style')

@php
    $customerJson = collect($customers ?? [])->map(function ($c) {
        return [
            'id' => $c['id'] ?? '',
            'name' => $c['name'] ?? '',
            'phone' => $c['phone'] ?? '',
            'package_id' => $c['package_id'] ?? '',
            'package_name' => $c['package_name'] ?? '-',
            'amount' => (float) ($c['amount'] ?? 0),
        ];
    })->values();
@endphp

<section class="page-head">
    <h1>Buat Tagihan Manual</h1>
    <p>Pilih pelanggan dengan livefind. Paket dan nominal otomatis terisi dari data pelanggan/paket/invoice terakhir.</p>
</section>
<div class="trial-card-page">
    <div class="trial-card-body">
        <form class="trial-form" method="POST" action="{{ url('/kasir/tagihan-manual') }}" autocomplete="off">
            @csrf

            <label class="livefind-label">
                Pelanggan
                <div class="livefind-box">
                    <input
                        type="text"
                        id="customerSearch"
                        placeholder="Ketik nama pelanggan, contoh: M"
                        autocomplete="off"
                        required
                    >

                    <input type="hidden" name="customer_id" id="customerId">
                    <input type="hidden" name="package_id" id="packageId">

                    <div class="livefind-results" id="customerResults"></div>
                </div>
                <small class="field-note">Ketik minimal 1 huruf, lalu pilih nama pelanggan dari daftar.</small>
            </label>

            <label>
                Paket
                <input type="text" id="packageName" placeholder="Otomatis setelah pelanggan dipilih" readonly>
            </label>

            <label>
                Periode
                <input type="month" name="period" id="periodInput" value="{{ now()->format('Y-m') }}" required>
                <small class="field-note">
                    Periode bulan lalu akan otomatis menjadi Nunggak. Periode bulan depan akan menjadi Bayar Awal saat dibayar.
                </small>
            </label>

            <label>
                Nominal
                <input type="number" name="amount" id="amountInput" min="0" step="1000" placeholder="Otomatis setelah pelanggan dipilih" required>
            </label>

            <label>
                Keterangan
                <textarea name="description" placeholder="Opsional"></textarea>
            </label>

            <button class="trial-btn" type="submit">Buat Tagihan</button>
        </form>
    </div>
</div>

@push('scripts')
<script id="manual-livefind-period-final-v2">
(function(){
    const customers = @json($customerJson);

    const input = document.getElementById('customerSearch');
    const results = document.getElementById('customerResults');
    const customerId = document.getElementById('customerId');
    const packageId = document.getElementById('packageId');
    const packageName = document.getElementById('packageName');
    const amountInput = document.getElementById('amountInput');

    function rupiah(value){
        value = Number(value || 0);
        return 'Rp ' + value.toLocaleString('id-ID');
    }

    function clearSelected(){
        customerId.value = '';
        packageId.value = '';
        packageName.value = '';
        amountInput.value = '';
    }

    function closeResults(){
        results.classList.remove('open');
        results.innerHTML = '';
    }

    function flash(el){
        el.classList.add('autofilled');
        setTimeout(function(){
            el.classList.remove('autofilled');
        }, 700);
    }

    function pickCustomer(c){
        input.value = c.name || '';
        customerId.value = c.id || '';
        packageId.value = c.package_id || '';
        packageName.value = c.package_name || '-';

        const amount = Number(c.amount || 0);
        amountInput.value = amount > 0 ? amount : '';

        closeResults();

        flash(packageName);
        flash(amountInput);
    }

    function escapeHtml(str){
        return String(str || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function render(keyword){
        keyword = (keyword || '').toLowerCase().trim();

        if(!keyword){
            clearSelected();
            closeResults();
            return;
        }

        clearSelected();

        const matches = customers
            .filter(function(c){
                const name = String(c.name || '').toLowerCase();
                const phone = String(c.phone || '').toLowerCase();
                return name.includes(keyword) || phone.includes(keyword);
            })
            .slice(0, 20);

        if(matches.length === 0){
            results.innerHTML = '<div class="livefind-empty">Pelanggan tidak ditemukan.</div>';
            results.classList.add('open');
            return;
        }

        results.innerHTML = matches.map(function(c, index){
            return `
                <button type="button" class="livefind-item" data-index="${index}">
                    <span>
                        <b>${escapeHtml(c.name || '-')}</b>
                        <small>${escapeHtml(c.phone || '')}</small>
                    </span>
                    <em>${escapeHtml(c.package_name || '-')} · ${rupiah(c.amount || 0)}</em>
                </button>
            `;
        }).join('');

        results.querySelectorAll('.livefind-item').forEach(function(btn){
            btn.addEventListener('click', function(){
                const idx = Number(btn.dataset.index);
                pickCustomer(matches[idx]);
            });
        });

        results.classList.add('open');
    }

    input.addEventListener('input', function(){
        render(input.value);
    });

    input.addEventListener('focus', function(){
        if(input.value.trim()){
            render(input.value);
        }
    });

    document.addEventListener('click', function(e){
        if(!e.target.closest('.livefind-box')){
            closeResults();
        }
    });

    document.querySelector('.trial-form').addEventListener('submit', function(e){
        if(!customerId.value){
            e.preventDefault();
            input.focus();
            render(input.value || ' ');
            alert('Pilih pelanggan dari daftar livefind terlebih dahulu.');
            return;
        }

        if(!amountInput.value || Number(amountInput.value) <= 0){
            e.preventDefault();
            amountInput.focus();
            alert('Nominal pelanggan belum ditemukan otomatis. Isi nominal terlebih dahulu.');
            return;
        }
    });
})();
</script>
@endpush
@endsection
