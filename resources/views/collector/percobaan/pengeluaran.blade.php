@extends('collector.percobaan.layout')
@section('title','Pengeluaran Percobaan')

@section('content')
@include('collector.percobaan._page_style')

<section class="page-head">
    <h1>Pengeluaran</h1>
    <p>Tambah dan lihat pengeluaran dari portal percobaan.</p>
</section>

@if(session('success')) <div class="alert-ok">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert-error">{{ session('error') }}</div> @endif

<div class="trial-card-page">
    <div class="trial-card-body">
        <form class="trial-form" method="POST" action="{{ url('/kasir/pengeluaran') }}">
            @csrf

            <label>
                Tanggal
                <input type="date" name="date" value="{{ now()->toDateString() }}">
            </label>

            <label>
                Kategori
                

<select name="category" required>
    @php($selectedCategory = old('category', 'Operasional'))
    @foreach(['Operasional', 'Kebutuhan', 'Gaji', 'Lain-lain'] as $cat)
        <option value="{{ $cat }}" @selected($selectedCategory === $cat)>{{ $cat }}</option>
    @endforeach
</select>


            </label>

            <label>
                Nominal
                <input type="number" name="amount" min="0" step="1000" placeholder="Contoh: 50000" required>
            </label>

            <label>
                Keterangan
                <textarea name="description" placeholder="Keterangan pengeluaran wajib diisi" required></textarea>
            </label>

            <button class="trial-btn" type="submit">Tambah Pengeluaran</button>
        </form>
    </div>
</div>

<div class="trial-card-page">
    <div class="trial-table-wrap">
        <table class="trial-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Nominal</th>
                
                    <th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>#{{ $row->id ?? '-' }}</td>
                        <td>{{ $dateCol ? ($row->{$dateCol} ?? '-') : '-' }}</td>
                        <td>{{ $row->description ?? $row->keterangan ?? $row->category ?? $row->kategori ?? '-' }}</td>
                        <td class="money">Rp {{ number_format((float) ($amountCol ? ($row->{$amountCol} ?? 0) : 0), 0, ',', '.') }}</td>
                    
                <td class="expense-crud-actions-v1">
                    <div class="expense-action-wrap">
                        <button
                            type="button"
                            class="expense-action-btn edit js-expense-edit"
                            data-id="{{ $row->id ?? '' }}"
                            data-date="{{ $row->{$dateCol} ?? $row->expense_date ?? $row->date ?? '' }}"
                            data-category="{{ $row->category ?? 'Operasional' }}"
                            data-amount="{{ $row->{$amountCol} ?? $row->amount ?? $row->nominal ?? 0 }}"
                            data-description="{{ $row->description ?? '' }}"
                            data-notes="{{ $row->notes ?? '' }}"
                        >Edit</button>

                        <form method="POST" action="{{ url('/kasir/pengeluaran/'.($row->id ?? 0).'/delete') }}" onsubmit="return confirm('Hapus pengeluaran ini?');">
                            @csrf
                            <button type="submit" class="expense-action-btn delete">Hapus</button>
                        </form>
                    </div>
                </td>

</tr>
                @empty
                    <tr>
                        <td colspan="5">Data pengeluaran belum ada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


<div class="expense-edit-modal" id="expenseEditModal" aria-hidden="true">
    <div class="expense-edit-card">
        <div class="expense-edit-head">
            <h2>Edit Pengeluaran</h2>
            <button type="button" class="expense-edit-close" id="expenseEditClose">×</button>
        </div>

        <form method="POST" id="expenseEditForm" action="{{ url('/kasir/pengeluaran/0/update') }}">
            @csrf

            <label>Tanggal</label>
            <input type="date" name="expense_date" id="editExpenseDate">

            <label>Kategori</label>
            <select name="category" id="editExpenseCategory" required>
                <option value="Operasional">Operasional</option>
                <option value="Kebutuhan">Kebutuhan</option>
                <option value="Gaji">Gaji</option>
                <option value="Lain-lain">Lain-lain</option>
            </select>

            <label>Nominal</label>
            <input type="number" name="amount" id="editExpenseAmount" min="1" required>

            <label>Keterangan</label>
            <textarea name="description" id="editExpenseDescription" required></textarea>

            <label>Catatan</label>
            <textarea name="notes" id="editExpenseNotes"></textarea>

            <button class="trial-btn" type="submit">Simpan Perubahan</button>
        </form>
    </div>
</div>

<style id="expense-edit-modal-v1">
.expense-action-wrap{
    display:flex;
    gap:8px;
    align-items:center;
    justify-content:flex-start;
    flex-wrap:wrap;
}
.expense-action-btn{
    border:0;
    border-radius:12px;
    padding:8px 12px;
    font-weight:800;
    cursor:pointer;
    font-size:12px;
}
.expense-action-btn.edit{
    background:#dbeafe;
    color:#1e40af;
}
.expense-action-btn.delete{
    background:#fee2e2;
    color:#991b1b;
}
.expense-edit-modal{
    position:fixed;
    inset:0;
    z-index:9999;
    background:rgba(15,23,42,.55);
    display:none;
    align-items:center;
    justify-content:center;
    padding:18px;
}
.expense-edit-modal.show{
    display:flex;
}
.expense-edit-card{
    width:min(520px,100%);
    max-height:90vh;
    overflow:auto;
    background:#fff;
    color:#111827;
    border-radius:22px;
    padding:18px;
    box-shadow:0 24px 70px rgba(0,0,0,.35);
}
.expense-edit-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:14px;
}
.expense-edit-head h2{
    margin:0;
    font-size:20px;
}
.expense-edit-close{
    width:38px;
    height:38px;
    border-radius:12px;
    border:0;
    cursor:pointer;
    font-size:26px;
    line-height:1;
    background:#f1f5f9;
    color:#0f172a;
}
.expense-edit-card form{
    display:grid;
    gap:10px;
}
.expense-edit-card label{
    font-weight:800;
    font-size:13px;
}
.expense-edit-card input,
.expense-edit-card select,
.expense-edit-card textarea{
    width:100%;
    border:1px solid #d1d5db;
    border-radius:14px;
    padding:11px 12px;
    font:inherit;
}
.expense-edit-card textarea{
    min-height:76px;
}
@media(max-width:640px){
    .expense-action-wrap{
        flex-direction:column;
        align-items:stretch;
    }
    .expense-action-btn{
        width:100%;
    }
}
</style>

<script id="expense-edit-modal-script-v1">
(function(){
    var modal = document.getElementById('expenseEditModal');
    var close = document.getElementById('expenseEditClose');
    var form = document.getElementById('expenseEditForm');

    if(!modal || !form) return;

    function setValue(id, value){
        var el = document.getElementById(id);
        if(el) el.value = value || '';
    }

    document.querySelectorAll('.js-expense-edit').forEach(function(btn){
        btn.addEventListener('click', function(){
            var id = btn.getAttribute('data-id') || '0';

            form.action = "{{ url('/kasir/pengeluaran') }}/" + id + "/update";

            setValue('editExpenseDate', btn.getAttribute('data-date'));
            setValue('editExpenseCategory', btn.getAttribute('data-category') || 'Operasional');
            setValue('editExpenseAmount', btn.getAttribute('data-amount'));
            setValue('editExpenseDescription', btn.getAttribute('data-description'));
            setValue('editExpenseNotes', btn.getAttribute('data-notes'));

            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        });
    });

    function hide(){
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
    }

    if(close) close.addEventListener('click', hide);

    modal.addEventListener('click', function(e){
        if(e.target === modal) hide();
    });
})();
</script>


<!-- kasir-expense-popup-v1-start -->
@if(session('success') || session('error'))
<div class="kasir-expense-popup-backdrop" id="kasirExpensePopup">
    <div class="kasir-expense-popup-card {{ session('error') ? 'is-error' : 'is-success' }}">
        <div class="kasir-expense-popup-icon">
            @if(session('error'))
                !
            @else
                ✓
            @endif
        </div>

        <h2>{{ session('error') ? 'Gagal' : 'Berhasil' }}</h2>

        <p>
            {{ session('success') ?? session('error') }}
        </p>

        <button type="button" id="kasirExpensePopupClose">OK</button>
    </div>
</div>

<style id="kasir-expense-popup-style-v1">
.kasir-expense-popup-backdrop{
    position:fixed;
    inset:0;
    z-index:99999;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:22px;
    background:rgba(2,6,23,.66);
    backdrop-filter:blur(8px);
}
.kasir-expense-popup-card{
    width:min(430px,100%);
    border-radius:26px;
    padding:28px 22px 22px;
    text-align:center;
    background:linear-gradient(180deg,#101827,#0b1220);
    color:#fff;
    border:1px solid rgba(255,255,255,.12);
    box-shadow:0 30px 80px rgba(0,0,0,.42);
}
.kasir-expense-popup-card.is-success .kasir-expense-popup-icon{
    background:linear-gradient(135deg,#16a34a,#22c55e);
}
.kasir-expense-popup-card.is-error .kasir-expense-popup-icon{
    background:linear-gradient(135deg,#e11d48,#fb7185);
}
.kasir-expense-popup-icon{
    width:82px;
    height:82px;
    margin:0 auto 18px;
    border-radius:999px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:42px;
    font-weight:900;
    color:#fff;
    box-shadow:0 16px 34px rgba(0,0,0,.28);
}
.kasir-expense-popup-card h2{
    margin:0 0 10px;
    font-size:26px;
    font-weight:900;
}
.kasir-expense-popup-card p{
    margin:0;
    color:#e5e7eb;
    font-size:15px;
    line-height:1.55;
    font-weight:700;
    word-break:break-word;
}
.kasir-expense-popup-card button{
    width:100%;
    margin-top:22px;
    border:0;
    border-radius:18px;
    padding:15px 18px;
    cursor:pointer;
    background:linear-gradient(135deg,#f7d774,#f5b942);
    color:#111827;
    font-size:16px;
    font-weight:900;
}
</style>

<script id="kasir-expense-popup-script-v1">
(function(){
    var popup = document.getElementById('kasirExpensePopup');
    var close = document.getElementById('kasirExpensePopupClose');

    if(!popup || !close) return;

    close.addEventListener('click', function(){
        popup.style.display = 'none';
    });

    popup.addEventListener('click', function(e){
        if(e.target === popup){
            popup.style.display = 'none';
        }
    });
})();
</script>
@endif
<!-- kasir-expense-popup-v1-end -->

