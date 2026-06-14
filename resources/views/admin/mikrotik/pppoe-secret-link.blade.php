@extends('layouts.neo')

@section('title','Tautkan PPPoE Secret')

@section('content')
@include('admin.mikrotik._style')

<style>
.link-wrap{
    display:flex;
    flex-direction:column;
    gap:14px;
}
.link-card{
    background:#fff;
    border:1px solid #e5eaf3;
    border-radius:24px;
    box-shadow:0 12px 28px rgba(16,24,40,.055);
    overflow:hidden;
}
.link-head{
    padding:16px;
    display:flex;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
    background:linear-gradient(180deg,#ffffff,#f8fbff);
    border-bottom:1px solid #eef2f7;
}
.link-title b{
    display:block;
    font-size:18px;
    font-weight:950;
    color:#101828;
}
.link-title span{
    display:block;
    margin-top:4px;
    font-size:12px;
    font-weight:750;
    color:#667085;
}
.link-body{
    padding:16px;
}
.secret-info{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:10px;
}
.secret-box{
    border:1px solid #eef2f7;
    border-radius:18px;
    padding:12px;
    background:#f8fafc;
}
.secret-box span{
    display:block;
    font-size:11px;
    font-weight:900;
    color:#64748b;
    text-transform:uppercase;
}
.secret-box b{
    display:block;
    margin-top:5px;
    font-size:13px;
    color:#111827;
    word-break:break-word;
}
.search-row{
    display:flex;
    gap:8px;
    margin-top:14px;
}
.search-row input{
    flex:1;
    height:44px;
    border:1px solid #e5e7eb;
    border-radius:15px;
    padding:0 12px;
    font-weight:800;
}
.btn{
    min-height:40px;
    border:0;
    border-radius:14px;
    padding:0 14px;
    font-size:12px;
    font-weight:950;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    cursor:pointer;
}
.btn.primary{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
}
.btn.light{
    background:#eff6ff;
    color:#1d4ed8;
    border:1px solid #dbeafe;
}
.customer-table-wrap{
    overflow:auto;
}
.customer-table{
    width:100%;
    min-width:860px;
    border-collapse:separate;
    border-spacing:0;
}
.customer-table th{
    text-align:left;
    padding:12px;
    background:#f8fafc;
    border-bottom:1px solid #e5e7eb;
    color:#64748b;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.06em;
}
.customer-table td{
    padding:12px;
    border-bottom:1px solid #eef2f7;
    color:#111827;
    font-size:13px;
    font-weight:650;
}
.score-pill{
    display:inline-flex;
    border-radius:999px;
    padding:5px 10px;
    background:#ecfdf3;
    color:#027a48;
    font-size:11px;
    font-weight:950;
}
@media(max-width:760px){
    .secret-info{
        grid-template-columns:1fr;
    }
    .search-row{
        flex-direction:column;
    }
}
</style>

<div class="link-wrap">
    <div class="link-card">
        <div class="link-head">
            <div class="link-title">
                <b>Tautkan PPPoE Secret ke Pelanggan</b>
                <span>Pilih pelanggan billing yang paling cocok dengan nama PPPoE Secret.</span>
            </div>

            <a class="btn light" href="{{ url('/admin/mikrotik/pppoe-secret') }}">Kembali</a>
        </div>

        <div class="link-body">
            <div class="secret-info">
                <div class="secret-box">
                    <span>PPPoE Name</span>
                    <b>{{ $secret->name }}</b>
                </div>
                <div class="secret-box">
                    <span>Profile</span>
                    <b>{{ $secret->profile ?? '-' }}</b>
                </div>
                <div class="secret-box">
                    <span>Password</span>
                    <b>{{ $secret->password ? 'TERISI' : 'KOSONG' }}</b>
                </div>
                <div class="secret-box">
                    <span>Nama Terbaca</span>
                    <b>{{ $baseKeyword ?: '-' }}</b>
                </div>
            </div>

            <form class="search-row" method="GET" action="{{ url('/admin/mikrotik/pppoe-secret/'.$secret->id.'/tautkan') }}">
                <input type="text" name="q" value="{{ $keyword }}" placeholder="Cari nama pelanggan...">
                <button class="btn primary" type="submit">Filter Nama</button>
            </form>
        </div>
    </div>

    <div class="link-card">
        <div class="link-head">
            <div class="link-title">
                <b>Rekomendasi Pelanggan</b>
                <span>Urutan berdasarkan kemiripan nama dan ID awal dari PPPoE Secret.</span>
            </div>
        </div>

        <div class="customer-table-wrap">
            <table class="customer-table">
                <thead>
                    <tr>
                        <th>Skor</th>
                        <th>ID</th>
                        <th>Nama Pelanggan</th>
                        <th>PPPoE Username Saat Ini</th>
                        <th>Status Sync</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($candidates as $item)
                        @php
                            $customer = $item['customer'];
                            $score = $item['score'];
                        @endphp
                        <tr>
                            <td><span class="score-pill">{{ $score }}</span></td>
                            <td>{{ $customer->id }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->pppoe_username ?: '-' }}</td>
                            <td>{{ $customer->mikrotik_sync_status ?: '-' }}</td>
                            <td>
                                <form method="POST" action="{{ url('/admin/mikrotik/pppoe-secret/'.$secret->id.'/tautkan/customer/'.$customer->id) }}" onsubmit="return confirm('Tautkan PPPoE {{ $secret->name }} ke pelanggan {{ $customer->name }}?');">
                                    @csrf
                                    <button class="btn primary" type="submit">Pilih & Sinkronkan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Tidak ada pelanggan yang cocok. Ubah kata kunci filter nama di atas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
