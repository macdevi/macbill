@extends('layouts.neo')
@section('title','Port ODP')
@section('content')
@php
    $usedPorts = collect($assigned ?? [])->filter()->count();
    $totalPorts = (int) $odp->port_count;
    $freePorts = max(0, $totalPorts - $usedPorts);

    $i = function ($name) {
        $icons = [
            'back' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>',
            'save' => '<svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>',
            'edit' => '<svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>',
            'map' => '<svg viewBox="0 0 24 24"><path d="M9 18 3 21V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>',
            'port' => '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>',
            'users' => '<svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M17 11a4 4 0 0 1 0 8"/></svg>',
            'check' => '<svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>',
        ];

        return '<span class="neo-mini-ico">'.$icons[$name].'</span>';
    };
@endphp

<style>
.odp-port-hero{
    background:linear-gradient(135deg,#1d4ed8,#0891b2);
    border-radius:28px;
    padding:22px;
    color:#fff;
    margin-bottom:12px;
    box-shadow:0 18px 44px rgba(16,24,40,.08);
}
.odp-port-hero span{
    display:block;
    color:#e0f2fe;
    font-size:13px;
    font-weight:800;
}
.odp-port-hero b{
    display:block;
    margin-top:6px;
    font-size:30px;
    line-height:1;
    letter-spacing:-.07em;
}
.odp-port-hero p{
    margin:10px 0 0;
    color:#e0f2fe;
    font-size:14px;
    line-height:1.45;
}
.odp-port-summary{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:10px;
    margin-bottom:10px;
}
.odp-port-card{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:22px;
    padding:15px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.odp-port-card .label{
    color:#667085;
    font-size:12px;
    font-weight:800;
}
.odp-port-card .value{
    margin-top:7px;
    color:#101828;
    font-size:22px;
    font-weight:950;
    letter-spacing:-.055em;
}
.odp-port-form{
    background:#fff;
    border:1px solid #e4eaf3;
    border-radius:24px;
    padding:14px;
    box-shadow:0 10px 24px rgba(16,24,40,.055);
}
.odp-port-toolbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-bottom:10px;
    flex-wrap:wrap;
}
.odp-port-toolbar b{
    color:#101828;
    font-size:16px;
    letter-spacing:-.04em;
}
.odp-port-toolbar span{
    color:#667085;
    font-size:12px;
}
.odp-port-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:10px;
}
.odp-port-box{
    border:1px solid #e4eaf3;
    background:#f8fbff;
    border-radius:18px;
    padding:12px;
}
.odp-port-box.used{
    background:#ecfdf3;
    border-color:#abefc6;
}
.odp-port-box.dup{
    background:#fef3f2;
    border-color:#fecdca;
}
.odp-port-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:8px;
    margin-bottom:9px;
}
.odp-port-head b{
    color:#101828;
    font-size:14px;
}
.odp-port-head span{
    font-size:11px;
    font-weight:900;
}
.odp-port-select{
    width:100%;
    height:40px;
    border-radius:13px;
    border:1px solid #dbe5f2;
    background:#fff;
    color:#101828;
    padding:0 10px;
    outline:none;
    font-size:12px;
    font-weight:750;
}
.odp-port-select:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 4px rgba(37,99,235,.10);
}
.odp-port-actions{
    display:flex;
    justify-content:flex-end;
    gap:8px;
    margin-top:14px;
}
.odp-port-warning{
    display:none;
    margin:10px 0;
    border:1px solid #fecdca;
    background:#fef3f2;
    color:#b42318;
    border-radius:16px;
    padding:10px;
    font-size:13px;
    line-height:1.45;
}
.odp-port-warning.show{
    display:block;
}
@media(max-width:1100px){
    .odp-port-grid{
        grid-template-columns:repeat(3,minmax(0,1fr));
    }
}
@media(max-width:840px){
    .odp-port-summary{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
    .odp-port-grid{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
}
@media(max-width:620px){
    .odp-port-hero{
        border-radius:22px;
        padding:18px;
    }
    .odp-port-hero b{
        font-size:25px;
    }
    .odp-port-summary,
    .odp-port-grid{
        gap:8px;
    }
    .odp-port-card{
        border-radius:18px;
        padding:12px;
    }
    .odp-port-card .value{
        font-size:19px;
    }
    .odp-port-form{
        border-radius:20px;
        padding:12px;
    }
    .odp-port-box{
        border-radius:16px;
        padding:10px;
    }
    .odp-port-actions{
        flex-direction:column;
    }
    .odp-port-actions .btn{
        width:100%;
        justify-content:center;
    }
}
</style>

<div class="pagehead">
    <div>
        <h1>Port ODP</h1>
        <p>Atur pelanggan pada masing-masing port ODP.</p>
    </div>

    <div class="neo-actions">
        <a class="btn light" href="{{ url('/admin/odps') }}">{!! $i('back') !!}Kembali</a>
        <a class="btn light" href="{{ url('/admin/odps/'.$odp->id.'/edit') }}">{!! $i('edit') !!}Edit ODP</a>
        <a class="btn light" href="{{ url('/admin/odps-map') }}">{!! $i('map') !!}Peta</a>
    </div>
</div>

@if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="odp-port-hero">
    <span>ODP</span>
    <b>{{ $odp->name }}</b>
    <p>{{ $odp->location ?: 'Lokasi ODP belum diisi.' }}</p>
</div>

<div class="odp-port-summary">
    <div class="odp-port-card">
        <div class="label">Total Port</div>
        <div class="value">{{ $totalPorts }}</div>
    </div>

    <div class="odp-port-card">
        <div class="label">Terpakai</div>
        <div class="value">{{ $usedPorts }}</div>
    </div>

    <div class="odp-port-card">
        <div class="label">Kosong</div>
        <div class="value">{{ $freePorts }}</div>
    </div>

    <div class="odp-port-card">
        <div class="label">Pelanggan Kandidat</div>
        <div class="value">{{ $customers->count() }}</div>
    </div>
</div>

<form class="odp-port-form" method="POST" action="{{ url('/admin/odps/'.$odp->id.'/ports') }}">
    @csrf

    <div class="odp-port-toolbar">
        <div>
            <b>Daftar Port</b>
            <span>Port hijau berarti sudah terisi. Port putih berarti kosong.</span>
        </div>

        <button class="btn" id="save-port-btn" type="submit">{!! $i('save') !!}Simpan Port</button>
    </div>

    <div id="duplicate-warning" class="odp-port-warning">
        Ada pelanggan yang dipilih pada lebih dari satu port. Pilih satu pelanggan hanya untuk satu port.
    </div>

    <div class="odp-port-grid">
        @for($port = 1; $port <= $totalPorts; $port++)
            @php
                $currentCustomerId = (int)($assigned[$port] ?? 0);
            @endphp

            <div class="odp-port-box {{ $currentCustomerId ? 'used' : '' }}" data-port-box>
                <div class="odp-port-head">
                    <b>Port {{ $port }}</b>
                    <span class="badge {{ $currentCustomerId ? 'green' : 'blue' }}" data-port-status>
                        {{ $currentCustomerId ? 'Terpakai' : 'Kosong' }}
                    </span>
                </div>

                <select class="odp-port-select" name="ports[{{ $port }}]" data-port-select>
                    <option value="">- Kosong -</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected($currentCustomerId === (int)$customer->id)>
                            {{ $customer->name }}
                            @if($customer->phone)
                                · {{ $customer->phone }}
                            @endif
                            @if($customer->odp_id && $customer->odp_id != $odp->id)
                                · ODP lain
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        @endfor
    </div>

    <div class="odp-port-actions">
        <a class="btn light" href="{{ url('/admin/odps') }}">Batal</a>
        <button class="btn" type="submit">{!! $i('save') !!}Simpan Perubahan</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selects = Array.from(document.querySelectorAll('[data-port-select]'));
    const warning = document.getElementById('duplicate-warning');
    const buttons = Array.from(document.querySelectorAll('button[type="submit"]'));

    function refreshPortState() {
        const counts = {};

        selects.forEach(function (select) {
            if (select.value) {
                counts[select.value] = (counts[select.value] || 0) + 1;
            }
        });

        let hasDuplicate = false;

        selects.forEach(function (select) {
            const box = select.closest('[data-port-box]');
            const status = box.querySelector('[data-port-status]');
            const value = select.value;
            const isDuplicate = value && counts[value] > 1;

            box.classList.remove('used', 'dup');

            if (isDuplicate) {
                hasDuplicate = true;
                box.classList.add('dup');
                status.className = 'badge red';
                status.textContent = 'Duplikat';
            } else if (value) {
                box.classList.add('used');
                status.className = 'badge green';
                status.textContent = 'Terpakai';
            } else {
                status.className = 'badge blue';
                status.textContent = 'Kosong';
            }
        });

        warning.classList.toggle('show', hasDuplicate);

        buttons.forEach(function (button) {
            button.disabled = hasDuplicate;
            button.style.opacity = hasDuplicate ? '.55' : '1';
            button.style.cursor = hasDuplicate ? 'not-allowed' : 'pointer';
        });
    }

    selects.forEach(function (select) {
        select.addEventListener('change', refreshPortState);
    });

    refreshPortState();
});
</script>
@endsection
