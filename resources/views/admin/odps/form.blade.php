@extends('layouts.neo')
@section('title', $odp->exists ? 'Edit ODP' : 'Tambah ODP')
@section('content')
@php
    $defaultLat = old('latitude', $odp->latitude ?: -8.209567);
    $defaultLng = old('longitude', $odp->longitude ?: 112.658531);

    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'save' => '<svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>',
            'map' => '<svg viewBox="0 0 24 24"><path d="M9 18 3 21V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>',
            'pin' => '<svg viewBox="0 0 24 24"><path d="M12 21s7-5.2 7-12a7 7 0 0 0-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>',
            'port' => '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>',
            'locate' => '<svg viewBox="0 0 24 24"><path d="M12 2v3"/><path d="M12 19v3"/><path d="M2 12h3"/><path d="M19 12h3"/><circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
.odp-form-layout{
    display:grid;
    grid-template-columns:1fr 320px;
    gap:12px;
    align-items:start;
}
.odp-form-card,
.odp-side-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:24px;
    padding:18px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.odp-side-card{
    position:sticky;
    top:82px;
}
.odp-section-title{
    display:flex;
    align-items:center;
    gap:8px;
    margin:16px 0 12px;
    color:#101828;
    font-size:15px;
    font-weight:950;
    letter-spacing:-.035em;
}
.odp-section-title:first-child{
    margin-top:0;
}
.odp-note{
    background:#eff6ff;
    border:1px solid #b2ddff;
    color:#175cd3;
    border-radius:18px;
    padding:12px;
    font-size:13px;
    line-height:1.45;
    margin-bottom:14px;
}
.odp-map-wrap{
    border:1px solid #dbe5f2;
    border-radius:20px;
    overflow:hidden;
    background:#f8fafc;
}
#odp-location-map{
    height:330px;
    width:100%;
    background:#eef5ff;
}
.odp-map-tools{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    padding:10px;
    border-top:1px solid #e4eaf3;
    background:#fff;
}
.odp-map-help{
    padding:10px;
    background:#f8fbff;
    border-top:1px solid #e4eaf3;
    color:#667085;
    font-size:12px;
    line-height:1.45;
}
.odp-map-fallback{
    display:none;
    padding:12px;
    border:1px solid #fedf89;
    background:#fffaeb;
    color:#b54708;
    border-radius:16px;
    font-size:13px;
    line-height:1.45;
    margin-top:10px;
}
.odp-side-card b{
    display:block;
    color:#101828;
    font-size:15px;
    letter-spacing:-.035em;
}
.odp-side-card p{
    margin:7px 0 0;
    color:#667085;
    font-size:12px;
    line-height:1.45;
}
.odp-side-list{
    margin-top:12px;
    display:grid;
    gap:8px;
}
.odp-side-item{
    display:flex;
    justify-content:space-between;
    gap:8px;
    border:1px solid #eef2f7;
    background:#f8fbff;
    border-radius:14px;
    padding:9px;
    font-size:12px;
    color:#667085;
}
.odp-side-item strong{
    color:#101828;
    text-align:right;
}
.odp-form-actions{
    display:flex;
    justify-content:flex-end;
    gap:8px;
    margin-top:14px;
}
@media(max-width:980px){
    .odp-form-layout{
        grid-template-columns:1fr;
    }
    .odp-side-card{
        position:static;
    }
}
@media(max-width:760px){
    .odp-form-card,
    .odp-side-card{
        border-radius:20px;
        padding:14px;
    }
    #odp-location-map{
        height:300px;
    }
    .odp-form-actions{
        flex-direction:column;
    }
    .odp-form-actions .btn,
    .odp-map-tools .btn{
        width:100%;
        justify-content:center;
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
        <h1>{{ $odp->exists ? 'Edit ODP' : 'Tambah ODP' }}</h1>
        <p>Kelola nama ODP, lokasi, jumlah port, koordinat, dan titik lokasi pada peta.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/odps') }}">{!! $i('back') !!}Kembali</a>
        <a class="btn light" href="{{ url('/admin/odps-map') }}">{!! $i('map') !!}Peta ODP</a>
    </div>
</div>

@if($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif

<div class="odp-form-layout">
    <form class="odp-form-card" method="POST" action="{{ $odp->exists ? url('/admin/odps/'.$odp->id) : url('/admin/odps') }}">
        @csrf
        @if($odp->exists) @method('PUT') @endif

        <div class="odp-note">
            <b>Catatan:</b> klik peta untuk memilih titik ODP. Marker juga bisa digeser. Latitude dan longitude akan terisi otomatis.
        </div>

        <div class="odp-section-title">{!! $i('port') !!}Data ODP</div>

        <div class="formgrid">
            <div class="field">
                <label>Nama ODP</label>
                <input class="input" name="name" value="{{ old('name', $odp->name) }}" placeholder="Contoh: ODP-01 Perumahan A" required>
            </div>

            <div class="field">
                <label>Jumlah Port</label>
                <input class="input" type="number" name="port_count" min="1" max="128" value="{{ old('port_count', $odp->port_count ?: 8) }}" required>
            </div>

            <div class="field full">
                <label>Lokasi / Keterangan</label>
                <input class="input" name="location" value="{{ old('location', $odp->location) }}" placeholder="Contoh: Depan rumah Pak RT / Tiang 04">
            </div>

            <div class="field">
                <label>Status ODP</label>
                <select class="select" name="status">
                    <option value="active" @selected(old('status', $odp->status ?: 'active') === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status', $odp->status) === 'inactive')>Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="odp-section-title">{!! $i('pin') !!}Pilih Titik Lokasi</div>

        <div class="odp-map-wrap">
            <div id="odp-location-map"></div>

            <div class="odp-map-tools">
                <button class="btn light" type="button" id="btn-use-current-location">{!! $i('locate') !!}Lokasi Saya</button>
                <button class="btn light" type="button" id="btn-use-default-location">{!! $i('pin') !!}Titik Default</button>
                <a class="btn light" target="_blank" id="btn-open-google-map" href="#">{!! $i('map') !!}Google Maps</a>
            </div>

            <div class="odp-map-help">
                Klik pada peta untuk menentukan titik ODP. Untuk akurasi lebih baik, zoom peta terlebih dahulu lalu geser marker ke posisi tiang/box ODP.
            </div>
        </div>

        <div id="odp-map-fallback" class="odp-map-fallback">
            Peta gagal dimuat. Cek koneksi internet browser. Latitude dan longitude tetap bisa diisi manual.
        </div>

        <div class="formgrid" style="margin-top:14px">
            <div class="field">
                <label>Latitude</label>
                <input class="input" id="latitude" name="latitude" value="{{ $defaultLat }}" placeholder="-8.209567">
            </div>

            <div class="field">
                <label>Longitude</label>
                <input class="input" id="longitude" name="longitude" value="{{ $defaultLng }}" placeholder="112.658531">
            </div>
        </div>

        <div class="odp-form-actions">
            <a class="btn light" href="{{ url('/admin/odps') }}">Batal</a>
            <button class="btn" type="submit">{!! $i('save') !!}Simpan ODP</button>
        </div>
    </form>

    <aside class="odp-side-card">
        <b>Ringkasan ODP</b>
        <p>Koordinat dipakai untuk peta ODP. Pilih titik dari peta agar data lokasi lebih akurat.</p>

        <div class="odp-side-list">
            <div class="odp-side-item">
                <span>Status</span>
                <strong>{{ old('status', $odp->status ?: 'active') === 'active' ? 'Aktif' : 'Nonaktif' }}</strong>
            </div>

            <div class="odp-side-item">
                <span>Jumlah Port</span>
                <strong>{{ old('port_count', $odp->port_count ?: 8) }}</strong>
            </div>

            <div class="odp-side-item">
                <span>Latitude</span>
                <strong id="side-latitude">{{ $defaultLat }}</strong>
            </div>

            <div class="odp-side-item">
                <span>Longitude</span>
                <strong id="side-longitude">{{ $defaultLng }}</strong>
            </div>
        </div>

        @if($odp->exists)
            <div style="margin-top:12px">
                <a class="btn light" href="{{ url('/admin/odps/'.$odp->id.'/ports') }}">{!! $i('port') !!}Atur Port</a>
            </div>
        @endif
    </aside>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const sideLat = document.getElementById('side-latitude');
    const sideLng = document.getElementById('side-longitude');
    const googleBtn = document.getElementById('btn-open-google-map');
    const fallback = document.getElementById('odp-map-fallback');

    const defaultLat = -8.209567;
    const defaultLng = 112.658531;

    function parseNumber(value, fallbackValue) {
        const n = parseFloat(String(value || '').replace(',', '.'));
        return Number.isFinite(n) ? n : fallbackValue;
    }

    let currentLat = parseNumber(latInput.value, defaultLat);
    let currentLng = parseNumber(lngInput.value, defaultLng);

    function updateCoordinate(lat, lng, moveMap = false) {
        currentLat = parseFloat(lat);
        currentLng = parseFloat(lng);

        latInput.value = currentLat.toFixed(7);
        lngInput.value = currentLng.toFixed(7);

        sideLat.textContent = latInput.value;
        sideLng.textContent = lngInput.value;

        googleBtn.href = 'https://www.google.com/maps?q=' + latInput.value + ',' + lngInput.value;

        if (window.odpMarker) {
            window.odpMarker.setLatLng([currentLat, currentLng]);
        }

        if (moveMap && window.odpMap) {
            window.odpMap.setView([currentLat, currentLng], 18);
        }
    }

    updateCoordinate(currentLat, currentLng, false);

    if (typeof L === 'undefined') {
        fallback.style.display = 'block';
        return;
    }

    const map = L.map('odp-location-map').setView([currentLat, currentLng], 17);
    window.odpMap = map;

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

    const marker = L.marker([currentLat, currentLng], {
        draggable: true
    }).addTo(map);

    window.odpMarker = marker;

    marker.bindPopup('Titik ODP. Geser marker atau klik peta untuk ubah lokasi.').openPopup();

    map.on('click', function (event) {
        updateCoordinate(event.latlng.lat, event.latlng.lng, false);
        marker.openPopup();
    });

    marker.on('dragend', function () {
        const pos = marker.getLatLng();
        updateCoordinate(pos.lat, pos.lng, false);
    });

    latInput.addEventListener('change', function () {
        updateCoordinate(
            parseNumber(latInput.value, defaultLat),
            parseNumber(lngInput.value, defaultLng),
            true
        );
    });

    lngInput.addEventListener('change', function () {
        updateCoordinate(
            parseNumber(latInput.value, defaultLat),
            parseNumber(lngInput.value, defaultLng),
            true
        );
    });

    document.getElementById('btn-use-default-location').addEventListener('click', function () {
        updateCoordinate(defaultLat, defaultLng, true);
    });

    document.getElementById('btn-use-current-location').addEventListener('click', function () {
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung fitur lokasi.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                updateCoordinate(position.coords.latitude, position.coords.longitude, true);
            },
            function () {
                alert('Lokasi tidak bisa diambil. Izinkan akses lokasi pada browser atau pilih titik manual dari peta.');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });

    setTimeout(function () {
        map.invalidateSize();
    }, 300);
});
</script>
@endsection
