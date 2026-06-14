@extends('layouts.neo')
@section('title', $customer->exists ? 'Edit Pelanggan' : 'Tambah Pelanggan')
@section('content')
@php
    $selectedSecret = $customer->mikrotikPppoeSecret ?? null;
    $mikrotikRouters = $mikrotikRouters ?? \App\Models\MikrotikRouter::query()->where('status','active')->orderBy('name')->get();
    $pppoeProfiles = $pppoeProfiles ?? \App\Models\MikrotikPppoeProfile::query()->with('router')->orderBy('name')->get();
    $pppoeSecrets = $pppoeSecrets ?? \App\Models\MikrotikPppoeSecret::query()->with('router')->orderBy('name')->get();
    $mikrotikRouters = $mikrotikRouters ?? collect();
    $pppoeProfiles = $pppoeProfiles ?? collect();
    $settings = $appSettings ?? \App\Services\SettingService::allMerged();
    $money = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');

    $selectedOdp = null;
    if ($customer->odp_id) {
        $selectedOdp = $odps->firstWhere('id', $customer->odp_id);
    }

    $defaultLat = old('latitude', $customer->latitude ?: ($selectedOdp?->latitude ?: -8.209567));
    $defaultLng = old('longitude', $customer->longitude ?: ($selectedOdp?->longitude ?: 112.658531));

    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'save' => '<svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>',
            'odp' => '<svg viewBox="0 0 24 24"><path d="M9 18 3 21V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>',
            'user' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
            'wifi' => '<svg viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>',
            'pin' => '<svg viewBox="0 0 24 24"><path d="M12 21s7-5.2 7-12a7 7 0 0 0-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>',
            'route' => '<svg viewBox="0 0 24 24"><circle cx="6" cy="6" r="3"/><circle cx="18" cy="18" r="3"/><path d="M8.5 8.5 15.5 15.5"/></svg>',
            'locate' => '<svg viewBox="0 0 24 24"><path d="M12 2v3"/><path d="M12 19v3"/><path d="M2 12h3"/><path d="M19 12h3"/><circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };

    $odpPortData = $odps->map(function ($odp) {
        $used = \App\Models\Customer::query()
            ->where('odp_id', $odp->id)
            ->whereNotNull('port_number')
            ->orderBy('port_number')
            ->get(['id', 'name', 'port_number'])
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'port_number' => (int) $customer->port_number,
                ];
            })
            ->values();

        return [
            'id' => $odp->id,
            'name' => $odp->name,
            'location' => $odp->location,
            'port_count' => (int) $odp->port_count,
            'latitude' => $odp->latitude,
            'longitude' => $odp->longitude,
            'used' => $used,
        ];
    })->values();
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
.customer-form-layout{display:grid;grid-template-columns:1fr 320px;gap:12px;align-items:start}
.customer-form-card,.customer-side-card{background:#fff;border:1px solid #e4eaf3;border-radius:24px;padding:18px;box-shadow:0 10px 24px rgba(16,24,40,.055)}
.customer-side-card{position:sticky;top:82px}
.customer-section{margin-bottom:16px}
.customer-section:last-child{margin-bottom:0}
.customer-section-title{display:flex;align-items:center;gap:8px;margin-bottom:12px;color:#101828;font-size:15px;font-weight:950;letter-spacing:-.035em}
.customer-side-card b{display:block;color:#101828;font-size:15px;letter-spacing:-.035em}
.customer-side-card p{margin:7px 0 0;color:#667085;font-size:12px;line-height:1.45}
.customer-side-list{margin-top:12px;display:grid;gap:8px}
.customer-side-item{display:flex;justify-content:space-between;gap:8px;border:1px solid #eef2f7;background:#f8fbff;border-radius:14px;padding:9px;font-size:12px;color:#667085}
.customer-side-item strong{color:#101828;text-align:right}
.customer-form-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:14px}
.port-status,.route-status{margin-top:7px;border-radius:14px;padding:9px;font-size:12px;line-height:1.4;background:#f8fafc;color:#667085;border:1px solid #eef2f7}
.port-status.ok,.route-status.ok{background:#ecfdf3;border-color:#abefc6;color:#027a48}
.port-status.warn,.route-status.warn{background:#fffaeb;border-color:#fedf89;color:#b54708}
.port-status.err,.route-status.err{background:#fef3f2;border-color:#fecdca;color:#b42318}
.customer-map-wrap{border:1px solid #dbe5f2;border-radius:20px;overflow:hidden;background:#f8fafc}
#customer-location-map{height:360px;width:100%;background:#eef5ff}
.customer-map-tools{display:flex;gap:8px;flex-wrap:wrap;padding:10px;border-top:1px solid #e4eaf3;background:#fff}
.customer-map-help{padding:10px;background:#f8fbff;border-top:1px solid #e4eaf3;color:#667085;font-size:12px;line-height:1.45}
.customer-map-fallback{display:none;padding:12px;border:1px solid #fedf89;background:#fffaeb;color:#b54708;border-radius:16px;font-size:13px;line-height:1.45;margin-top:10px}
@media(max-width:980px){.customer-form-layout{grid-template-columns:1fr}.customer-side-card{position:static}}
@media(max-width:760px){
    .customer-form-card,.customer-side-card{border-radius:20px;padding:14px}
    #customer-location-map{height:310px}
    .customer-form-actions{flex-direction:column}
    .customer-form-actions .btn,.customer-map-tools .btn{width:100%;justify-content:center}
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
        <h1>{{ $customer->exists ? 'Edit Pelanggan' : 'Tambah Pelanggan' }}</h1>
        <p>Input data pelanggan, pilih ODP, pilih titik lokasi pelanggan, dan lihat jalur kabel otomatis.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/customers') }}">{!! $i('back') !!}Kembali</a>
        <a class="btn light" href="{{ url('/admin/odps') }}">{!! $i('odp') !!}ODP</a>
    </div>
</div>

@if($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif

<div class="customer-form-layout">
    <form class="customer-form-card" method="POST" action="{{ $customer->exists ? url('/admin/customers/'.$customer->id) : url('/admin/customers') }}">
        @csrf
        @if($customer->exists) @method('PUT') @endif

        <input type="hidden" name="cable_path_json" id="cable_path_json" value="{{ old('cable_path_json', $customer->cable_path_json) }}">
        <input type="hidden" name="cable_distance_m" id="cable_distance_m" value="{{ old('cable_distance_m', $customer->cable_distance_m) }}">

        <div class="customer-section">
            <div class="customer-section-title">{!! $i('user') !!}Data Pelanggan</div>

            <div class="formgrid">
                <div class="field">
                    <label>Nama Pelanggan</label>
                    <input class="input" name="name" value="{{ old('name', $customer->name) }}" placeholder="Nama pelanggan" required>
                </div>

                <div class="field">
                    <label>No HP / WhatsApp</label>
                    <input class="input" name="phone" value="{{ old('phone', $customer->phone) }}" placeholder="08xxxxxxxxxx">
                </div>

                <div class="field full">
                    <label>Alamat</label>
                    <textarea class="textarea" name="address" placeholder="Alamat lengkap pelanggan">{{ old('address', $customer->address) }}</textarea>
                </div>
            </div>
        </div>

        <div class="customer-section">
            <div class="customer-section-title">{!! $i('odp') !!}ODP dan Port</div>

            <div class="formgrid">
                <div class="field">
                    <label>ODP</label>
                    <select class="select" name="odp_id" id="odp_id">
                        <option value="">- Pilih ODP -</option>
                        @foreach($odps as $odp)
                            <option value="{{ $odp->id }}" @selected((string)old('odp_id', $customer->odp_id) === (string)$odp->id)>
                                {{ $odp->name }}{{ $odp->location ? ' · '.$odp->location : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div id="route_status" class="route-status">Pilih ODP dan titik pelanggan untuk menampilkan jalur kabel.</div>
                </div>

                <div class="field">
                    <label>Nomor Port</label>
                    <select class="select" name="port_number" id="port_number">
                        <option value="">- Pilih ODP dulu -</option>
                    </select>
                    <div id="port_status" class="port-status">Pilih ODP untuk melihat port kosong dan port terpakai.</div>
                </div>
            </div>
        </div>

        <div class="customer-section">
            <div class="customer-section-title">{!! $i('pin') !!}Titik Lokasi Pelanggan dan Jalur Kabel</div>

            <div class="customer-map-wrap">
                <div id="customer-location-map"></div>

                <div class="customer-map-tools">
                    <button class="btn light" type="button" id="btn-use-current-location">{!! $i('locate') !!}Lokasi Saya</button>
                    <button class="btn light" type="button" id="btn-use-odp-location">{!! $i('odp') !!}Titik ODP</button>
                    <button class="btn light" type="button" id="btn-use-default-location">{!! $i('pin') !!}Titik Default</button>
                </div>

                <div class="customer-map-help">
                    Klik peta untuk menentukan lokasi rumah pelanggan. Garis biru menunjukkan estimasi jalur kabel lurus dari ODP ke pelanggan. Untuk jalur detail per tiang/belokan, nanti bisa ditambah mode waypoint.
                </div>
            </div>

            <div id="customer-map-fallback" class="customer-map-fallback">
                Peta gagal dimuat. Latitude dan longitude tetap bisa diisi manual.
            </div>

            <div class="formgrid" style="margin-top:14px">
                <div class="field">
                    <label>Latitude Pelanggan</label>
                    <input class="input" id="latitude" name="latitude" value="{{ $defaultLat }}" placeholder="-8.209567">
                </div>

                <div class="field">
                    <label>Longitude Pelanggan</label>
                    <input class="input" id="longitude" name="longitude" value="{{ $defaultLng }}" placeholder="112.658531">
                </div>
            </div>
        </div>

        
        <div class="customer-section">
            <div class="customer-section-title">{!! $i('wifi') !!}Akun PPPoE Mikrotik</div>

            <div class="formgrid">
                <div class="field">
                    <label>Router Mikrotik</label>
                    <select class="select" name="mikrotik_router_id" id="mikrotik_router_id">
                        <option value="">- Belum pakai Mikrotik -</option>
                        @foreach($mikrotikRouters as $router)
                            <option value="{{ $router->id }}" @selected((string)old('mikrotik_router_id', $customer->mikrotik_router_id) === (string)$router->id)>
                                {{ $router->name }} · {{ $router->host }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field full">
                    <label>Ambil dari Secret Mikrotik</label>

                    <div class="pppoe-autofind">
                        <input
                            class="input"
                            type="text"
                            id="pppoe_secret_search"
                            autocomplete="off"
                            placeholder="Ketik nama Secret, contoh: Pelanggan001 / USER001"
                            value="{{ $selectedSecret ? ($selectedSecret->name.' · '.($selectedSecret->profile ?: '-').' · '.($selectedSecret->router?->name ?: '-')) : '' }}"
                        >

                        <input
                            type="hidden"
                            name="mikrotik_pppoe_secret_id"
                            id="mikrotik_pppoe_secret_id"
                            value="{{ old('mikrotik_pppoe_secret_id', $customer->mikrotik_pppoe_secret_id) }}"
                        >

                        <div id="pppoe_secret_results" class="pppoe-secret-results"></div>
                    </div>

                    <div class="route-status" style="margin-top:8px">
                        Ketik minimal 2 huruf/angka. Pilihan dibatasi 20 hasil agar tidak panjang di HP.
                    </div>
                </div>


                <div class="field">
                    <label>PPPoE Profile</label>
                    <select class="select" name="mikrotik_pppoe_profile_id" id="mikrotik_pppoe_profile_id">
                        <option value="">- Pilih router dulu -</option>
                        @foreach($pppoeProfiles as $profile)
                            <option value="{{ $profile->id }}" data-router="{{ $profile->mikrotik_router_id }}" @selected((string)old('mikrotik_pppoe_profile_id', $customer->mikrotik_pppoe_profile_id) === (string)$profile->id)>
                                {{ $profile->name }}{{ $profile->rate_limit ? ' · '.$profile->rate_limit : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>PPPoE Username / Secret Name</label>
                    <input class="input" name="pppoe_username" id="pppoe_username" value="{{ old('pppoe_username', $customer->pppoe_username) }}" placeholder="contoh: {{ Str::slug($customer->name ?: 'nama-pelanggan') }}">
                </div>

                <div class="field">
                    <label>PPPoE Password</label>
                    <input class="input" type="password" name="pppoe_password" id="pppoe_password" placeholder="{{ $customer->exists && $customer->pppoe_password ? 'Kosongkan jika tidak diganti' : 'Password PPPoE pelanggan' }}">
                </div>

                <div class="field">
                    <label>Status Sync Mikrotik</label>
                    <select class="select" name="mikrotik_sync_status">
                        @foreach(['Belum Sync','Tersinkron','Gagal Sync','Perlu Update'] as $status)
                            <option value="{{ $status }}" @selected(old('mikrotik_sync_status', $customer->mikrotik_sync_status ?: 'Belum Sync') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Catatan Sync</label>
                    <input class="input" name="mikrotik_sync_message" value="{{ old('mikrotik_sync_message', $customer->mikrotik_sync_message) }}" placeholder="Otomatis terisi saat sync nanti">
                </div>
            </div>

            <div class="route-status">
                Tahap ini hanya menyimpan akun PPPoE. Tombol sync ke Mikrotik dibuat pada tahap berikutnya.
            </div>
        </div>

<div class="customer-section">
            <div class="customer-section-title">{!! $i('wifi') !!}Paket dan Tagihan</div>

            <div class="formgrid">
                <div class="field">
                    <label>Paket Internet</label>
                    <select class="select" name="package_id">
                        <option value="">- Pilih Paket -</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" @selected((string)old('package_id', $customer->package_id) === (string)$package->id)>
                                {{ $package->name }}{{ $package->speed ? ' · '.$package->speed : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Tanggal Tagihan</label>
                    <input class="input" type="number" name="billing_day" min="1" max="31" value="{{ old('billing_day', $customer->billing_day ?: ($settings['default_billing_day'] ?? 1)) }}" required>
                </div>

                <div class="field">
                    <label>Harga Bulanan</label>
                    <input class="input" type="number" name="monthly_price" min="0" value="{{ old('monthly_price', $customer->monthly_price ?: 0) }}" required>
                </div>

                <div class="field">
                    <label>Status Pelanggan</label>
                    <select class="select" name="status">
                        <option value="active" @selected(old('status', $customer->status ?: 'active') === 'active')>Aktif</option>
                        <option value="inactive" @selected(old('status', $customer->status) === 'inactive')>Nonaktif</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="customer-form-actions">
            <a class="btn light" href="{{ url('/admin/customers') }}">Batal</a>
            <button class="btn" type="submit">{!! $i('save') !!}Simpan Pelanggan</button>
        </div>
    </form>

    <aside class="customer-side-card">
        <b>Ringkasan Jalur</b>
        <p>ODP dan titik pelanggan harus punya koordinat agar jalur kabel bisa dihitung.</p>

        <div class="customer-side-list">
            <div class="customer-side-item">
                <span>Latitude</span>
                <strong id="side-latitude">{{ $defaultLat }}</strong>
            </div>

            <div class="customer-side-item">
                <span>Longitude</span>
                <strong id="side-longitude">{{ $defaultLng }}</strong>
            </div>

            <div class="customer-side-item">
                <span>Jarak Kabel</span>
                <strong id="side-distance">{{ $customer->cable_distance_m ? number_format($customer->cable_distance_m,0,',','.') . ' m' : '-' }}</strong>
            </div>

            <div class="customer-side-item">
                <span>Route</span>
                <strong id="side-route">Belum aktif</strong>
            </div>
        </div>
    </aside>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>




<style>
.pppoe-autofind{position:relative}
.pppoe-secret-results{
    display:none;
    position:absolute;
    left:0;
    right:0;
    top:calc(100% + 6px);
    z-index:60;
    background:#fff;
    border:1px solid #d9e3f0;
    border-radius:16px;
    box-shadow:0 18px 44px rgba(16,24,40,.16);
    max-height:260px;
    overflow:auto;
}
.pppoe-secret-item{
    width:100%;
    border:0;
    background:#fff;
    text-align:left;
    padding:12px 14px;
    border-bottom:1px solid #eef2f7;
    cursor:pointer;
}
.pppoe-secret-item:hover{background:#eff6ff}
.pppoe-secret-item b{
    display:block;
    color:#101828;
    font-size:14px;
    line-height:1.25;
}
.pppoe-secret-item span{
    display:block;
    color:#667085;
    font-size:12px;
    margin-top:3px;
}
.pppoe-secret-empty{
    padding:12px 14px;
    color:#667085;
    font-size:13px;
}
@media(max-width:760px){
    .pppoe-secret-results{
        max-height:220px;
        border-radius:14px;
    }
    .pppoe-secret-item{
        padding:12px;
    }
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const routerSelect = document.getElementById('mikrotik_router_id');
    const profileSelect = document.getElementById('mikrotik_pppoe_profile_id');
    const searchInput = document.getElementById('pppoe_secret_search');
    const secretIdInput = document.getElementById('mikrotik_pppoe_secret_id');
    const resultsBox = document.getElementById('pppoe_secret_results');
    const usernameInput = document.getElementById('pppoe_username');
    const passwordInput = document.getElementById('pppoe_password');

    if (!searchInput || !secretIdInput || !resultsBox) return;

    let timer = null;

    function hideResults() {
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
    }

    function showMessage(text) {
        resultsBox.innerHTML = '<div class="pppoe-secret-empty">' + text + '</div>';
        resultsBox.style.display = 'block';
    }

    function filterProfilesByRouter() {
        if (!routerSelect || !profileSelect) return;

        const routerId = routerSelect.value;

        Array.from(profileSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = routerId && option.dataset.router !== routerId;
        });

        const selected = profileSelect.options[profileSelect.selectedIndex];

        if (selected && selected.hidden) {
            profileSelect.value = '';
        }
    }

    function selectProfileBySecret(secret) {
        if (!profileSelect || !secret.profile) return;

        if (secret.profile_id) {
            profileSelect.value = secret.profile_id;
            return;
        }

        const match = Array.from(profileSelect.options).find(function (option) {
            return option.dataset.router == secret.router_id && option.dataset.name == secret.profile;
        });

        if (match) {
            profileSelect.value = match.value;
        }
    }

    function applySecret(secret) {
        secretIdInput.value = secret.id;
        searchInput.value = secret.label || secret.name;

        if (routerSelect && secret.router_id) {
            routerSelect.value = secret.router_id;
        }

        if (usernameInput) {
            usernameInput.value = secret.name || '';
        }

        if (passwordInput && secret.password) {
            passwordInput.value = secret.password;
        }

        filterProfilesByRouter();
        selectProfileBySecret(secret);
        hideResults();
    }

    async function searchSecret() {
        const q = searchInput.value.trim();

        secretIdInput.value = '';

        if (q.length < 2) {
            hideResults();
            return;
        }

        const params = new URLSearchParams();
        params.set('q', q);

        if (routerSelect && routerSelect.value) {
            params.set('router_id', routerSelect.value);
        }

        showMessage('Mencari Secret...');

        try {
            const res = await fetch('/admin/pppoe-secrets/search?' + params.toString(), {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const json = await res.json();
            const data = json.data || [];

            if (!data.length) {
                showMessage('Secret tidak ditemukan.');
                return;
            }

            resultsBox.innerHTML = data.map(function (secret, index) {
                const disabled = secret.disabled === 'true' ? ' · Disabled' : '';
                const remote = secret.remote_address ? ' · ' + secret.remote_address : '';
                const comment = secret.comment ? ' · ' + secret.comment : '';

                return `
                    <button type="button" class="pppoe-secret-item" data-index="${index}">
                        <b>${secret.name}</b>
                        <span>${secret.profile || '-'} · ${secret.router_name || '-'}${remote}${disabled}${comment}</span>
                    </button>
                `;
            }).join('');

            resultsBox.querySelectorAll('.pppoe-secret-item').forEach(function (button) {
                button.addEventListener('click', function () {
                    applySecret(data[Number(button.dataset.index)]);
                });
            });

            resultsBox.style.display = 'block';
        } catch (e) {
            showMessage('Gagal mencari Secret.');
        }
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(searchSecret, 300);
    });

    searchInput.addEventListener('focus', function () {
        if (searchInput.value.trim().length >= 2) {
            searchSecret();
        }
    });

    document.addEventListener('click', function (e) {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            hideResults();
        }
    });

    if (routerSelect) {
        routerSelect.addEventListener('change', function () {
            filterProfilesByRouter();
            secretIdInput.value = '';
            searchInput.value = '';
        });
    }

    filterProfilesByRouter();
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const odpSelect = document.querySelector('select[name="odp_id"], #odp_id');
    const portSelect = document.querySelector('select[name="port_number"], #port_number');

    if (!odpSelect || !portSelect) return;

    const initialPort = portSelect.dataset.current || portSelect.value || "{{ old('port_number', $customer->port_number ?? '') }}";
    const customerId = "{{ $customer->id ?? '' }}";

    function setPortLoading() {
        portSelect.innerHTML = '<option value="">Memuat port...</option>';
        portSelect.disabled = true;
    }

    function setPortEmpty(text) {
        portSelect.innerHTML = '<option value="">' + text + '</option>';
        portSelect.disabled = false;
    }

    async function loadOdpPorts() {
        const odpId = odpSelect.value;

        if (!odpId) {
            setPortEmpty('- Pilih ODP dulu -');
            return;
        }

        setPortLoading();

        const params = new URLSearchParams();

        if (customerId) {
            params.set('customer_id', customerId);
        }

        try {
            const res = await fetch('/admin/odps/' + odpId + '/ports-json?' + params.toString(), {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const json = await res.json();
            const ports = json.ports || [];

            portSelect.innerHTML = '<option value="">- Pilih Port -</option>';

            ports.forEach(function (port) {
                const option = document.createElement('option');
                option.value = port.value;
                option.textContent = port.label + (port.used ? ' - Terpakai' : '');

                if (port.used && String(port.value) !== String(initialPort)) {
                    option.disabled = true;
                }

                if (String(port.value) === String(initialPort)) {
                    option.selected = true;
                }

                portSelect.appendChild(option);
            });

            portSelect.disabled = false;
        } catch (e) {
            setPortEmpty('Gagal memuat port');
        }
    }

    odpSelect.addEventListener('change', function () {
        portSelect.dataset.current = '';
        loadOdpPorts();
    });

    loadOdpPorts();
});
</script>





<!-- MACSERVICE CUSTOMER LOCATION MAP EXACT ID START -->
<style>
#customer-location-map{
    min-height:360px;
    height:360px;
    width:100%;
    border-radius:0;
    overflow:hidden;
    background:#eaf3ff;
}
#customer-location-map .leaflet-control-layers{
    border:1px solid #dbe5f2 !important;
    border-radius:14px !important;
    box-shadow:0 12px 28px rgba(16,24,40,.16) !important;
    overflow:hidden !important;
}
#customer-location-map .leaflet-control-layers-expanded{
    padding:9px 11px !important;
    color:#101828 !important;
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif !important;
    font-size:12px !important;
    font-weight:800 !important;
}
@media(max-width:760px){
    #customer-location-map{
        min-height:310px;
        height:310px;
    }
}
</style>

@php
    $macCustomerMapOdpsExact = ($odps ?? \App\Models\Odp::query()->get())->mapWithKeys(function ($odp) {
        return [
            (string) $odp->id => [
                'id' => $odp->id,
                'name' => $odp->name ?? ('ODP #'.$odp->id),
                'location' => $odp->location ?? '',
                'lat' => $odp->latitude ?? null,
                'lng' => $odp->longitude ?? null,
            ],
        ];
    });
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapEl = document.getElementById('customer-location-map');

    if (!mapEl) return;

    const odpData = @json($macCustomerMapOdpsExact);

    const odpSelect = document.getElementById('odp_id') || document.querySelector('select[name="odp_id"]');
    const latInput = document.getElementById('latitude') || document.querySelector('input[name="latitude"]');
    const lngInput = document.getElementById('longitude') || document.querySelector('input[name="longitude"]');
    const pathInput = document.getElementById('cable_path_json') || document.querySelector('[name="cable_path_json"]');
    const distanceInput = document.getElementById('cable_distance_m') || document.querySelector('[name="cable_distance_m"]');

    const routeStatus = document.getElementById('route_status');
    const sideLat = document.getElementById('side-latitude');
    const sideLng = document.getElementById('side-longitude');
    const sideDistance = document.getElementById('side-distance');

    const btnCurrent = document.getElementById('btn-use-current-location');
    const btnOdp = document.getElementById('btn-use-odp-location');
    const btnDefault = document.getElementById('btn-use-default-location');

    const defaultPoint = [-8.209567, 112.658531];

    let map = null;
    let odpMarker = null;
    let customerMarker = null;
    let line = null;

    function parseNumber(value) {
        if (value === null || value === undefined || value === '') return null;

        const n = parseFloat(String(value).replace(',', '.'));

        return Number.isFinite(n) ? n : null;
    }

    function customerPoint() {
        const lat = parseNumber(latInput ? latInput.value : null);
        const lng = parseNumber(lngInput ? lngInput.value : null);

        if (lat !== null && lng !== null) {
            return [lat, lng];
        }

        return null;
    }

    function selectedOdpPoint() {
        if (!odpSelect || !odpSelect.value) return null;

        const item = odpData[String(odpSelect.value)];

        if (!item) return null;

        const lat = parseNumber(item.lat);
        const lng = parseNumber(item.lng);

        if (lat !== null && lng !== null) {
            return [lat, lng];
        }

        return null;
    }

    function selectedOdpName() {
        if (!odpSelect || !odpSelect.value) return 'ODP';

        const item = odpData[String(odpSelect.value)];

        return item ? item.name : 'ODP';
    }

    function setCustomerPoint(point) {
        if (!point) return;

        if (latInput) latInput.value = Number(point[0]).toFixed(7);
        if (lngInput) lngInput.value = Number(point[1]).toFixed(7);

        if (sideLat) sideLat.textContent = Number(point[0]).toFixed(7);
        if (sideLng) sideLng.textContent = Number(point[1]).toFixed(7);
    }

    function distanceMeter(a, b) {
        const R = 6371000;
        const toRad = d => d * Math.PI / 180;

        const dLat = toRad(b[0] - a[0]);
        const dLng = toRad(b[1] - a[1]);
        const lat1 = toRad(a[0]);
        const lat2 = toRad(b[0]);

        const x =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.sin(dLng / 2) * Math.sin(dLng / 2) * Math.cos(lat1) * Math.cos(lat2);

        return Math.round(R * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x)));
    }

    function updateRouteStatus(text, type) {
        if (!routeStatus) return;

        routeStatus.textContent = text;
        routeStatus.classList.remove('ok', 'warn', 'err');

        if (type) {
            routeStatus.classList.add(type);
        }
    }

    function redraw() {
        if (!map) return;

        const odpPoint = selectedOdpPoint();
        let custPoint = customerPoint();

        if (!custPoint) {
            custPoint = odpPoint || defaultPoint;
            setCustomerPoint(custPoint);
        }

        if (odpPoint) {
            if (!odpMarker) {
                odpMarker = L.marker(odpPoint).addTo(map).bindPopup(selectedOdpName());
            } else {
                odpMarker.setLatLng(odpPoint);
                odpMarker.bindPopup(selectedOdpName());
            }
        } else if (odpMarker) {
            map.removeLayer(odpMarker);
            odpMarker = null;
        }

        if (custPoint) {
            if (!customerMarker) {
                customerMarker = L.marker(custPoint, { draggable: true }).addTo(map).bindPopup('Pelanggan');
                customerMarker.on('dragend', function () {
                    const ll = customerMarker.getLatLng();
                    setCustomerPoint([ll.lat, ll.lng]);
                    redraw();
                });
            } else {
                customerMarker.setLatLng(custPoint);
            }
        }

        if (odpPoint && custPoint) {
            const path = [odpPoint, custPoint];

            if (!line) {
                line = L.polyline(path, { weight: 4 }).addTo(map);
            } else {
                line.setLatLngs(path);
            }

            const distance = distanceMeter(odpPoint, custPoint);

            if (pathInput) {
                pathInput.value = JSON.stringify(path);
            }

            if (distanceInput) {
                distanceInput.value = distance;
            }

            if (sideDistance) {
                sideDistance.textContent = distance.toLocaleString('id-ID') + ' m';
            }

            updateRouteStatus('Jalur kabel terbaca dari ODP ke titik pelanggan. Estimasi jarak lurus: ' + distance.toLocaleString('id-ID') + ' m.', 'ok');

            const bounds = L.latLngBounds(path);

            if (bounds.isValid()) {
                map.fitBounds(bounds, { padding: [34, 34], maxZoom: 18 });
            }
        } else {
            if (line) {
                map.removeLayer(line);
                line = null;
            }

            if (!odpPoint) {
                updateRouteStatus('ODP belum memiliki koordinat. Isi koordinat ODP dulu atau gunakan titik default.', 'warn');
            } else {
                updateRouteStatus('Klik peta untuk menentukan lokasi pelanggan.', 'warn');
            }

            map.setView(custPoint || defaultPoint, 17);
        }

        setTimeout(function () {
            map.invalidateSize();
        }, 150);
    }

    function initMap() {
        if (!window.L) {
            mapEl.innerHTML = '<div style="padding:14px;color:#b42318;background:#fef3f2;border-radius:16px">Leaflet gagal dimuat.</div>';
            return;
        }

        const start = customerPoint() || selectedOdpPoint() || defaultPoint;

        if (mapEl._leaflet_id) {
            try {
                mapEl.innerHTML = '';
                delete mapEl._leaflet_id;
            } catch (e) {}
        }

        map = L.map(mapEl, {
            zoomControl: true,
            attributionControl: true
        }).setView(start, 17);

        const satellite = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            {
                maxZoom: 20,
                attribution: 'Tiles © Esri — Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community'
            }
        );

        const street = L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                maxZoom: 20,
                attribution: '© OpenStreetMap'
            }
        );

        satellite.addTo(map);

        L.control.layers({
            'Satelit': satellite,
            'Peta Jalan': street
        }).addTo(map);

        map.on('click', function (e) {
            setCustomerPoint([e.latlng.lat, e.latlng.lng]);
            redraw();
        });

        if (odpSelect) {
            odpSelect.addEventListener('change', function () {
                const odpPoint = selectedOdpPoint();

                if (odpPoint && (!latInput.value || !lngInput.value)) {
                    setCustomerPoint(odpPoint);
                }

                redraw();
            });
        }

        if (btnOdp) {
            btnOdp.addEventListener('click', function () {
                const odpPoint = selectedOdpPoint();

                if (!odpPoint) {
                    updateRouteStatus('ODP belum memiliki koordinat.', 'warn');
                    return;
                }

                setCustomerPoint(odpPoint);
                redraw();
            });
        }

        if (btnDefault) {
            btnDefault.addEventListener('click', function () {
                setCustomerPoint(defaultPoint);
                redraw();
            });
        }

        if (btnCurrent) {
            btnCurrent.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    updateRouteStatus('Browser tidak mendukung lokasi saat ini.', 'warn');
                    return;
                }

                updateRouteStatus('Mengambil lokasi perangkat...', 'warn');

                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        setCustomerPoint([pos.coords.latitude, pos.coords.longitude]);
                        redraw();
                    },
                    function () {
                        updateRouteStatus('Gagal mengambil lokasi perangkat.', 'err');
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }

        redraw();

        setTimeout(function () { map.invalidateSize(); }, 300);
        setTimeout(function () { map.invalidateSize(); }, 900);
        setTimeout(function () { map.invalidateSize(); }, 1600);

        window.macCustomerLocationMap = map;
    }

    function ensureLeaflet(callback) {
        if (window.L) {
            callback();
            return;
        }

        let existing = document.querySelector('script[src*="leaflet"]');

        if (existing) {
            existing.addEventListener('load', callback);
            setTimeout(function () {
                if (window.L) callback();
            }, 800);
            return;
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = callback;
        document.body.appendChild(script);
    }

    ensureLeaflet(initMap);
});
</script>
<!-- MACSERVICE CUSTOMER LOCATION MAP EXACT ID END -->

@endsection
