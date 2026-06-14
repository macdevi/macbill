@extends('collector.percobaan.layout')
@section('title','Status Pelanggan')

@section('content')
@include('collector.percobaan._page_style')

@php
    $money = fn($v) => 'Rp '.number_format((float) $v,0,',','.');
    $payClass = function ($status) {
        return match ($status) {
            'Nunggak' => 'status-nunggak',
            'Belum Bayar' => 'status-belum-bayar',
            'Bayar Awal' => 'status-bayar-awal',
            'Lunas' => 'status-lunas',
            default => 'status-muted',
        };
    };

    $connClass = function ($status) {
        return match ($status) {
            'Aktif' => 'conn-active',
            'Offline' => 'conn-offline',
            default => 'conn-muted',
        };
    };
@endphp

<section class="page-head">
    <h1>Status Pelanggan</h1>
    <p>Semua pelanggan billing, status pembayaran, koneksi PPPoE, alamat, nomor HP, dan detail pelanggan.</p>
</section>

<form class="trial-filter" method="GET">
    <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama, HP, alamat, PPPoE, atau paket...">
    <button type="submit">Cari</button>
</form>

<div class="trial-card-page">
    <div class="trial-table-wrap">
        <table class="trial-table status-customer-table">
            <thead>
                <tr>
                    <th>Nama Pelanggan</th>
                    <th>Status</th>
                    <th>Koneksi</th>
                    <th>Alamat</th>
                    <th>No HP</th>
                    <th>Detail</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    @php
                        $detail = [
                            'id' => $row->id,
                            'name' => $row->name,
                            'phone' => $row->phone,
                            'address' => $row->address,
                            'package_name' => $row->package_name,
                            'package_speed' => $row->package_speed,
                            'billing_day' => $row->billing_day,
                            'monthly_price' => $row->monthly_price,
                            'customer_status' => $row->customer_status,
                            'payment_status' => $row->payment_status,
                            'connection_status' => $row->connection_status,
                            'pppoe_username' => $row->pppoe_username,
                            'pppoe_online_status' => $row->pppoe_online_status,
                            'pppoe_online_at' => $row->pppoe_online_at,
                            'pppoe_last_seen_at' => $row->pppoe_last_seen_at,
                            'pppoe_remote_address' => $row->pppoe_remote_address,
                            'pppoe_caller_id' => $row->pppoe_caller_id,
                            'pppoe_uptime' => $row->pppoe_uptime,
                            'invoice_count' => $row->invoice_count,
                            'open_invoice_count' => $row->open_invoice_count,
                            'latest_invoice_number' => $row->latest_invoice_number,
                            'latest_invoice_period' => $row->latest_invoice_period,
                            'latest_invoice_due_date' => $row->latest_invoice_due_date,
                            'latest_invoice_amount' => $row->latest_invoice_amount,
                            'latest_invoice_paid_amount' => $row->latest_invoice_paid_amount,
                            'latest_invoice_status' => $row->latest_invoice_status,
                            'latest_invoice_paid_at' => $row->latest_invoice_paid_at,
                        ];
                    @endphp

                    <tr class="status-customer-row">
                        <td class="customer-cell">
                            <b>{{ $row->name }}</b>
                            <small>{{ $row->package_name }}</small>
                        </td>

                        <td>
                            <span class="status-pill {{ $payClass($row->payment_status) }}">
                                {{ $row->payment_status }}
                            </span>
                        </td>

                        <td>
                            <span class="connection-pill {{ $connClass($row->connection_status) }}">
                                {{ $row->connection_status }}
                            </span>
                        </td>

                        <td class="address-cell">{{ $row->address }}</td>
                        <td>{{ $row->phone }}</td>

                        <td class="action-cell">
                            <button type="button" class="action-icon js-customer-detail" data-json='@json($detail)' title="Detail pelanggan">
                                <svg viewBox="0 0 24 24">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">Data pelanggan tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="trial-modal" id="customerDetailModal" aria-hidden="true">
    <div class="trial-modal-backdrop js-close-customer-modal"></div>
    <div class="trial-modal-box wide customer-detail-box">
        <button type="button" class="modal-x js-close-customer-modal">×</button>

        <h2 id="detailName">Detail Pelanggan</h2>
        <p id="detailSub">-</p>

        <div class="detail-grid">
            <div><span>Status Bayar</span><b id="detailPaymentStatus">-</b></div>
            <div><span>Koneksi</span><b id="detailConnectionStatus">-</b></div>
            <div><span>Paket</span><b id="detailPackage">-</b></div>
            <div><span>Nominal</span><b id="detailMonthlyPrice">-</b></div>
            <div><span>Billing Day</span><b id="detailBillingDay">-</b></div>
            <div><span>Status Customer</span><b id="detailCustomerStatus">-</b></div>
        </div>

        <div class="detail-section">
            <h3>Kontak & Alamat</h3>
            <p><b>HP:</b> <span id="detailPhone">-</span></p>
            <p><b>Alamat:</b> <span id="detailAddress">-</span></p>
        </div>

        <div class="detail-section">
            <h3>PPPoE</h3>
            <p><b>Username:</b> <span id="detailPppoeUsername">-</span></p>
            <p><b>Status MikroTik:</b> <span id="detailPppoeRaw">-</span></p>
            <p><b>Remote IP:</b> <span id="detailRemoteIp">-</span></p>
            <p><b>Caller ID:</b> <span id="detailCallerId">-</span></p>
            <p><b>Uptime:</b> <span id="detailUptime">-</span></p>
            <p><b>Online At:</b> <span id="detailOnlineAt">-</span></p>
            <p><b>Last Seen:</b> <span id="detailLastSeen">-</span></p>
        </div>

        <div class="detail-section">
            <h3>Invoice Terakhir</h3>
            <p><b>Total Invoice:</b> <span id="detailInvoiceCount">-</span></p>
            <p><b>Invoice Terbuka:</b> <span id="detailOpenInvoiceCount">-</span></p>
            <p><b>No Invoice:</b> <span id="detailInvoiceNumber">-</span></p>
            <p><b>Periode:</b> <span id="detailInvoicePeriod">-</span></p>
            <p><b>Jatuh Tempo:</b> <span id="detailInvoiceDue">-</span></p>
            <p><b>Status:</b> <span id="detailInvoiceStatus">-</span></p>
            <p><b>Nominal:</b> <span id="detailInvoiceAmount">-</span></p>
            <p><b>Dibayar:</b> <span id="detailInvoicePaidAmount">-</span></p>
            <p><b>Tanggal Bayar:</b> <span id="detailInvoicePaidAt">-</span></p>
        </div>
    </div>
</div>

@push('scripts')
<script id="status-pelanggan-script-v1">
(function(){
    const modal = document.getElementById('customerDetailModal');

    function rupiah(value){
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function setText(id, value){
        const el = document.getElementById(id);
        if(el) el.textContent = value || '-';
    }

    function openModal(){
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(){
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.js-close-customer-modal').forEach(function(btn){
        btn.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape') closeModal();
    });

    document.querySelectorAll('.js-customer-detail').forEach(function(btn){
        btn.addEventListener('click', function(){
            const d = JSON.parse(btn.dataset.json || '{}');

            setText('detailName', d.name || 'Detail Pelanggan');
            setText('detailSub', 'ID #' + (d.id || '-') + ' · ' + (d.phone || '-'));

            setText('detailPaymentStatus', d.payment_status);
            setText('detailConnectionStatus', d.connection_status);
            setText('detailPackage', (d.package_name || '-') + ' / ' + (d.package_speed || '-'));
            setText('detailMonthlyPrice', rupiah(d.monthly_price));
            setText('detailBillingDay', d.billing_day);
            setText('detailCustomerStatus', d.customer_status);

            setText('detailPhone', d.phone);
            setText('detailAddress', d.address);

            setText('detailPppoeUsername', d.pppoe_username);
            setText('detailPppoeRaw', d.pppoe_online_status);
            setText('detailRemoteIp', d.pppoe_remote_address);
            setText('detailCallerId', d.pppoe_caller_id);
            setText('detailUptime', d.pppoe_uptime);
            setText('detailOnlineAt', d.pppoe_online_at);
            setText('detailLastSeen', d.pppoe_last_seen_at);

            setText('detailInvoiceCount', d.invoice_count);
            setText('detailOpenInvoiceCount', d.open_invoice_count);
            setText('detailInvoiceNumber', d.latest_invoice_number);
            setText('detailInvoicePeriod', d.latest_invoice_period);
            setText('detailInvoiceDue', d.latest_invoice_due_date);
            setText('detailInvoiceStatus', d.latest_invoice_status);
            setText('detailInvoiceAmount', rupiah(d.latest_invoice_amount));
            setText('detailInvoicePaidAmount', rupiah(d.latest_invoice_paid_amount));
            setText('detailInvoicePaidAt', d.latest_invoice_paid_at);

            openModal();
        });
    });
})();
</script>
@endpush
@endsection
