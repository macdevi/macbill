@extends('layouts.neo')
@section('title', $expense->exists ? 'Edit Pengeluaran' : 'Tambah Pengeluaran')
@section('content')
<div class="pagehead">
    <div>
        <h1>{{ $expense->exists ? 'Edit Pengeluaran' : 'Tambah Pengeluaran' }}</h1>
        <p>Input pengeluaran operasional.</p>
    </div>
    <div class="actions">
        <a class="btn light" href="{{ url($base) }}">Kembali</a>
    </div>
</div>

@if($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif

<form class="card" method="POST" action="{{ $expense->exists ? url($base.'/'.$expense->id) : url($base) }}">
    @csrf
    @if($expense->exists) @method('PUT') @endif

    <div class="formgrid">
        <div class="field">
            <label>Tanggal</label>
            <input class="input" type="date" name="expense_date" value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?: now()->toDateString()) }}">
        </div>

        <div class="field">
            <label>Kategori</label>
            <select class="select" name="category">
                @php $cat = old('category', $expense->category ?: 'Operasional'); @endphp
                <option value="Operasional" @selected($cat === 'Operasional')>Operasional</option>
                <option value="Transport" @selected($cat === 'Transport')>Transport</option>
                <option value="Maintenance" @selected($cat === 'Maintenance')>Maintenance</option>
                <option value="Peralatan" @selected($cat === 'Peralatan')>Peralatan</option>
                <option value="Listrik" @selected($cat === 'Listrik')>Listrik</option>
                <option value="Internet / Backbone" @selected($cat === 'Internet / Backbone')>Internet / Backbone</option>
                <option value="Gaji / Komisi" @selected($cat === 'Gaji / Komisi')>Gaji / Komisi</option>
                <option value="Lainnya" @selected($cat === 'Lainnya')>Lainnya</option>
            </select>
        </div>

        <div class="field full">
            <label>Keterangan</label>
            <input class="input" name="description" value="{{ old('description', $expense->description) }}" placeholder="Contoh: Beli kabel LAN / bensin teknisi / bayar listrik">
        </div>

        <div class="field">
            <label>Nominal</label>
            <input class="input" type="number" name="amount" min="0" value="{{ old('amount', $expense->amount ?: 0) }}">
        </div>

        <div class="field">
            <label>Metode</label>
            @php $method = old('payment_method', $expense->payment_method ?: 'Tunai'); @endphp
            <select class="select" name="payment_method">
                <option value="Tunai" @selected($method === 'Tunai')>Tunai</option>
                <option value="Transfer" @selected($method === 'Transfer')>Transfer</option>
                <option value="QRIS" @selected($method === 'QRIS')>QRIS</option>
                <option value="Lainnya" @selected($method === 'Lainnya')>Lainnya</option>
            </select>
        </div>

        <div class="field full">
            <label>Catatan</label>
            <textarea class="textarea" name="notes" placeholder="Opsional">{{ old('notes', $expense->notes) }}</textarea>
        </div>
    </div>

    <button class="btn" type="submit">Simpan</button>
</form>
@endsection
