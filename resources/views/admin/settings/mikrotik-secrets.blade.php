@extends('layouts.neo')
@section('title','PPPoE Secret')
@section('content')

<style>
.mikrotik-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 14px}.mikrotik-tabs a{display:inline-flex;align-items:center;min-height:40px;padding:0 14px;border-radius:999px;background:#fff;border:1px solid #e5e7eb;color:#475569;font-size:13px;font-weight:900;box-shadow:0 8px 20px rgba(15,23,42,.04)}.mikrotik-tabs a.active{background:#0f172a;color:#fff;border-color:#0f172a}.mikrotik-subhero{background:linear-gradient(135deg,#0f172a,#1d4ed8);border-radius:24px;padding:20px;color:#fff;margin-bottom:12px;box-shadow:0 18px 44px rgba(15,23,42,.14)}.mikrotik-subhero span{display:block;color:#bfdbfe;font-size:13px;font-weight:850}.mikrotik-subhero b{display:block;margin-top:6px;font-size:27px;line-height:1.05;letter-spacing:-.055em}.mikrotik-subhero p{margin:9px 0 0;color:#dbeafe;font-size:14px;line-height:1.45;max-width:820px}.mikrotik-sync-panel{background:#fff;border:1px solid #e5e7eb;border-radius:22px;padding:16px;margin-bottom:12px;box-shadow:0 12px 30px rgba(15,23,42,.055)}.mikrotik-sync-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px}.mikrotik-sync-head b{display:block;color:#0f172a;font-size:16px;letter-spacing:-.03em}.mikrotik-sync-head span{display:block;margin-top:4px;color:#64748b;font-size:12px;line-height:1.45}.mikrotik-router-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.mikrotik-router-mini{display:flex;align-items:center;justify-content:space-between;gap:10px;border:1px solid #e5e7eb;background:#f8fafc;border-radius:18px;padding:12px}.mikrotik-router-mini b{display:block;font-size:13px;color:#0f172a}.mikrotik-router-mini span{display:block;margin-top:3px;color:#64748b;font-size:12px;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.mikrotik-router-mini form{margin:0;flex:0 0 auto}.mikrotik-moved-note{background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:18px;padding:13px;font-size:13px;font-weight:800;line-height:1.45;margin:12px 0}.neo-xls{border-radius:22px!important;overflow:hidden}.neo-xls-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}.neo-xls-table{min-width:860px}.network-section-head{background:transparent!important;border:0!important;padding:0!important;box-shadow:none!important}.network-section-head b{font-size:24px!important;letter-spacing:-.045em!important;color:#0f172a!important}.network-section-head span{display:block!important;margin-top:6px!important;color:#64748b!important;font-size:13px!important;line-height:1.45!important}@media(max-width:980px){.mikrotik-router-grid{grid-template-columns:1fr}}@media(max-width:760px){.mikrotik-tabs{overflow-x:auto;flex-wrap:nowrap;padding-bottom:3px}.mikrotik-tabs a{flex:0 0 auto}.mikrotik-subhero{border-radius:20px;padding:18px}.mikrotik-subhero b{font-size:23px}.mikrotik-sync-head{display:block}.mikrotik-sync-head .btn{margin-top:10px;width:100%;justify-content:center}.mikrotik-router-mini{border-radius:16px}.network-section-head b{font-size:22px!important}}
</style>

<div class="pagehead">
    <div><h1>PPPoE Secret</h1><p>Secret dari Mikrotik yang bisa dipilih otomatis di form pelanggan.</p></div>
    <div class="neo-actions"><a class="btn light" href="{{ url('/admin/settings/mikrotik') }}">Router</a><a class="btn" href="{{ url('/admin/settings/mikrotik/create') }}">Tambah Mikrotik</a></div>
</div>
@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="mikrotik-tabs">
    <a class="{{ request()->is('admin/settings/mikrotik') ? 'active' : '' }}" href="{{ url('/admin/settings/mikrotik') }}">Router</a>
    <a class="{{ request()->is('admin/settings/mikrotik/profiles') ? 'active' : '' }}" href="{{ url('/admin/settings/mikrotik/profiles') }}">PPPoE Profile</a>
    <a class="{{ request()->is('admin/settings/mikrotik/secrets') ? 'active' : '' }}" href="{{ url('/admin/settings/mikrotik/secrets') }}">PPPoE Secret</a>
    <a class="{{ request()->is('admin/settings/mikrotik/active-sessions') ? 'active' : '' }}" href="{{ url('/admin/settings/mikrotik/active-sessions') }}">Active Session</a>
</div>

<div class="mikrotik-subhero"><span>PPPoE Data</span><b>Secret Mikrotik</b><p>Data secret dipindahkan ke sub menu khusus agar daftar router tidak penuh tabel panjang.</p></div>

<div class="mikrotik-sync-panel">
    <div class="mikrotik-sync-head">
        <div><b>Ambil PPPoE Secret</b><span>Membaca daftar PPPoE secret dari router. Password tetap terenkripsi oleh Laravel.</span></div>
        <a class="btn light" href="{{ url('/admin/settings/mikrotik') }}">Kelola Router</a>
    </div>
    <div class="mikrotik-router-grid">
        @forelse($routers as $router)
            <div class="mikrotik-router-mini">
                <div><b>{{ $router->name }}</b><span>{{ $router->host }}:{{ $router->api_port }}</span></div>
                <form method="POST" action="{{ url('/admin/settings/mikrotik/'.$router->id.'/sync-secrets') }}" onsubmit="return confirm('Ambil PPPoE Secret dari router ini?')">
                    @csrf
                    <button class="btn" type="submit">Ambil Secret</button>
                </form>
            </div>
        @empty
            <div class="mikrotik-router-mini"><div><b>Belum ada router</b><span>Tambahkan Mikrotik terlebih dahulu.</span></div></div>
        @endforelse
    </div>
</div>

<div class="network-section-head" style="margin:16px 2px 9px">
    <b>PPPoE Secret Tersimpan</b>
    <span>Secret dari Mikrotik. Data ini bisa dipilih otomatis di form pelanggan.</span>
</div>

<div class="neo-xls">
    <div class="neo-xls-info">
        <span>Total secret: <b>{{ \App\Models\MikrotikPppoeSecret::count() }}</b></span>
        <span>Data terakhir hasil sync dari Mikrotik</span>
    </div>

    <div class="neo-xls-scroll">
        <table class="neo-xls-table">
            <thead>
                <tr>
                    <th>Router</th>
                    <th>Secret Name</th>
                    <th>Service</th>
                    <th>Profile</th>
                    <th>Remote Address</th>
                    <th>Status</th>
                    <th>Komentar</th>
                    <th>Sync Terakhir</th>
                </tr>
            </thead>

            <tbody>
                @php($secrets = \App\Models\MikrotikPppoeSecret::with('router')->orderBy('name')->limit(80)->get())
                @forelse($secrets as $secret)
                    <tr>
                        <td>{{ $secret->router?->name ?: '-' }}</td>
                        <td class="neo-strong">{{ $secret->name }}</td>
                        <td>{{ $secret->service ?: '-' }}</td>
                        <td>{{ $secret->profile ?: '-' }}</td>
                        <td>{{ $secret->remote_address ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $secret->disabled === 'true' ? 'red' : 'green' }}">
                                {{ $secret->disabled === 'true' ? 'Disabled' : 'Aktif' }}
                            </span>
                        </td>
                        <td class="neo-clip">{{ $secret->comment ?: '-' }}</td>
                        <td>{{ $secret->last_synced_at?->format('d/m/Y H:i') ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Belum ada PPPoE Secret. Klik tombol Secret pada router untuk mengambil data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
