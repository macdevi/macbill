@extends('collector.percobaan.layout')
@section('title','Riwayat Pembayaran Percobaan')

@section('content')
@include('collector.percobaan._page_style')

<section class="page-head">
    <h1>Riwayat</h1>
    <p>Riwayat pembayaran dari data pembayaran/invoice lunas.</p>
</section>

<form class="trial-filter" method="GET">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari pelanggan atau invoice...">
    <button type="submit">Cari</button>
</form>

<div class="trial-card-page">
    <div class="trial-table-wrap">
        <table class="trial-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Invoice</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>#{{ $row->id ?? '-' }}</td>
                        <td>{{ $dateCol ? ($row->{$dateCol} ?? '-') : '-' }}</td>
                        <td>{{ $row->customer_name ?? $row->name ?? '-' }}</td>
                        <td>{{ $row->invoice_number ?? $row->number ?? $row->no_invoice ?? '-' }}</td>
                        <td class="money">Rp {{ number_format((float) ($amountCol ? ($row->{$amountCol} ?? 0) : 0), 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Riwayat pembayaran belum ada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
