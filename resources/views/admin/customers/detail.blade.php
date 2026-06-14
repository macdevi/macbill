@extends('layouts.neo')
@section('title','Detail Pelanggan')
@section('content')
@php
    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $badge = function ($status) {
        return match ($status) {
            'Lunas' => 'green',
            'Bayar Awal' => 'blue',
            'Nunggak' => 'red',
            'Belum Bayar' => 'yellow',
            default => 'blue',
        };
    };

    $phoneDigits = preg_replace('/\D+/', '', (string) $customer->phone);
    $waNumber = $phoneDigits;
    if (str_starts_with($waNumber, '0')) {
        $waNumber = '62'.substr($waNumber, 1);
    }

    $hasCustomerPoint = $customer->latitude && $customer->longitude;
    $hasOdpPoint = $customer->odpMaster && $customer->odpMaster->latitude && $customer->odpMaster->longitude;
    $canShowRoute = $hasCustomerPoint && $hasOdpPoint;

    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'edit' => '<svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>',
            'odp' => '<svg viewBox="0 0 24 24"><path d="M9 18 3 21V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>',
            'phone' => '<svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.7.6 2.5a2 2 0 0 1-.5 2.1L8 9.5a16 16 0 0 0 6.5 6.5l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.6.5 2.5.6A2 2 0 0 1 22 16.9z"/></svg>',
            'map' => '<svg viewBox="0 0 24 24"><path d="M9 18 3 21V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>',
            'print' => '<svg viewBox="0 0 24 24"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>',
            'eye' => '<svg viewBox="0 0 24 24"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>',
            'route' => '<svg viewBox="0 0 24 24"><circle cx="6" cy="6" r="3"/><circle cx="18" cy="18" r="3"/><path d="M8.5 8.5 15.5 15.5"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

@if($hasCustomerPoint)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endif

<style>
.customer-profile{background:linear-gradient(135deg,#1d4ed8,#0891b2);border-radius:28px;padding:22px;color:#fff;margin-bottom:12px;box-shadow:0 18px 44px rgba(16,24,40,.08)}
.customer-profile span{display:block;color:#e0f2fe;font-size:13px;font-weight:800}
.customer-profile b{display:block;margin-top:6px;font-size:30px;line-height:1;letter-spacing:-.07em}
.customer-profile p{margin:10px 0 0;color:#e0f2fe;font-size:14px;line-height:1.45}
.customer-detail-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:10px}
.customer-detail-card{background:#fff;border:1px solid #e4eaf3;border-radius:22px;padding:15px;box-shadow:0 10px 24px rgba(16,24,40,.055)}
.customer-detail-card .label{color:#667085;font-size:12px;font-weight:800}
.customer-detail-card .value{margin-top:7px;color:#101828;font-size:18px;font-weight:950;letter-spacing:-.04em}
.customer-detail-card .small{margin-top:5px;color:#667085;font-size:12px;line-height:1.4}
.customer-section-head{margin:16px 2px 9px}
.customer-section-head b{color:#101828;font-size:16px;letter-spacing:-.04em}
.customer-section-head span{display:block;margin-top:2px;color:#667085;font-size:12px}
.customer-actions-inline{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
.customer-route-card{background:#fff;border:1px solid #e4eaf3;border-radius:24px;padding:14px;box-shadow:0 10px 24px rgba(16,24,40,.055);margin-bottom:10px}
#customer-route-map{height:380px;width:100%;border-radius:18px;background:#eef5ff}
.customer-route-note{margin-top:10px;border-radius:16px;padding:10px;background:#f8fbff;border:1px solid #e4eaf3;color:#667085;font-size:13px;line-height:1.45}
@media(max-width:980px){.customer-detail-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:760px){
    .customer-profile{border-radius:22px;padding:18px}
    .customer-profile b{font-size:25px}
    .customer-detail-grid{gap:8px}
    .customer-detail-card{border-radius:18px;padding:12px}
    .customer-detail-card .value{font-size:16px}
    .customer-route-card{border-radius:20px;padding:12px}
    #customer-route-map{height:320px}
}

/* ===== SATELLITE MAP CONTROL POLISH ===== */
.leaflet-control-layers{
    border:1px solid #dbe5f2 !important;
    border-radius:14px !important;
    box-shadow:0 12px 28px rgba(16,24,40,.16) !important;
    overflow:hidden !important;
}
.leaflet-control-layers-expanded{
    padding:9px 11px !important;
    color:#101828 !important;
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif !important;
    font-size:12px !important;
    font-weight:800 !important;
}
.leaflet-control-layers label{
    margin:5px 0 !important;
}
.leaflet-popup-content-wrapper{
    border-radius:16px !important;
}

</style>

<div class="pagehead">
    <div>
        <h1>Detail Pelanggan</h1>
        <p>Informasi pelanggan, ODP, port, titik lokasi, jalur kabel, tagihan, dan riwayat pembayaran.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/customers') }}">{!! $i('back') !!}Kembali</a>
        <a class="btn" href="{{ url('/admin/customers/'.$customer->id.'/edit') }}">{!! $i('edit') !!}Edit</a>
        @if($customer->odp_id)
            <a class="btn light" href="{{ url('/admin/odps/'.$customer->odp_id.'/ports') }}">{!! $i('odp') !!}Port ODP</a>
        @endif
    </div>
</div>

<div class="customer-profile">
    <span>Pelanggan</span>
    <b>{{ $customer->name }}</b>
    <p>{{ $customer->address ?: 'Alamat belum diisi.' }}</p>
</div>

<div class="customer-detail-grid">
    <div class="customer-detail-card">
        <div class="label">Status</div>
        <div class="value"><span class="badge {{ $customer->status === 'active' ? 'green' : 'red' }}">{{ $customer->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Paket</div>
        <div class="value">{{ $customer->package?->name ?: '-' }}</div>
        <div class="small">{{ $customer->package?->speed ?: '' }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Harga Bulanan</div>
        <div class="value">{{ $money($customer->monthly_price) }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Tanggal Tagihan</div>
        <div class="value">Tanggal {{ $customer->billing_day }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">ODP</div>
        <div class="value">{{ $customer->odpMaster?->name ?: ($customer->odp ?: '-') }}</div>
        <div class="small">{{ $customer->odpMaster?->location ?: '' }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Port ODP</div>
        <div class="value">{{ $customer->port_number ? 'Port '.$customer->port_number : '-' }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Jarak Kabel</div>
        <div class="value">{{ $customer->cable_distance_m ? number_format($customer->cable_distance_m, 0, ',', '.') . ' m' : '-' }}</div>
        <div class="small">{{ $canShowRoute ? 'Jalur aktif' : 'Titik belum lengkap' }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">No HP / WhatsApp</div>
        <div class="value">{{ $customer->phone ?: '-' }}</div>
        @if($customer->phone)
            <div class="customer-actions-inline" style="margin-top:8px">
                <a class="btn light" target="_blank" href="https://wa.me/{{ $waNumber }}">{!! $i('phone') !!}WhatsApp</a>
            </div>
        @endif
    </div>
</div>

<div class="customer-section-head">
    <b>Peta Lokasi dan Jalur Kabel</b>
    <span>Titik pelanggan, titik ODP, dan estimasi jalur kabel.</span>
</div>

@if($hasCustomerPoint)
    <div class="customer-route-card">
        <div id="customer-route-map"></div>
        <div class="customer-route-note">
            @if($canShowRoute)
                Jalur kabel ditampilkan sebagai estimasi garis lurus dari ODP ke pelanggan. Untuk jalur kabel mengikuti tiang atau belokan jalan, nanti dapat ditambah mode waypoint.
            @else
                Titik pelanggan sudah ada, tetapi titik ODP belum lengkap. Edit ODP untuk mengisi koordinat ODP.
            @endif
        </div>
    </div>
@else
    <div class="customer-detail-card" style="margin-bottom:10px">
        <div class="label">Peta</div>
        <div class="value">Belum ada titik pelanggan</div>
        <div class="small">Edit pelanggan, lalu pilih titik lokasi dari peta.</div>
    </div>
@endif


<div class="customer-section-head">
    <b>Akun PPPoE Mikrotik</b>
    <span>Data akun PPPoE yang nanti dipakai untuk sync ke Mikrotik Secret.</span>
</div>

<div class="customer-detail-grid">
    <div class="customer-detail-card">
        <div class="label">Router</div>
        <div class="value">{{ $customer->mikrotikRouter?->name ?: '-' }}</div>
        <div class="small">{{ $customer->mikrotikRouter?->host ?: '' }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">PPPoE Profile</div>
        <div class="value">{{ $customer->mikrotikPppoeProfile?->name ?: '-' }}</div>
        <div class="small">{{ $customer->mikrotikPppoeProfile?->rate_limit ?: '' }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">PPPoE Username</div>
        <div class="value">{{ $customer->pppoe_username ?: '-' }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Status Sync</div>
        <div class="value">
            <span class="badge {{ ($customer->mikrotik_sync_status ?? 'Belum Sync') === 'Tersinkron' ? 'green' : (($customer->mikrotik_sync_status ?? 'Belum Sync') === 'Gagal Sync' ? 'red' : 'yellow') }}">
                {{ $customer->mikrotik_sync_status ?: 'Belum Sync' }}
            </span>
        </div>
        <div class="small">{{ $customer->mikrotik_sync_message ?: '' }}</div>
    </div>
</div>

<div class="customer-section-head">
    <b>Tagihan Terakhir</b>
    <span>Riwayat tagihan pelanggan maksimal 20 data terakhir.</span>
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total: <b>{{ $invoices->count() }}</b></span>
        <span>Geser kanan untuk aksi</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th>Invoice</th>
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
                        <td class="neo-strong">{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->period }}</td>
                        <td>{{ $invoice->due_date }}</td>
                        <td class="neo-money">{{ $money($invoice->amount) }}</td>
                        <td><span class="badge {{ $badge($invoice->status) }}">{{ $invoice->status }}</span></td>
                        <td>
                            <div class="customer-actions-inline">
                                <a class="btn light icon" title="Detail" href="{{ url('/admin/invoices/'.$invoice->id.'/detail') }}">{!! $i('eye') !!}</a>
                                <a class="btn light icon" title="Cetak" target="_blank" href="{{ url('/admin/invoices/'.$invoice->id.'/print') }}">{!! $i('print') !!}</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada tagihan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="customer-section-head">
    <b>Riwayat Pembayaran</b>
    <span>Transaksi pembayaran pelanggan maksimal 20 data terakhir.</span>
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total: <b>{{ $payments->count() }}</b></span>
        <span>Riwayat pembayaran pelanggan</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nominal</th>
                    <th>Metode</th>
                    <th>Catatan</th>
                </tr>
            </thead>

            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at }}</td>
                        <td class="neo-money">{{ $money($payment->amount) }}</td>
                        <td>{{ $payment->method ?: '-' }}</td>
                        <td>{{ $payment->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($hasCustomerPoint)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof L === 'undefined') return;

    const customerLat = parseFloat('{{ $customer->latitude }}');
    const customerLng = parseFloat('{{ $customer->longitude }}');

    const hasOdp = {{ $hasOdpPoint ? 'true' : 'false' }};
    const odpLat = hasOdp ? parseFloat('{{ $customer->odpMaster?->latitude }}') : null;
    const odpLng = hasOdp ? parseFloat('{{ $customer->odpMaster?->longitude }}') : null;

    const map = L.map('customer-route-map').setView([customerLat, customerLng], 17);

    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: 'Tiles © Esri — Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community'
    });

    const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 22,
        attribution: '&copy; OpenStreetMap'
    });

    satelliteLayer.addTo(map);

    L.control.layers({
        'Satelit': satelliteLayer,
        'Peta Jalan': streetLayer
    }, null, {
        collapsed: true
    }).addTo(map);

    const customerMarker = L.marker([customerLat, customerLng]).addTo(map);
    customerMarker.bindPopup('Pelanggan: {{ addslashes($customer->name) }}');

    if (hasOdp) {
        const odpMarker = L.marker([odpLat, odpLng]).addTo(map);
        odpMarker.bindPopup('ODP: {{ addslashes($customer->odpMaster?->name) }}');

        L.polyline([[odpLat, odpLng], [customerLat, customerLng]], {
            weight: 4
        }).addTo(map);

        const bounds = L.latLngBounds([[odpLat, odpLng], [customerLat, customerLng]]);
        map.fitBounds(bounds, {padding: [32, 32], maxZoom: 18});
    }

    setTimeout(function () {
        map.invalidateSize();
    }, 300);
});
</script>
@endif

<div class="customer-section-head">
    <b>Sync PPPoE Secret ke Mikrotik</b>
    <span>Gunakan tombol ini setelah Router, Profile, Username, dan Password PPPoE sudah terisi.</span>
</div>

<div class="customer-detail-grid">
    <div class="customer-detail-card">
        <div class="label">Router</div>
        <div class="value">{{ $customer->mikrotikRouter?->name ?: '-' }}</div>
        <div class="small">{{ $customer->mikrotikRouter?->host ?: '' }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Profile</div>
        <div class="value">{{ $customer->mikrotikPppoeProfile?->name ?: ($customer->mikrotikPppoeSecret?->profile ?: '-') }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Secret Name</div>
        <div class="value">{{ $customer->pppoe_username ?: '-' }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Status Sync</div>
        <div class="value">
            <span class="badge {{ ($customer->mikrotik_sync_status ?? 'Belum Sync') === 'Tersinkron' ? 'green' : (($customer->mikrotik_sync_status ?? 'Belum Sync') === 'Gagal Sync' ? 'red' : 'yellow') }}">
                {{ $customer->mikrotik_sync_status ?: 'Belum Sync' }}
            </span>
        </div>
        <div class="small">{{ $customer->mikrotik_synced_at ? $customer->mikrotik_synced_at->format('d/m/Y H:i') : '' }}</div>
    </div>
</div>

<div style="margin:12px 0 18px;display:flex;gap:8px;flex-wrap:wrap">
    <form method="POST" action="{{ url('/admin/customers/'.$customer->id.'/sync-pppoe-secret') }}" onsubmit="return confirm('Sync PPPoE Secret pelanggan ini ke Mikrotik?')">
        @csrf
        <button class="btn" type="submit">Sync PPPoE Secret</button>
    </form>

    <a class="btn light" href="{{ url('/admin/customers/'.$customer->id.'/edit') }}">Edit Data PPPoE</a>
</div>


<div class="customer-section-head">
    <b>Status Online PPPoE</b>
    <span>Status berdasarkan pembacaan terakhir dari Mikrotik PPP Active.</span>
</div>

<div class="customer-detail-grid">
    <div class="customer-detail-card">
        <div class="label">Status Online</div>
        <div class="value">
            <span class="badge {{ ($customer->pppoe_online_status ?? 'Unknown') === 'Online' ? 'green' : (($customer->pppoe_online_status ?? 'Unknown') === 'Offline' ? 'red' : 'yellow') }}">
                {{ $customer->pppoe_online_status ?: 'Unknown' }}
            </span>
        </div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Remote Address</div>
        <div class="value">{{ $customer->pppoe_remote_address ?: '-' }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Caller ID</div>
        <div class="value">{{ $customer->pppoe_caller_id ?: '-' }}</div>
    </div>

    <div class="customer-detail-card">
        <div class="label">Uptime</div>
        <div class="value">{{ $customer->pppoe_uptime ?: '-' }}</div>
        <div class="small">{{ $customer->pppoe_last_seen_at ? 'Last seen '.$customer->pppoe_last_seen_at->format('d/m/Y H:i') : '' }}</div>
    </div>
</div>

@endsection
