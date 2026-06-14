@extends('layouts.neo')
@section('title','Peta Jaringan')
@section('content')
@php
    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $odpPoints = $odps->filter(function ($odp) {
        return $odp->latitude && $odp->longitude;
    })->map(function ($odp) {
        $used = (int) $odp->customers_count;
        $total = (int) $odp->port_count;

        return [
            'id' => $odp->id,
            'name' => $odp->name,
            'location' => $odp->location ?: '-',
            'lat' => (float) $odp->latitude,
            'lng' => (float) $odp->longitude,
            'used' => $used,
            'total' => $total,
            'available' => max(0, $total - $used),
            'status' => $odp->status,
            'edit_url' => url('/admin/odps/'.$odp->id.'/edit'),
            'ports_url' => url('/admin/odps/'.$odp->id.'/ports'),
            'google_url' => 'https://www.google.com/maps?q='.$odp->latitude.','.$odp->longitude,
        ];
    })->values();

    $customerPoints = $customers->filter(function ($customer) {
        return $customer->latitude && $customer->longitude;
    })->map(function ($customer) {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone ?: '-',
            'address' => $customer->address ?: '-',
            'package' => $customer->package?->name ?: '-',
            'odp_id' => $customer->odp_id,
            'odp_name' => $customer->odpMaster?->name ?: ($customer->odp ?: '-'),
            'port' => $customer->port_number,
            'lat' => (float) $customer->latitude,
            'lng' => (float) $customer->longitude,
            'status' => $customer->status,
            'detail_url' => url('/admin/customers/'.$customer->id.'/detail'),
            'edit_url' => url('/admin/customers/'.$customer->id.'/edit'),
            'google_url' => 'https://www.google.com/maps?q='.$customer->latitude.','.$customer->longitude,
        ];
    })->values();

    $routes = $customers->filter(function ($customer) {
        return $customer->latitude
            && $customer->longitude
            && $customer->odpMaster
            && $customer->odpMaster->latitude
            && $customer->odpMaster->longitude;
    })->map(function ($customer) {
        $path = null;

        if ($customer->cable_path_json) {
            $decoded = json_decode($customer->cable_path_json, true);

            if (is_array($decoded) && count($decoded) >= 2) {
                $path = collect($decoded)->map(function ($p) {
                    return [
                        'lat' => (float) ($p['lat'] ?? 0),
                        'lng' => (float) ($p['lng'] ?? 0),
                    ];
                })->filter(function ($p) {
                    return $p['lat'] && $p['lng'];
                })->values()->all();
            }
        }

        if (!$path || count($path) < 2) {
            $path = [
                [
                    'lat' => (float) $customer->odpMaster->latitude,
                    'lng' => (float) $customer->odpMaster->longitude,
                ],
                [
                    'lat' => (float) $customer->latitude,
                    'lng' => (float) $customer->longitude,
                ],
            ];
        }

        return [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'odp_name' => $customer->odpMaster?->name ?: '-',
            'port' => $customer->port_number,
            'distance_m' => (int) ($customer->cable_distance_m ?: 0),
            'path' => $path,
            'detail_url' => url('/admin/customers/'.$customer->id.'/detail'),
        ];
    })->values();

    $odpsWithoutPoint = $odps->filter(function ($odp) {
        return !$odp->latitude || !$odp->longitude;
    });

    $customersWithoutPoint = $customers->filter(function ($customer) {
        return !$customer->latitude || !$customer->longitude;
    });

    $customersWithoutRoute = $customers->filter(function ($customer) {
        return $customer->latitude
            && $customer->longitude
            && (!$customer->odpMaster || !$customer->odpMaster->latitude || !$customer->odpMaster->longitude);
    });

    $totalCable = $routes->sum('distance_m');

    $i = function ($name) {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>',
            'odp' => '<svg viewBox="0 0 24 24"><path d="M9 18 3 21V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>',
            'plus' => '<svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
            'user' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
            'route' => '<svg viewBox="0 0 24 24"><circle cx="6" cy="6" r="3"/><circle cx="18" cy="18" r="3"/><path d="M8.5 8.5 15.5 15.5"/></svg>',
            'map' => '<svg viewBox="0 0 24 24"><path d="M9 18 3 21V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>',
            'edit' => '<svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
.network-hero{
    background:linear-gradient(135deg,#1d4ed8,#0891b2);
    border-radius:28px;
    padding:22px;
    color:#fff;
    margin-bottom:12px;
    box-shadow:0 18px 44px rgba(16,24,40,.08);
}
.network-hero span{
    display:block;
    color:#e0f2fe;
    font-size:13px;
    font-weight:800;
}
.network-hero b{
    display:block;
    margin-top:6px;
    font-size:31px;
    line-height:1;
    letter-spacing:-.07em;
}
.network-hero p{
    margin:10px 0 0;
    color:#e0f2fe;
    font-size:14px;
    line-height:1.45;
    max-width:780px;
}
.network-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:10px;
    margin-bottom:10px;
}
.network-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:22px;
    padding:15px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.network-card .label{
    color:#667085;
    font-size:12px;
    font-weight:800;
}
.network-card .value{
    margin-top:7px;
    color:#101828;
    font-size:22px;
    font-weight:950;
    letter-spacing:-.055em;
}
.network-map-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:24px;
    padding:12px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
    margin-bottom:10px;
}
.network-map-toolbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:10px;
}
.network-map-toolbar b{
    color:#101828;
    font-size:16px;
    letter-spacing:-.04em;
}
.network-map-toolbar span{
    display:block;
    color:#667085;
    font-size:12px;
    margin-top:2px;
}
.network-toggle{
    display:flex;
    gap:7px;
    flex-wrap:wrap;
}
.network-toggle button{
    border:1px solid #dbe5f2;
    background:#f8fbff;
    color:#175cd3;
    border-radius:14px;
    height:34px;
    padding:0 11px;
    font-size:12px;
    font-weight:900;
    cursor:pointer;
}
.network-toggle button.active{
    background:linear-gradient(135deg,#2563eb,#06b6d4);
    color:#fff;
    border-color:transparent;
}
#networkMap{
    height:620px;
    border-radius:20px;
    border:1px solid #e4eaf3;
    background:#eef5ff;
    overflow:hidden;
}
.network-note{
    margin-top:10px;
    background:#f8fbff;
    border:1px solid #e4eaf3;
    color:#667085;
    border-radius:16px;
    padding:10px;
    font-size:13px;
    line-height:1.45;
}
.network-section-head{
    margin:16px 2px 9px;
}
.network-section-head b{
    color:#101828;
    font-size:16px;
    letter-spacing:-.04em;
}
.network-section-head span{
    display:block;
    color:#667085;
    font-size:12px;
    margin-top:2px;
}
.network-warning-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:10px;
    margin-bottom:10px;
}
.network-warning{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:22px;
    padding:14px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.network-warning b{
    display:block;
    color:#101828;
    font-size:14px;
    letter-spacing:-.03em;
}
.network-warning p{
    margin:5px 0 10px;
    color:#667085;
    font-size:12px;
    line-height:1.4;
}
.network-warning-list{
    display:flex;
    gap:6px;
    flex-wrap:wrap;
}
.net-popup{
    min-width:220px;
    font-family:Inter,ui-sans-serif,system-ui;
}
.net-popup b{
    display:block;
    font-size:14px;
    color:#101828;
    margin-bottom:4px;
}
.net-popup span{
    display:block;
    font-size:12px;
    color:#667085;
    margin-top:2px;
}
.net-popup .links{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    margin-top:8px;
}
.net-popup .links a{
    color:#175cd3;
    text-decoration:none;
    font-weight:800;
    font-size:12px;
}
.net-legend{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-top:10px;
    color:#667085;
    font-size:12px;
}
.net-legend span{
    display:inline-flex;
    align-items:center;
    gap:6px;
}
.net-dot{
    width:10px;
    height:10px;
    border-radius:999px;
    display:inline-block;
}
.net-dot.odp{background:#2563eb}
.net-dot.customer{background:#16a34a}
.net-dot.route{background:#0891b2}
@media(max-width:980px){
    .network-grid{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
    .network-warning-grid{
        grid-template-columns:1fr;
    }
}
@media(max-width:760px){
    .network-hero{
        border-radius:22px;
        padding:18px;
    }
    .network-hero b{
        font-size:25px;
    }
    .network-grid{
        gap:8px;
    }
    .network-card{
        border-radius:18px;
        padding:12px;
    }
    .network-card .value{
        font-size:19px;
    }
    .network-map-card{
        border-radius:20px;
        padding:10px;
    }
    #networkMap{
        height:520px;
        border-radius:17px;
    }
    .network-toggle button{
        flex:1;
    }
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
        <h1>Peta Jaringan</h1>
        <p>Gabungan titik ODP, titik pelanggan, dan jalur kabel pelanggan.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/dashboard') }}">{!! $i('home') !!}Home</a>
        <a class="btn light" href="{{ url('/admin/odps') }}">{!! $i('odp') !!}ODP</a>
        <a class="btn" href="{{ url('/admin/odps/create') }}">{!! $i('plus') !!}Tambah ODP</a>
    </div>
</div>

<div class="network-hero">
    <span>Network Map</span>
    <b>Peta Jalur ODP ke Pelanggan</b>
    <p>Gunakan peta ini untuk melihat persebaran ODP, pelanggan, dan estimasi jalur kabel. Data akan makin akurat setelah titik ODP dan titik pelanggan dilengkapi.</p>
</div>

<div class="network-grid">
    <div class="network-card">
        <div class="label">ODP Bertitik</div>
        <div class="value">{{ $odpPoints->count() }}/{{ $odps->count() }}</div>
    </div>

    <div class="network-card">
        <div class="label">Pelanggan Bertitik</div>
        <div class="value">{{ $customerPoints->count() }}/{{ $customers->count() }}</div>
    </div>

    <div class="network-card">
        <div class="label">Jalur Aktif</div>
        <div class="value">{{ $routes->count() }}</div>
    </div>

    <div class="network-card">
        <div class="label">Estimasi Total Kabel</div>
        <div class="value">{{ $totalCable ? number_format($totalCable, 0, ',', '.') . ' m' : '-' }}</div>
    </div>
</div>

<div class="network-map-card">
    <div class="network-map-toolbar">
        <div>
            <b>Peta Jaringan Gabungan</b>
            <span>Filter layer peta sesuai kebutuhan.</span>
        </div>

        <div class="network-toggle">
            <button type="button" class="active" data-layer-toggle="odp">ODP</button>
            <button type="button" class="active" data-layer-toggle="customer">Pelanggan</button>
            <button type="button" class="active" data-layer-toggle="route">Jalur Kabel</button>
        </div>
    </div>

    <div id="networkMap"></div>

    <div class="net-legend">
        <span><i class="net-dot odp"></i>ODP</span>
        <span><i class="net-dot customer"></i>Pelanggan</span>
        <span><i class="net-dot route"></i>Jalur kabel</span>
    </div>

    <div class="network-note">
        Garis jalur kabel saat ini adalah estimasi rute berdasarkan titik ODP dan titik pelanggan. Untuk jalur mengikuti tiang/belokan jalan, nanti bisa ditambah mode waypoint manual.
    </div>
</div>

<div class="network-section-head">
    <b>Data yang Perlu Dilengkapi</b>
    <span>Lengkapi titik lokasi agar peta jaringan semakin sinkron.</span>
</div>

<div class="network-warning-grid">
    <div class="network-warning">
        <b>ODP Belum Ada Titik</b>
        <p>ODP ini belum memiliki latitude/longitude.</p>
        <div class="network-warning-list">
            @forelse($odpsWithoutPoint->take(12) as $odp)
                <a class="badge yellow" href="{{ url('/admin/odps/'.$odp->id.'/edit') }}">{{ $odp->name }}</a>
            @empty
                <span class="badge green">Lengkap</span>
            @endforelse
            @if($odpsWithoutPoint->count() > 12)
                <span class="badge blue">+{{ $odpsWithoutPoint->count() - 12 }} lagi</span>
            @endif
        </div>
    </div>

    <div class="network-warning">
        <b>Pelanggan Belum Ada Titik</b>
        <p>Pelanggan ini belum punya titik lokasi rumah.</p>
        <div class="network-warning-list">
            @forelse($customersWithoutPoint->take(12) as $customer)
                <a class="badge yellow" href="{{ url('/admin/customers/'.$customer->id.'/edit') }}">{{ $customer->name }}</a>
            @empty
                <span class="badge green">Lengkap</span>
            @endforelse
            @if($customersWithoutPoint->count() > 12)
                <span class="badge blue">+{{ $customersWithoutPoint->count() - 12 }} lagi</span>
            @endif
        </div>
    </div>

    <div class="network-warning">
        <b>Route Belum Tembus</b>
        <p>Pelanggan sudah punya titik, tetapi ODP belum lengkap atau belum dipilih.</p>
        <div class="network-warning-list">
            @forelse($customersWithoutRoute->take(12) as $customer)
                <a class="badge red" href="{{ url('/admin/customers/'.$customer->id.'/edit') }}">{{ $customer->name }}</a>
            @empty
                <span class="badge green">Aman</span>
            @endforelse
            @if($customersWithoutRoute->count() > 12)
                <span class="badge blue">+{{ $customersWithoutRoute->count() - 12 }} lagi</span>
            @endif
        </div>
    </div>
</div>

<div class="network-section-head">
    <b>Daftar ODP</b>
    <span>Ringkasan kapasitas port dan titik lokasi.</span>
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total ODP: <b>{{ $odps->count() }}</b></span>
        <span>Geser kanan untuk aksi</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th>ODP</th>
                    <th>Lokasi</th>
                    <th>Koordinat</th>
                    <th>Port</th>
                    <th>Pelanggan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach($odps as $odp)
                    @php
                        $used = (int) $odp->customers_count;
                        $total = (int) $odp->port_count;
                        $available = max(0, $total - $used);
                        $portClass = $available <= 0 ? 'red' : ($available <= 2 ? 'yellow' : 'green');
                    @endphp

                    <tr>
                        <td class="neo-strong">{{ $odp->name }}</td>
                        <td>{{ $odp->location ?: '-' }}</td>
                        <td>
                            @if($odp->latitude && $odp->longitude)
                                {{ $odp->latitude }}, {{ $odp->longitude }}
                            @else
                                <span class="badge yellow">Belum ada titik</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $portClass }}">{{ $used }}/{{ $total }}</span>
                            <span class="muted">Sisa {{ $available }}</span>
                        </td>
                        <td>{{ $used }}</td>
                        <td>
                            <div class="customer-actions-inline" style="display:flex;gap:6px;flex-wrap:wrap">
                                <a class="btn light icon" title="Atur Port" href="{{ url('/admin/odps/'.$odp->id.'/ports') }}">{!! $i('odp') !!}</a>
                                <a class="btn light icon" title="Edit" href="{{ url('/admin/odps/'.$odp->id.'/edit') }}">{!! $i('edit') !!}</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof L === 'undefined') return;

    const odpPoints = @json($odpPoints);
    const customerPoints = @json($customerPoints);
    const routes = @json($routes);

    const defaultCenter = [-8.209567, 112.658531];

    const map = L.map('networkMap').setView(defaultCenter, 14);

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

    const odpLayer = L.layerGroup().addTo(map);
    const customerLayer = L.layerGroup().addTo(map);
    const routeLayer = L.layerGroup().addTo(map);

    const bounds = [];

    const odpIcon = L.divIcon({
        className: '',
        html: '<div style="width:26px;height:26px;border-radius:10px;background:#2563eb;border:3px solid white;box-shadow:0 8px 18px rgba(37,99,235,.35)"></div>',
        iconSize: [26, 26],
        iconAnchor: [13, 13]
    });

    const customerIcon = L.divIcon({
        className: '',
        html: '<div style="width:20px;height:20px;border-radius:999px;background:#16a34a;border:3px solid white;box-shadow:0 8px 16px rgba(22,163,74,.28)"></div>',
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    odpPoints.forEach(function (p) {
        const marker = L.marker([p.lat, p.lng], {icon: odpIcon}).addTo(odpLayer);

        const html = `
            <div class="net-popup">
                <b>${escapeHtml(p.name)}</b>
                <span>${escapeHtml(p.location)}</span>
                <span>Port: ${p.used}/${p.total} · Sisa ${p.available}</span>
                <span>Status: ${p.status === 'active' ? 'Aktif' : 'Nonaktif'}</span>
                <div class="links">
                    <a href="${p.ports_url}">Atur Port</a>
                    <a href="${p.edit_url}">Edit ODP</a>
                    <a target="_blank" href="${p.google_url}">Google Maps</a>
                </div>
            </div>
        `;

        marker.bindPopup(html);
        bounds.push([p.lat, p.lng]);
    });

    customerPoints.forEach(function (p) {
        const marker = L.marker([p.lat, p.lng], {icon: customerIcon}).addTo(customerLayer);

        const html = `
            <div class="net-popup">
                <b>${escapeHtml(p.name)}</b>
                <span>Paket: ${escapeHtml(p.package)}</span>
                <span>ODP: ${escapeHtml(p.odp_name)}${p.port ? ' · Port ' + p.port : ''}</span>
                <span>${escapeHtml(p.address)}</span>
                <div class="links">
                    <a href="${p.detail_url}">Detail</a>
                    <a href="${p.edit_url}">Edit</a>
                    <a target="_blank" href="${p.google_url}">Google Maps</a>
                </div>
            </div>
        `;

        marker.bindPopup(html);
        bounds.push([p.lat, p.lng]);
    });

    routes.forEach(function (r) {
        const latlngs = (r.path || [])
            .filter(function (p) {
                return p.lat && p.lng;
            })
            .map(function (p) {
                return [p.lat, p.lng];
            });

        if (latlngs.length < 2) return;

        const line = L.polyline(latlngs, {
            weight: 3,
            opacity: 0.75
        }).addTo(routeLayer);

        const distanceText = r.distance_m ? r.distance_m.toLocaleString('id-ID') + ' m' : '-';

        line.bindPopup(`
            <div class="net-popup">
                <b>Jalur Kabel</b>
                <span>ODP: ${escapeHtml(r.odp_name)}</span>
                <span>Pelanggan: ${escapeHtml(r.customer_name)}</span>
                <span>Port: ${r.port || '-'}</span>
                <span>Estimasi jarak: ${distanceText}</span>
                <div class="links">
                    <a href="${r.detail_url}">Detail Pelanggan</a>
                </div>
            </div>
        `);
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, {padding: [32, 32], maxZoom: 18});
    }

    const layerMap = {
        odp: odpLayer,
        customer: customerLayer,
        route: routeLayer
    };

    document.querySelectorAll('[data-layer-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const key = btn.getAttribute('data-layer-toggle');
            const layer = layerMap[key];

            if (!layer) return;

            if (map.hasLayer(layer)) {
                map.removeLayer(layer);
                btn.classList.remove('active');
            } else {
                map.addLayer(layer);
                btn.classList.add('active');
            }
        });
    });

    setTimeout(function () {
        map.invalidateSize();
    }, 350);

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
});
</script>
@endsection
