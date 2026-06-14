{{-- Clean expenses index: filter bulan saja --}}
@extends('layouts.neo')

@php
    $base = $base ?? (request()->is('collector/*') ? '/collector/expenses' : '/admin/expenses');
    $home = $home ?? (request()->is('collector/*') ? '/collector/dashboard' : '/admin/dashboard');
    $periodValue = request('period', $period ?? now()->format('Y-m'));

    $money = function ($value) {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    };

    $dateLabel = function ($value) {
        if (! $value) return '-';
        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return $value;
        }
    };
@endphp

@section('title', 'Pengeluaran')

@section('top_actions')
    <a class="logout" href="{{ url($home) }}" onclick="if (history.length > 1) { history.back(); return false; }">← Kembali</a>
    <a class="logout top-add-btn" href="{{ url($base.'/create') }}">+ Tambah</a>
@endsection

@section('content')
<style>
.expense-page {
    padding-bottom: 120px;
}

.expense-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 14px;
}

.expense-card,
.expense-filter,
.expense-table-card {
    background: #fff;
    border: 1px solid rgba(15, 23, 42, .08);
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
}

.expense-card {
    padding: 15px;
}

.expense-card span {
    display: block;
    color: #6b7280;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
}

.expense-card strong {
    display: block;
    color: #111827;
    font-size: 19px;
    font-weight: 800;
}

.expense-filter {
    padding: 13px;
    margin-bottom: 14px;
}

.expense-filter form {
    display: grid;
    gap: 10px;
}

.expense-filter input {
    width: 100%;
    border: 1px solid rgba(15, 23, 42, .12);
    border-radius: 16px;
    padding: 15px 16px;
    font-size: 17px;
    background: #fff;
    color: #111827;
}

.expense-filter .btn {
    width: 100%;
    border: 0;
    border-radius: 16px;
    padding: 15px 18px;
    background: #2454e8;
    color: #fff;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
}

.expense-table-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 13px 14px;
    color: #6b7280;
    font-weight: 800;
}

.expense-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.expense-table {
    width: 100%;
    min-width: 760px;
    border-collapse: collapse;
}

.expense-table th {
    text-align: left;
    font-size: 12px;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #6b7280;
    padding: 12px 10px;
    border-top: 1px solid rgba(15, 23, 42, .08);
    border-bottom: 1px solid rgba(15, 23, 42, .08);
    background: #f8fafc;
}

.expense-table td {
    padding: 13px 10px;
    border-bottom: 1px solid rgba(15, 23, 42, .07);
    color: #111827;
    vertical-align: middle;
}

.badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 5px 10px;
    background: #eef6ff;
    color: #1d4ed8;
    font-size: 12px;
    font-weight: 800;
}

.action-row {
    display: flex;
    gap: 8px;
    align-items: center;
}

.action-row a,
.action-row button {
    border: 0;
    border-radius: 10px;
    padding: 8px 10px;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
    cursor: pointer;
}

.action-row a {
    background: #eef2ff;
    color: #1d4ed8;
}

.action-row button {
    background: #fee2e2;
    color: #b91c1c;
}

.empty-row {
    text-align: center;
    color: #6b7280 !important;
    padding: 30px 10px !important;
}

.pagination {
    padding: 14px;
}

@media (max-width: 640px) {
    .expense-stats {
        gap: 10px;
    }

    .expense-card {
        padding: 13px;
    }

    .expense-card strong {
        font-size: 18px;
    }

    .expense-table-head {
        font-size: 14px;
    }
}
</style>

<div class="expense-page">
    <div class="expense-stats">
        <div class="expense-card">
            <span>Total Periode</span>
            <strong>{{ $money($totalPeriod ?? 0) }}</strong>
        </div>

        <div class="expense-card">
            <span>Data</span>
            <strong>{{ method_exists($expenses, 'total') ? $expenses->total() : $expenses->count() }}</strong>
        </div>

        <div class="expense-card">
            <span>Hari Ini</span>
            <strong>{{ $money($todayTotal ?? 0) }}</strong>
        </div>

        <div class="expense-card">
            <span>Bulan Ini</span>
            <strong>{{ $money($monthTotal ?? 0) }}</strong>
        </div>
    </div>

    <div class="expense-filter">
        <form method="GET" action="{{ url($base) }}">
            <input type="month" name="period" value="{{ $periodValue }}">
            <button class="btn" type="submit">Filter</button>
        </form>
    </div>

    <div class="expense-table-card">
        <div class="expense-table-head">
            <span>Total halaman ini: {{ $expenses->count() }}</span>
            <span>Geser kanan untuk aksi</span>
        </div>

        <div class="expense-table-wrap">
            <table class="expense-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Nominal</th>
                        <th>Metode</th>
                        <th>Petugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>#{{ $expense->id }}</td>
                            <td>{{ $dateLabel($expense->expense_date) }}</td>
                            <td><span class="badge">{{ $expense->category }}</span></td>
                            <td><strong>{{ $expense->description }}</strong></td>
                            <td><strong>{{ $money($expense->amount) }}</strong></td>
                            <td>{{ $expense->payment_method ?: '-' }}</td>
                            <td>{{ $expense->creator?->username ?? $expense->creator?->name ?? '-' }}</td>
                            <td>
                                <div class="action-row">
                                    <a href="{{ url($base.'/'.$expense->id.'/edit') }}">Edit</a>
                                    <form method="POST" action="{{ url($base.'/'.$expense->id) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty-row" colspan="8">Belum ada data pengeluaran pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $expenses->links() }}
        </div>
    </div>
</div>
@endsection
