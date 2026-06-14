@extends('layouts.neo')

@section('content')

<div class="genieacs-page">
    <div class="genieacs-hero">
        <div>
            <p class="genieacs-kicker">ADMIN NETWORK</p>
            <h1>GenieACS</h1>
            <p>Monitoring dan integrasi dasar perangkat TR-069 dari GenieACS.</p>
        </div>

        <div class="genieacs-actions">
            <form method="POST" action="{{ url('/admin/genieacs/test') }}">
                @csrf
                <button type="submit" class="genieacs-btn primary">Test Koneksi</button>
            </form>

            <a href="{{ url('/admin/genieacs?load=devices') }}" class="genieacs-btn secondary">Ambil Device</a>
        </div>
    </div>

    @if(session('success') || session('error') || !empty($result))
        <div class="genieacs-alert {{ session('error') || (!empty($result) && empty($result['ok'])) ? 'danger' : 'success' }}">
            {{ session('success') ?? session('error') ?? ($result['message'] ?? '') }}
        </div>
    @endif

    <div class="genieacs-grid">
        <div class="genieacs-card">
            <h2>Setting Koneksi</h2>
            <p class="genieacs-muted">Isi URL NBI GenieACS. Contoh umum: <b>http://IP-SERVER:7557</b>.</p>

            <form method="POST" action="{{ url('/admin/genieacs/save') }}" class="genieacs-form">
                @csrf

                <label>URL GenieACS / NBI</label>
                <input type="text" name="genieacs_url" value="{{ old('genieacs_url', $config['url'] ?? '') }}" placeholder="http://127.0.0.1:7557">

                <label>Username Basic Auth</label>
                <input type="text" name="genieacs_username" value="{{ old('genieacs_username', $config['username'] ?? '') }}" placeholder="Kosongkan jika tidak pakai auth">

                <label>Password Basic Auth</label>
                <input type="password" name="genieacs_password" value="{{ old('genieacs_password', $config['password'] ?? '') }}" placeholder="Kosongkan jika tidak pakai auth">

                <label>Timeout Detik</label>
                <input type="number" name="genieacs_timeout" min="3" max="60" value="{{ old('genieacs_timeout', $config['timeout'] ?? 8) }}">

                <button type="submit" class="genieacs-btn primary full">Simpan Setting</button>
            </form>
        </div>

        <div class="genieacs-card">
            <h2>Status Integrasi</h2>

            <div class="genieacs-stat">
                <span>URL</span>
                <b>{{ !empty($stat['url_filled']) ? 'Terisi' : 'Belum diisi' }}</b>
            </div>

            <div class="genieacs-stat">
                <span>Basic Auth</span>
                <b>{{ !empty($stat['auth_filled']) ? 'Aktif' : 'Tidak digunakan' }}</b>
            </div>

            <div class="genieacs-stat">
                <span>Device tampil</span>
                <b>{{ $stat['device_count'] ?? 0 }}</b>
            </div>

            <div class="genieacs-note">
                Tahap ini belum menghubungkan device ke pelanggan. Setelah koneksi berhasil, tahap berikutnya bisa ditambah mapping ke pelanggan berdasarkan serial number, MAC, PPPoE, atau nomor pelanggan.
            </div>
        </div>
    </div>

    <div class="genieacs-card">
        <div class="genieacs-table-head">
            <div>
                <h2>Daftar Device GenieACS</h2>
                <p class="genieacs-muted">Menampilkan maksimal 100 device dari endpoint <b>/devices/</b>.</p>
            </div>

            <a href="{{ url('/admin/genieacs?load=devices') }}" class="genieacs-btn secondary">Refresh Device</a>
        </div>

        <div class="genieacs-table-wrap">
            <table class="genieacs-table">
                <thead>
                    <tr>
                        <th>Device ID</th>
                        <th>Manufacturer</th>
                        <th>Product</th>
                        <th>Serial</th>
                        <th>Last Inform</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                        <tr>
                            <td>{{ $device['_id'] ?? '-' }}</td>
                            <td>{{ data_get($device, '_deviceId._Manufacturer', '-') }}</td>
                            <td>{{ data_get($device, '_deviceId._ProductClass', '-') }}</td>
                            <td>{{ data_get($device, '_deviceId._SerialNumber', '-') }}</td>
                            <td>{{ $device['_lastInform'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="genieacs-empty">Belum ada device ditampilkan. Klik <b>Ambil Device</b>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style id="genieacs-admin-style-v1">
.genieacs-page{
    display:grid;
    gap:18px;
}
.genieacs-hero{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:18px;
    padding:22px;
    border-radius:24px;
    background:linear-gradient(135deg,#0f172a,#1d4ed8 58%,#06b6d4);
    color:#fff;
    box-shadow:0 18px 44px rgba(37,99,235,.22);
}
.genieacs-kicker{
    margin:0 0 6px;
    font-size:12px;
    letter-spacing:.14em;
    font-weight:900;
    opacity:.75;
}
.genieacs-hero h1{
    margin:0;
    font-size:30px;
    font-weight:900;
}
.genieacs-hero p{
    margin:6px 0 0;
    opacity:.88;
    font-weight:700;
}
.genieacs-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.genieacs-grid{
    display:grid;
    grid-template-columns:1.25fr .75fr;
    gap:18px;
}
.genieacs-card{
    padding:20px;
    border-radius:22px;
    background:#fff;
    border:1px solid #e5e7eb;
    box-shadow:0 14px 34px rgba(15,23,42,.08);
}
.genieacs-card h2{
    margin:0 0 8px;
    font-size:20px;
    font-weight:900;
    color:#111827;
}
.genieacs-muted{
    margin:0 0 16px;
    color:#64748b;
    font-weight:650;
}
.genieacs-form{
    display:grid;
    gap:10px;
}
.genieacs-form label{
    font-weight:900;
    font-size:13px;
    color:#334155;
}
.genieacs-form input{
    width:100%;
    border:1px solid #dbe3ef;
    border-radius:14px;
    padding:12px 13px;
    font:inherit;
    outline:none;
}
.genieacs-form input:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 4px rgba(37,99,235,.12);
}
.genieacs-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:42px;
    padding:10px 14px;
    border:0;
    border-radius:14px;
    font-weight:900;
    text-decoration:none;
    cursor:pointer;
    font-size:14px;
}
.genieacs-btn.primary{
    color:#fff;
    background:linear-gradient(135deg,#2563eb,#06b6d4);
    box-shadow:0 12px 24px rgba(37,99,235,.20);
}
.genieacs-btn.secondary{
    color:#1d4ed8;
    background:#eff6ff;
    border:1px solid #bfdbfe;
}
.genieacs-btn.full{
    width:100%;
    margin-top:8px;
}
.genieacs-alert{
    padding:14px 16px;
    border-radius:18px;
    font-weight:800;
}
.genieacs-alert.success{
    color:#166534;
    background:#dcfce7;
    border:1px solid #bbf7d0;
}
.genieacs-alert.danger{
    color:#991b1b;
    background:#fee2e2;
    border:1px solid #fecaca;
}
.genieacs-stat{
    display:flex;
    justify-content:space-between;
    gap:12px;
    padding:13px 0;
    border-bottom:1px solid #eef2f7;
}
.genieacs-stat span{
    color:#64748b;
    font-weight:800;
}
.genieacs-stat b{
    color:#0f172a;
    font-weight:900;
}
.genieacs-note{
    margin-top:16px;
    padding:14px;
    border-radius:16px;
    background:#f8fafc;
    color:#475569;
    font-weight:700;
    line-height:1.5;
}
.genieacs-table-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:14px;
    margin-bottom:14px;
}
.genieacs-table-wrap{
    overflow:auto;
    border:1px solid #e5e7eb;
    border-radius:18px;
}
.genieacs-table{
    width:100%;
    border-collapse:collapse;
    min-width:760px;
}
.genieacs-table th,
.genieacs-table td{
    padding:12px 14px;
    border-bottom:1px solid #eef2f7;
    text-align:left;
    vertical-align:top;
}
.genieacs-table th{
    background:#f8fafc;
    color:#334155;
    font-weight:900;
}
.genieacs-table td{
    color:#334155;
    font-weight:700;
    font-size:13px;
}
.genieacs-empty{
    text-align:center;
    color:#64748b;
}
@media(max-width:900px){
    .genieacs-hero,
    .genieacs-table-head{
        flex-direction:column;
        align-items:stretch;
    }
    .genieacs-grid{
        grid-template-columns:1fr;
    }
    .genieacs-actions{
        flex-direction:column;
    }
    .genieacs-btn{
        width:100%;
    }
}
</style>

<style id="genieacs-compact-mobile-v2">
/* Compact mobile GenieACS */
@media(max-width:768px){
    .genieacs-page{
        gap:10px !important;
    }

    .genieacs-hero{
        padding:14px !important;
        border-radius:18px !important;
        gap:12px !important;
    }

    .genieacs-kicker{
        font-size:10px !important;
        margin-bottom:4px !important;
    }

    .genieacs-hero h1{
        font-size:22px !important;
        line-height:1.1 !important;
    }

    .genieacs-hero p{
        font-size:12px !important;
        line-height:1.35 !important;
        margin-top:5px !important;
    }

    .genieacs-actions{
        display:grid !important;
        grid-template-columns:1fr 1fr !important;
        gap:8px !important;
        width:100% !important;
    }

    .genieacs-actions form{
        width:100% !important;
    }

    .genieacs-btn{
        min-height:38px !important;
        width:100% !important;
        padding:8px 10px !important;
        border-radius:12px !important;
        font-size:12px !important;
        white-space:nowrap !important;
    }

    .genieacs-grid{
        grid-template-columns:1fr !important;
        gap:10px !important;
    }

    .genieacs-card{
        padding:14px !important;
        border-radius:18px !important;
    }

    .genieacs-card h2{
        font-size:16px !important;
        margin-bottom:6px !important;
    }

    .genieacs-muted{
        font-size:12px !important;
        line-height:1.35 !important;
        margin-bottom:10px !important;
    }

    .genieacs-form{
        gap:7px !important;
    }

    .genieacs-form label{
        font-size:11px !important;
    }

    .genieacs-form input{
        min-height:38px !important;
        padding:9px 10px !important;
        border-radius:12px !important;
        font-size:13px !important;
    }

    .genieacs-stat{
        padding:9px 0 !important;
        font-size:12px !important;
    }

    .genieacs-note{
        margin-top:10px !important;
        padding:10px !important;
        border-radius:14px !important;
        font-size:12px !important;
        line-height:1.38 !important;
    }

    .genieacs-alert{
        padding:10px 12px !important;
        border-radius:14px !important;
        font-size:12px !important;
        line-height:1.35 !important;
    }

    .genieacs-table-head{
        gap:8px !important;
        margin-bottom:10px !important;
    }

    .genieacs-table-wrap{
        border-radius:16px !important;
        overflow:visible !important;
        border:0 !important;
    }

    .genieacs-table{
        min-width:0 !important;
        width:100% !important;
        border-collapse:separate !important;
        border-spacing:0 8px !important;
    }

    .genieacs-table thead{
        display:none !important;
    }

    .genieacs-table,
    .genieacs-table tbody,
    .genieacs-table tr,
    .genieacs-table td{
        display:block !important;
    }

    .genieacs-table tr{
        background:#f8fafc !important;
        border:1px solid #e5e7eb !important;
        border-radius:16px !important;
        padding:10px !important;
        box-shadow:0 8px 18px rgba(15,23,42,.06) !important;
    }

    .genieacs-table td{
        border:0 !important;
        padding:5px 0 !important;
        font-size:12px !important;
        word-break:break-word !important;
    }

    .genieacs-table td:nth-child(1)::before{content:"Device ID: ";font-weight:900;color:#0f172a;}
    .genieacs-table td:nth-child(2)::before{content:"Manufacturer: ";font-weight:900;color:#0f172a;}
    .genieacs-table td:nth-child(3)::before{content:"Product: ";font-weight:900;color:#0f172a;}
    .genieacs-table td:nth-child(4)::before{content:"Serial: ";font-weight:900;color:#0f172a;}
    .genieacs-table td:nth-child(5)::before{content:"Last Inform: ";font-weight:900;color:#0f172a;}

    .genieacs-empty{
        text-align:left !important;
    }

    .genieacs-empty::before{
        content:"" !important;
    }
}
</style>


@endsection
