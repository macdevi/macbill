@extends('collector.percobaan.layout')
@section('title','Tagihan Gabungan')

@section('content')
@include('collector.percobaan._page_style')

@php
    $money = fn($v) => 'Rp '.number_format((float) $v,0,',','.');
    $periodLabel = function ($period) {
        try {
            return \Illuminate\Support\Carbon::parse($period.'-01')->translatedFormat('F Y');
        } catch (\Throwable $e) {
            return $period ?: '-';
        }
    };
@endphp

<section class="page-head">
    <h1>Tagihan</h1>
    <p>Invoice digabung per pelanggan. Klik bayar untuk memilih periode yang akan dibayar.</p>
</section>

<form class="trial-filter" method="GET">
    <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Cari pelanggan, nomor HP, invoice, atau periode...">
    <button type="submit">Cari</button>
</form>

<div class="trial-card-page">
    <div class="trial-table-wrap">
        <table class="trial-table grouped-billing-table">
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Aksi</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Periode</th>
                    <th>Invoice</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    @php
                        $details = collect($row->details ?? []);
                        $json = [
                            'customer_id' => $row->customer_id,
                            'customer_name' => $row->customer_name,
                            'customer_phone' => $row->customer_phone,
                            'customer_address' => $row->customer_address,
                            'package_name' => $row->package_name,
                            'billing_day' => $row->billing_day,
                            'total_amount' => $row->total_amount,
                            'period_count' => $row->period_count,
                            'details' => $details->map(function ($d) use ($periodLabel) {
                                return [
                                    'id' => $d['id'],
                                    'invoice_number' => $d['invoice_number'],
                                    'period' => $d['period'],
                                    'period_label' => $periodLabel($d['period']),
                                    'due_date' => $d['due_date'],
                                    'amount' => $d['amount'],
                                    'remaining' => $d['remaining'],
                                    'status' => $d['status'],
                                ];
                            })->values(),
                        ];

                        $periodText = $details->count() > 1
                            ? $details->count().' periode'
                            : $periodLabel($details->first()['period'] ?? '');

                        $statusClass = $row->status === 'Nunggak' ? 'status-old-danger' : 'status-old-warning';
                    @endphp

                    <tr class="tagihan-row">
                        <td class="customer-cell">
                            <b>{{ $row->customer_name }}</b>
                            <small>{{ $row->customer_phone }}</small>
                        </td>

                        <td class="action-cell">
                            <button type="button" class="action-icon js-group-detail" data-json='@json($json)' title="Lihat detail">
                                <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>

                            <button type="button" class="action-icon pay js-group-pay" data-json='@json($json)' title="Bayar periode">
                                <svg viewBox="0 0 24 24"><path d="M3 7h18v10H3z"></path><path d="M7 11h5"></path><path d="M17 14h.01"></path></svg>
                            </button>
                        </td>

                        <td><span class="status-pill {{ $statusClass }}">{{ $row->status }}</span></td>
                        <td class="money">{{ $money($row->total_amount) }}</td>
                        <td>
                            <b>{{ $periodText }}</b>
                            <small class="period-mini">
                                {{ $details->pluck('period')->join(', ') }}
                            </small>
                        </td>
                        <td>{{ $row->invoice_count }} invoice</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">Tidak ada tagihan terbuka.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="trial-modal" id="detailModal" aria-hidden="true">
    <div class="trial-modal-backdrop js-close-modal"></div>
    <div class="trial-modal-box wide">
        <button type="button" class="modal-x js-close-modal">×</button>
        <h2>Detail Tagihan Gabungan</h2>
        <p id="detailCustomer">-</p>

        <div class="detail-grid">
            <div><span>HP</span><b id="detailPhone">-</b></div>
            <div><span>Paket</span><b id="detailPackage">-</b></div>
            <div><span>Billing Day</span><b id="detailBillingDay">-</b></div>
            <div><span>Total</span><b id="detailTotal">-</b></div>
        </div>

        <div class="group-period-list" id="detailPeriods"></div>
    </div>
</div>

<div class="trial-modal" id="payModal" aria-hidden="true">
    <div class="trial-modal-backdrop js-close-modal"></div>
    <div class="trial-modal-box wide">
        <button type="button" class="modal-x js-close-modal">×</button>
        <h2>Pilih Periode Dibayar</h2>
        <p id="payCustomer">-</p>

        <div class="group-period-list selectable" id="payPeriods"></div>

        <div class="pay-total-box">
            <span>Total Dipilih</span>
            <b id="payTotal">Rp 0</b>
        </div>

        <a href="#" id="paySelectedLink" class="trial-btn pay-submit-link disabled">Bayar Periode Dipilih</a>
    </div>
</div>

@push('scripts')
<script id="tagihan-gabungan-script-v1">
(function(){
    const detailModal = document.getElementById('detailModal');
    const payModal = document.getElementById('payModal');

    const detailCustomer = document.getElementById('detailCustomer');
    const detailPhone = document.getElementById('detailPhone');
    const detailPackage = document.getElementById('detailPackage');
    const detailBillingDay = document.getElementById('detailBillingDay');
    const detailTotal = document.getElementById('detailTotal');
    const detailPeriods = document.getElementById('detailPeriods');

    const payCustomer = document.getElementById('payCustomer');
    const payPeriods = document.getElementById('payPeriods');
    const payTotal = document.getElementById('payTotal');
    const paySelectedLink = document.getElementById('paySelectedLink');

    function rupiah(v){
        return 'Rp ' + Number(v || 0).toLocaleString('id-ID');
    }

    function esc(str){
        return String(str || '')
            .replaceAll('&','&amp;')
            .replaceAll('<','&lt;')
            .replaceAll('>','&gt;')
            .replaceAll('"','&quot;')
            .replaceAll("'",'&#039;');
    }

    function openModal(modal){
        modal.classList.add('open');
        modal.setAttribute('aria-hidden','false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(){
        [detailModal, payModal].forEach(function(modal){
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden','true');
        });
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.js-close-modal').forEach(function(btn){
        btn.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape') closeModal();
    });

    function periodHtml(d, withCheck){
        const danger = d.status === 'Nunggak' ? 'danger' : 'warning';
        return `
            <label class="group-period-item ${withCheck ? 'can-check' : ''}">
                ${withCheck ? `<input type="checkbox" class="pay-check" value="${d.id}" data-amount="${d.remaining}" checked>` : ''}
                <span>
                    <b>${esc(d.period_label)}</b>
                    <small>${esc(d.invoice_number)} · Jatuh tempo ${esc(d.due_date || '-')}</small>
                </span>
                <em>
                    <strong>${rupiah(d.remaining)}</strong>
                    <i class="${danger}">${esc(d.status)}</i>
                </em>
            </label>
        `;
    }

    document.querySelectorAll('.js-group-detail').forEach(function(btn){
        btn.addEventListener('click', function(){
            const data = JSON.parse(btn.dataset.json || '{}');

            detailCustomer.textContent = data.customer_name || '-';
            detailPhone.textContent = data.customer_phone || '-';
            detailPackage.textContent = data.package_name || '-';
            detailBillingDay.textContent = data.billing_day || '-';
            detailTotal.textContent = rupiah(data.total_amount || 0);

            detailPeriods.innerHTML = (data.details || []).map(function(d){
                return periodHtml(d, false);
            }).join('');

            openModal(detailModal);
        });
    });

    function refreshPayTotal(){
        let ids = [];
        let total = 0;

        payPeriods.querySelectorAll('.pay-check:checked').forEach(function(chk){
            ids.push(chk.value);
            total += Number(chk.dataset.amount || 0);
        });

        payTotal.textContent = rupiah(total);

        if(ids.length){
            paySelectedLink.classList.remove('disabled');
            paySelectedLink.href = '{{ url('/kasir/bayar-gabungan') }}?invoice_ids=' + encodeURIComponent(ids.join(','));
        }else{
            paySelectedLink.classList.add('disabled');
            paySelectedLink.href = '#';
        }
    }

    document.querySelectorAll('.js-group-pay').forEach(function(btn){
        btn.addEventListener('click', function(){
            const data = JSON.parse(btn.dataset.json || '{}');

            payCustomer.textContent = data.customer_name || '-';

            payPeriods.innerHTML = (data.details || []).map(function(d){
                return periodHtml(d, true);
            }).join('');

            payPeriods.querySelectorAll('.pay-check').forEach(function(chk){
                chk.addEventListener('change', refreshPayTotal);
            });

            refreshPayTotal();
            openModal(payModal);
        });
    });

    paySelectedLink.addEventListener('click', function(e){
        if(paySelectedLink.classList.contains('disabled')){
            e.preventDefault();
            return;
        }

        const ok = confirm('Proses pembayaran untuk periode yang dipilih?');
        if(!ok){
            e.preventDefault();
        }
    });
})();
</script>
@endpush
@endsection
