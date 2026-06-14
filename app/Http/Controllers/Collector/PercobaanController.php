<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class PercobaanController extends Controller
{
    private function table(array $names): ?string
    {
        foreach ($names as $name) {
            if (Schema::hasTable($name)) {
                return $name;
            }
        }
        return null;
    }

    private function col(?string $table, array $names, ?string $fallback = null): ?string
    {
        if (!$table) return $fallback;

        foreach ($names as $name) {
            if (Schema::hasColumn($table, $name)) {
                return $name;
            }
        }

        return $fallback;
    }

    private function moneyCol(?string $table): ?string
    {
        return $this->col($table, [
            'amount',
            'total_amount',
            'nominal',
            'total',
            'grand_total',
            'bill_amount',
            'price'
        ]);
    }

    private function dateCol(?string $table): ?string
    {
        return $this->col($table, [
            'paid_at',
            'payment_date',
            'paid_date',
            'expense_date',
            'date',
            'tanggal',
            'created_at',
            'updated_at'
        ], 'created_at');
    }

    private function paidWords(): array
    {
        return ['lunas', 'paid', 'bayar awal', 'settled', 'success', 'sukses'];
    }

    private function paidInvoiceQuery($q, ?string $statusCol)
    {
        if (!$statusCol) return $q;

        return $q->where(function ($w) use ($statusCol) {
            foreach ($this->paidWords() as $word) {
                $w->orWhereRaw('LOWER('.$statusCol.') LIKE ?', ['%'.$word.'%']);
            }
        });
    }

    private function unpaidInvoiceQuery($q, ?string $statusCol)
    {
        if (!$statusCol) return $q;

        return $q->where(function ($w) use ($statusCol) {
            foreach ($this->paidWords() as $word) {
                $w->whereRaw('LOWER('.$statusCol.') NOT LIKE ?', ['%'.$word.'%']);
            }
        });
    }

    private function dashboardData(): array
    {
        $invoiceTable = $this->table(['invoices', 'invoice']);
        $paymentTable = $this->table(['payments', 'payment']);
        $expenseTable = $this->table(['expenses', 'pengeluaran']);
        $statusCol = $this->col($invoiceTable, ['status', 'payment_status']);
        $invoiceAmountCol = $this->moneyCol($invoiceTable);
        $paymentAmountCol = $this->moneyCol($paymentTable);
        $expenseAmountCol = $this->moneyCol($expenseTable);
        $paymentDateCol = $this->dateCol($paymentTable);
        $expenseDateCol = $this->dateCol($expenseTable);

        $start = now()->startOfMonth()->toDateTimeString();
        $end = now()->endOfMonth()->toDateTimeString();

        $siapTagih = 0;
        $pendapatanTertunda = 0;
        $nunggak = 0;

        if ($invoiceTable) {
            $q = DB::table($invoiceTable);
            $siapTagih = $this->unpaidInvoiceQuery(clone $q, $statusCol)->count();

            if ($invoiceAmountCol) {
                $pendapatanTertunda = (float) $this->unpaidInvoiceQuery(DB::table($invoiceTable), $statusCol)->sum($invoiceAmountCol);
            }

            if ($statusCol) {
                $nunggak = DB::table($invoiceTable)
                    ->whereRaw('LOWER('.$statusCol.') LIKE ?', ['%nunggak%'])
                    ->orWhereRaw('LOWER('.$statusCol.') LIKE ?', ['%overdue%'])
                    ->count();
            }
        }

        $pemasukanBulanIni = 0;

        if ($paymentTable && $paymentAmountCol) {
            $q = DB::table($paymentTable);
            if ($paymentDateCol && Schema::hasColumn($paymentTable, $paymentDateCol)) {
                $q->whereBetween($paymentDateCol, [$start, $end]);
            }
            $pemasukanBulanIni = (float) $q->sum($paymentAmountCol);
        } elseif ($invoiceTable && $invoiceAmountCol) {
            $q = DB::table($invoiceTable);
            if ($statusCol) {
                $this->paidInvoiceQuery($q, $statusCol);
            }
            $pemasukanBulanIni = (float) $q->sum($invoiceAmountCol);
        }

        $pengeluaranBulanIni = 0;

        if ($expenseTable && $expenseAmountCol) {
            $q = DB::table($expenseTable);
            if ($expenseDateCol && Schema::hasColumn($expenseTable, $expenseDateCol)) {
                $q->whereBetween($expenseDateCol, [$start, $end]);
            }
            $pengeluaranBulanIni = (float) $q->sum($expenseAmountCol);
        }

        $profitBulanIni = $pemasukanBulanIni - $pengeluaranBulanIni;

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $mStart = now()->startOfYear()->month($m)->startOfMonth();
            $mEnd = now()->startOfYear()->month($m)->endOfMonth();

            $income = 0;
            $expense = 0;

            if ($paymentTable && $paymentAmountCol) {
                $q = DB::table($paymentTable);
                if ($paymentDateCol && Schema::hasColumn($paymentTable, $paymentDateCol)) {
                    $q->whereBetween($paymentDateCol, [$mStart->toDateTimeString(), $mEnd->toDateTimeString()]);
                }
                $income = (float) $q->sum($paymentAmountCol);
            }

            if ($expenseTable && $expenseAmountCol) {
                $q = DB::table($expenseTable);
                if ($expenseDateCol && Schema::hasColumn($expenseTable, $expenseDateCol)) {
                    $q->whereBetween($expenseDateCol, [$mStart->toDateTimeString(), $mEnd->toDateTimeString()]);
                }
                $expense = (float) $q->sum($expenseAmountCol);
            }

            $months[$m] = [
                'month_no' => $m,
                'income' => $income,
                'expense' => $expense,
                'profit' => $income - $expense,
            ];
        }

        return [
            'stats' => [
                'siap_tagih' => $siapTagih,
                'pendapatan_tertunda' => $pendapatanTertunda,
                'pemasukan_bulan_ini' => $pemasukanBulanIni,
                'pengeluaran_bulan_ini' => $pengeluaranBulanIni,
                'profit_bulan_ini' => $profitBulanIni,
                'nunggak' => $nunggak,
            ],
            'annualStats' => [
                'year' => now()->year,
                'months' => $months,
            ],
        ];
    }

    public function index()
    {
        $data = $this->dashboardData();
        return view('collector.percobaan.dashboard', $data);
    }

    public function tagihan(Request $request)
    {
        $rows = collect();
        $search = trim((string) $request->get('q', ''));

        if (!Schema::hasTable('invoices')) {
            return view('collector.percobaan.tagihan', compact('rows', 'search'));
        }

        $q = DB::table('invoices as i')
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->leftJoin('packages as p', 'p.id', '=', 'i.package_id')
            ->select(
                'i.id',
                'i.invoice_number',
                'i.customer_id',
                'i.package_id',
                'i.period',
                'i.due_date',
                'i.amount',
                'i.paid_amount',
                'i.status',
                'i.paid_at',
                'c.name as customer_name',
                'c.phone as customer_phone',
                'c.address as customer_address',
                'c.billing_day as customer_billing_day',
                'c.monthly_price as customer_monthly_price',
                'p.name as package_name'
            )
            ->whereIn('i.status', ['Belum Bayar', 'Nunggak'])
            ->whereNull('i.paid_at');

        if ($search !== '') {
            $like = '%'.$search.'%';

            $q->where(function ($w) use ($like) {
                $w->where('c.name', 'like', $like)
                    ->orWhere('c.phone', 'like', $like)
                    ->orWhere('i.invoice_number', 'like', $like)
                    ->orWhere('i.period', 'like', $like);
            });
        }

        $invoiceRows = $q
            ->orderBy('c.name')
            ->orderBy('i.period')
            ->orderBy('i.due_date')
            ->get();

        $rows = $invoiceRows
            ->groupBy('customer_id')
            ->map(function ($items, $customerId) {
                $first = $items->first();

                $detail = $items->map(function ($r) {
                    $amount = (int) ($r->amount ?? 0);
                    $paid = (int) ($r->paid_amount ?? 0);
                    $remaining = max(0, $amount - $paid);

                    return [
                        'id' => $r->id,
                        'invoice_number' => $r->invoice_number,
                        'period' => $r->period,
                        'due_date' => $r->due_date,
                        'amount' => $amount,
                        'paid_amount' => $paid,
                        'remaining' => $remaining,
                        'status' => $r->status,
                    ];
                })->values();

                $totalAmount = $detail->sum('remaining');
                $periodCount = $detail->count();

                $status = $detail->contains(fn ($x) => $x['status'] === 'Nunggak')
                    ? 'Nunggak'
                    : 'Belum Bayar';

                return (object) [
                    'customer_id' => $customerId,
                    'customer_name' => $first->customer_name ?: ('Pelanggan #'.$customerId),
                    'customer_phone' => $first->customer_phone ?: '-',
                    'customer_address' => $first->customer_address ?: '-',
                    'package_name' => $first->package_name ?: '-',
                    'billing_day' => $first->customer_billing_day ?: '-',
                    'monthly_price' => (int) ($first->customer_monthly_price ?? 0),
                    'status' => $status,
                    'total_amount' => $totalAmount,
                    'period_count' => $periodCount,
                    'invoice_count' => $detail->count(),
                    'periods' => $detail->pluck('period')->filter()->values()->all(),
                    'invoice_ids' => $detail->pluck('id')->values()->all(),
                    'details' => $detail->all(),
                ];
            })
            ->values();

        return view('collector.percobaan.tagihan', compact('rows', 'search'));
    }



    public function manual()
    {
        $customers = collect();

        if (Schema::hasTable('customers')) {
            $q = DB::table('customers as c')
                ->leftJoin('packages as p', 'p.id', '=', 'c.package_id')
                ->select(
                    'c.id',
                    'c.name',
                    'c.phone',
                    'c.package_id',
                    'c.billing_day',
                    'c.monthly_price',
                    'p.name as package_name'
                )
                ->orderBy('c.name');

            $customers = $q->limit(1000)->get()->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name ?: ('Pelanggan #'.$c->id),
                    'phone' => $c->phone ?: '',
                    'package_id' => $c->package_id ?: '',
                    'package_name' => $c->package_name ?: '-',
                    'billing_day' => (int) ($c->billing_day ?: 1),
                    'amount' => (int) ($c->monthly_price ?: 0),
                ];
            })->values();
        }

        return view('collector.percobaan.manual', compact('customers'));
    }







    public function storeManual(Request $request)
    {
        if (!Schema::hasTable('invoices')) {
            return back()->with('error', 'Tabel invoices tidak ditemukan.');
        }

        $request->validate([
            'customer_id' => 'required|integer',
            'period' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);

        $customer = DB::table('customers')->where('id', $request->customer_id)->first();

        if (!$customer) {
            return back()->withInput()->with('error', 'Pelanggan tidak ditemukan.');
        }

        try {
            $period = $request->period ?: now()->format('Y-m');
            $year = (int) substr($period, 0, 4);
            $month = (int) substr($period, 5, 2);
            $periodDate = Carbon::create($year, $month, 1);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Format periode tidak valid.');
        }

        $billingDay = (int) ($customer->billing_day ?: 1);
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $billingDay = max(1, min($billingDay, $daysInMonth));

        $dueDate = Carbon::create($year, $month, $billingDay)->startOfDay();

        $amount = (int) ($customer->monthly_price ?: 0);

        if ($amount <= 0) {
            $lastAmount = DB::table('invoices')
                ->where('customer_id', $customer->id)
                ->whereNotNull('amount')
                ->orderByDesc('id')
                ->value('amount');

            $amount = (int) ($lastAmount ?: 0);
        }

        if ($amount <= 0) {
            return back()->withInput()->with('error', 'Nominal pelanggan masih 0. Isi monthly_price di form pelanggan terlebih dahulu.');
        }

        $exists = DB::table('invoices')
            ->where('customer_id', $customer->id)
            ->where('period', $period)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Invoice pelanggan ini untuk periode '.$period.' sudah ada. Sistem tidak membuat invoice dobel.');
        }

        $invoiceNumber = 'INV-'.str_replace('-', '', $period).'-'.str_pad((string) $customer->id, 6, '0', STR_PAD_LEFT);

        $status = $dueDate->lt(now()->startOfDay()) ? 'Nunggak' : 'Belum Bayar';

        $notes = trim((string) $request->description);
        $notes = trim($notes . ($notes ? ' | ' : '') . 'Manual Kasir Percobaan | Periode: '.$period);

        $data = [
            'invoice_number' => $invoiceNumber,
            'customer_id' => $customer->id,
            'package_id' => $customer->package_id,
            'period' => $period,
            'due_date' => $dueDate->toDateString(),
            'amount' => $amount,
            'paid_amount' => 0,
            'status' => $status,
            'notes' => $notes,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            DB::table('invoices')->insert($data);

            if ($status === 'Nunggak') {
                return redirect('/collector/percobaan/tagihan')->with('success', 'Tagihan '.$invoiceNumber.' berhasil dibuat sebagai Nunggak.');
            }

            return redirect('/collector/percobaan/tagihan')->with('success', 'Tagihan '.$invoiceNumber.' berhasil dibuat.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal membuat tagihan: '.$e->getMessage());
        }
    }







    public function pengeluaran(Request $request)
    {
        $expenseTable = $this->table(['expenses', 'pengeluaran']);
        $amountCol = $this->moneyCol($expenseTable);
        $dateCol = $this->dateCol($expenseTable);
        $rows = collect();

        if ($expenseTable) {
            $q = DB::table($expenseTable);
            if ($dateCol && Schema::hasColumn($expenseTable, $dateCol)) {
                $q->orderByDesc($dateCol);
            } else {
                $q->orderByDesc('id');
            }
            $rows = $q->limit(150)->get();
        }

        return view('collector.percobaan.pengeluaran', compact('rows', 'amountCol', 'dateCol'));
    }

    public function storePengeluaran(Request $request)
    {

        // kasir-expense-safe-input-v1-start
        $allowedExpenseCategories = ['Operasional', 'Kebutuhan', 'Gaji', 'Lain-lain'];
        $safeExpenseCategory = trim((string) $request->input('category', ''));
        if (!in_array($safeExpenseCategory, $allowedExpenseCategories, true)) {
            $safeExpenseCategory = 'Lain-lain';
        }

        $safeExpenseDescription = trim((string) $request->input('description', ''));
        if ($safeExpenseDescription === '') {
            $safeExpenseDescription = $safeExpenseCategory !== '' ? $safeExpenseCategory : 'Pengeluaran kasir';
        }

        $safeExpenseNotes = trim((string) $request->input('notes', ''));

        $request->merge([
            'category' => $safeExpenseCategory,
            'description' => $safeExpenseDescription,
            'notes' => $safeExpenseNotes,
        ]);
        // kasir-expense-safe-input-v1-end


        // kasir-expense-description-safe-start
        $safeCategory = trim((string) $request->input('category', ''));
        $safeDescription = trim((string) $request->input('description', ''));
        if ($safeDescription === '') {
            $safeDescription = $safeCategory !== '' ? $safeCategory : 'Pengeluaran kasir';
        }
        $safeNotes = trim((string) $request->input('notes', ''));
        // kasir-expense-description-safe-end

        $expenseTable = $this->table(['expenses', 'pengeluaran']);

        if (!$expenseTable) {
            return back()->with('error', 'Tabel expenses/pengeluaran tidak ditemukan.');
        }

        $amountCol = $this->moneyCol($expenseTable);

        if (!$amountCol) {
            return back()->with('error', 'Kolom nominal pengeluaran tidak ditemukan.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:255',
            'date' => 'nullable|date',
        ]);

        $data = [];
        $data[$amountCol] = $request->amount;

        if (Schema::hasColumn($expenseTable, 'category')) {
            $data['category'] = $request->category ?: 'Operasional';
        }

        if (Schema::hasColumn($expenseTable, 'kategori')) {
            $data['kategori'] = $request->category ?: 'Operasional';
        }

        if (Schema::hasColumn($expenseTable, 'description')) {
            $data['description'] = $request->description;
        }

        if (Schema::hasColumn($expenseTable, 'keterangan')) {
            $data['keterangan'] = $request->description;
        }

        if (Schema::hasColumn($expenseTable, 'notes')) {
            $data['notes'] = $request->description;
        }

        if (Schema::hasColumn($expenseTable, 'date')) {
            $data['date'] = $request->date ?: now()->toDateString();
        }

        if (Schema::hasColumn($expenseTable, 'tanggal')) {
            $data['tanggal'] = $request->date ?: now()->toDateString();
        }

        if (Schema::hasColumn($expenseTable, 'expense_date')) {
            $data['expense_date'] = $request->date ?: now()->toDateString();
        }

        if (Schema::hasColumn($expenseTable, 'collector_id')) {
            $data['collector_id'] = Auth::id();
        }

        if (Schema::hasColumn($expenseTable, 'created_by')) {
            $data['created_by'] = Auth::id();
        }

        if (Schema::hasColumn($expenseTable, 'user_id')) {
            $data['user_id'] = Auth::id();
        }

        if (Schema::hasColumn($expenseTable, 'created_at')) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn($expenseTable, 'updated_at')) {
            $data['updated_at'] = now();
        }

        try {
            DB::table($expenseTable)->insert($data);
            return redirect('/kasir/pengeluaran')->with('success', 'Pengeluaran berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal menambah pengeluaran: '.$e->getMessage());
        }
    }

    public function riwayat(Request $request)
    {
        $paymentTable = $this->table(['payments', 'payment']);
        $invoiceTable = $this->table(['invoices', 'invoice']);
        $customerTable = $this->table(['customers', 'customer']);

        $amountCol = $this->moneyCol($paymentTable);
        $dateCol = $this->dateCol($paymentTable);
        $rows = collect();
        $source = 'payments';

        if ($paymentTable) {
            $q = DB::table($paymentTable.' as p');

            if ($invoiceTable && Schema::hasColumn($paymentTable, 'invoice_id')) {
                $q->leftJoin($invoiceTable.' as i', 'i.id', '=', 'p.invoice_id');

                if ($customerTable && Schema::hasColumn($invoiceTable, 'customer_id')) {
                    $q->leftJoin($customerTable.' as c', 'c.id', '=', 'i.customer_id')
                      ->addSelect('c.name as customer_name');
                }

                $q->addSelect('i.invoice_number as invoice_number');
            }

            $q->addSelect('p.*');

            if ($request->filled('q')) {
                $search = '%'.$request->q.'%';
                $q->where(function ($w) use ($search) {
                    $w->orWhere('c.name', 'like', $search);
                    $w->orWhere('i.invoice_number', 'like', $search);
                });
            }

            if ($dateCol && Schema::hasColumn($paymentTable, $dateCol)) {
                $q->orderByDesc('p.'.$dateCol);
            } else {
                $q->orderByDesc('p.id');
            }

            $rows = $q->limit(150)->get();
        } elseif ($invoiceTable) {
            $source = 'invoices';
            $amountCol = $this->moneyCol($invoiceTable);
            $dateCol = $this->dateCol($invoiceTable);
            $statusCol = $this->col($invoiceTable, ['status', 'payment_status']);

            $q = DB::table($invoiceTable.' as i');

            if ($customerTable && Schema::hasColumn($invoiceTable, 'customer_id')) {
                $q->leftJoin($customerTable.' as c', 'c.id', '=', 'i.customer_id')
                  ->addSelect('c.name as customer_name');
            }

            $q->addSelect('i.*');

            if ($statusCol) {
                $this->paidInvoiceQuery($q, 'i.'.$statusCol);
            }

            if ($dateCol && Schema::hasColumn($invoiceTable, $dateCol)) {
                $q->orderByDesc('i.'.$dateCol);
            } else {
                $q->orderByDesc('i.id');
            }

            $rows = $q->limit(150)->get();
        }

        return view('collector.percobaan.riwayat', compact('rows', 'amountCol', 'dateCol', 'source'));
    }


    public function bayar(Request $request, $invoice)
    {
        if (!Schema::hasTable('invoices')) {
            return back()->with('error', 'Tabel invoices tidak ditemukan.');
        }

        $row = DB::table('invoices')->where('id', $invoice)->first();

        if (!$row) {
            return back()->with('error', 'Invoice tidak ditemukan.');
        }

        $paidStatuses = ['Lunas', 'Bayar Awal'];

        if (in_array($row->status, $paidStatuses, true) || !empty($row->paid_at)) {
            return redirect('/collector/percobaan/tagihan')->with('error', 'Invoice ini sudah dibayar.');
        }

        $amount = (int) ($row->amount ?? 0);
        $alreadyPaid = (int) ($row->paid_amount ?? 0);
        $remaining = max(0, $amount - $alreadyPaid);

        if ($amount <= 0 || $remaining <= 0) {
            return redirect('/collector/percobaan/tagihan')->with('error', 'Invoice tidak memiliki sisa tagihan.');
        }

        $paidAt = now();

        try {
            if (class_exists('\App\Services\BillingService')) {
                $status = \App\Services\BillingService::paidStatusFor($row->due_date, $paidAt);
            } else {
                $status = $paidAt->startOfDay()->lt(Carbon::parse($row->due_date)->startOfDay()) ? 'Bayar Awal' : 'Lunas';
            }
        } catch (\Throwable $e) {
            $status = 'Lunas';
        }

        try {
            DB::transaction(function () use ($row, $invoice, $amount, $status, $paidAt, $request) {
                DB::table('invoices')->where('id', $invoice)->update([
                    'paid_amount' => $amount,
                    'status' => $status,
                    'paid_at' => $paidAt,
                    'payment_method' => $request->input('method', 'Tunai'),
                    'updated_at' => now(),
                ]);

                if (Schema::hasTable('payments')) {
                    $hasPayment = Schema::hasColumn('payments', 'invoice_id')
                        ? DB::table('payments')->where('invoice_id', $invoice)->exists()
                        : false;

                    if (!$hasPayment) {
                        $payment = [];

                        if (Schema::hasColumn('payments', 'invoice_id')) {
                            $payment['invoice_id'] = $invoice;
                        }

                        if (Schema::hasColumn('payments', 'customer_id')) {
                            $payment['customer_id'] = $row->customer_id;
                        }

                        foreach (['amount', 'paid_amount', 'payment_amount', 'nominal', 'total'] as $col) {
                            if (Schema::hasColumn('payments', $col)) {
                                $payment[$col] = $amount;
                                break;
                            }
                        }

                        foreach (['payment_method', 'method'] as $col) {
                            if (Schema::hasColumn('payments', $col)) {
                                $payment[$col] = $request->input('method', 'Tunai');
                            }
                        }

                        foreach (['paid_at', 'payment_date', 'paid_date', 'date', 'tanggal'] as $col) {
                            if (Schema::hasColumn('payments', $col)) {
                                $payment[$col] = $paidAt;
                                break;
                            }
                        }

                        if (Schema::hasColumn('payments', 'status')) {
                            $payment['status'] = $status;
                        }

                        foreach (['collector_id', 'user_id', 'created_by'] as $col) {
                            if (Schema::hasColumn('payments', $col)) {
                                $payment[$col] = Auth::id();
                            }
                        }

                        if (Schema::hasColumn('payments', 'notes')) {
                            $payment['notes'] = 'Pembayaran dari portal percobaan';
                        }

                        if (Schema::hasColumn('payments', 'created_at')) {
                            $payment['created_at'] = now();
                        }

                        if (Schema::hasColumn('payments', 'updated_at')) {
                            $payment['updated_at'] = now();
                        }

                        if (!empty($payment)) {
                            DB::table('payments')->insert($payment);
                        }
                    }
                }
            });

            return redirect('/collector/percobaan/tagihan')->with('success', 'Tagihan berhasil dibayar sebagai '.$status.'.');
        } catch (\Throwable $e) {
            return redirect('/collector/percobaan/tagihan')->with('error', 'Gagal memproses pembayaran: '.$e->getMessage());
        }
    }




        public function profile()
    {
        $user = Auth::user();
        return view('collector.percobaan.profile', compact('user'));
    }





public function bayarGabungan(Request $request)
    {
        if (!Schema::hasTable('invoices')) {
            return redirect('/collector/percobaan/tagihan')->with('error', 'Tabel invoices tidak ditemukan.');
        }

        $raw = $request->get('invoice_ids', '');

        if (is_array($raw)) {
            $ids = $raw;
        } else {
            $ids = preg_split('/[,\s]+/', (string) $raw);
        }

        $ids = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (!$ids) {
            return redirect('/collector/percobaan/tagihan')->with('error', 'Pilih minimal satu periode/invoice yang akan dibayar.');
        }

        $invoices = DB::table('invoices')
            ->whereIn('id', $ids)
            ->whereIn('status', ['Belum Bayar', 'Nunggak'])
            ->whereNull('paid_at')
            ->orderBy('period')
            ->get();

        if ($invoices->isEmpty()) {
            return redirect('/collector/percobaan/tagihan')->with('error', 'Tidak ada invoice terbuka yang bisa dibayar.');
        }

        $paidAt = now();
        $count = 0;
        $total = 0;

        try {
            DB::transaction(function () use ($invoices, $paidAt, &$count, &$total) {
                foreach ($invoices as $row) {
                    $amount = (int) ($row->amount ?? 0);
                    $paid = (int) ($row->paid_amount ?? 0);
                    $remaining = max(0, $amount - $paid);

                    if ($amount <= 0 || $remaining <= 0) {
                        continue;
                    }

                    try {
                        if (class_exists('\App\Services\BillingService')) {
                            $status = \App\Services\BillingService::paidStatusFor($row->due_date, $paidAt);
                        } else {
                            $status = $paidAt->copy()->startOfDay()->lt(Carbon::parse($row->due_date)->startOfDay())
                                ? 'Bayar Awal'
                                : 'Lunas';
                        }
                    } catch (\Throwable $e) {
                        $status = 'Lunas';
                    }

                    DB::table('invoices')->where('id', $row->id)->update([
                        'paid_amount' => $amount,
                        'status' => $status,
                        'paid_at' => $paidAt,
                        'payment_method' => 'Tunai',
                        'updated_at' => now(),
                    ]);

                    if (Schema::hasTable('payments')) {
                        $hasPayment = Schema::hasColumn('payments', 'invoice_id')
                            ? DB::table('payments')->where('invoice_id', $row->id)->exists()
                            : false;

                        if (!$hasPayment) {
                            $payment = [];

                            if (Schema::hasColumn('payments', 'invoice_id')) {
                                $payment['invoice_id'] = $row->id;
                            }

                            if (Schema::hasColumn('payments', 'customer_id')) {
                                $payment['customer_id'] = $row->customer_id;
                            }

                            foreach (['amount', 'paid_amount', 'payment_amount', 'nominal', 'total'] as $col) {
                                if (Schema::hasColumn('payments', $col)) {
                                    $payment[$col] = $amount;
                                    break;
                                }
                            }

                            foreach (['payment_method', 'method'] as $col) {
                                if (Schema::hasColumn('payments', $col)) {
                                    $payment[$col] = 'Tunai';
                                }
                            }

                            foreach (['paid_at', 'payment_date', 'paid_date', 'date', 'tanggal'] as $col) {
                                if (Schema::hasColumn('payments', $col)) {
                                    $payment[$col] = $paidAt;
                                    break;
                                }
                            }

                            if (Schema::hasColumn('payments', 'status')) {
                                $payment['status'] = $status;
                            }

                            foreach (['collector_id', 'user_id', 'created_by'] as $col) {
                                if (Schema::hasColumn('payments', $col)) {
                                    $payment[$col] = Auth::id();
                                }
                            }

                            if (Schema::hasColumn('payments', 'notes')) {
                                $payment['notes'] = 'Pembayaran gabungan dari portal percobaan';
                            }

                            if (Schema::hasColumn('payments', 'created_at')) {
                                $payment['created_at'] = now();
                            }

                            if (Schema::hasColumn('payments', 'updated_at')) {
                                $payment['updated_at'] = now();
                            }

                            if (!empty($payment)) {
                                DB::table('payments')->insert($payment);
                            }
                        }
                    }

                    $count++;
                    $total += $amount;
                }
            });

            if ($count <= 0) {
                return redirect('/collector/percobaan/tagihan')->with('error', 'Tidak ada periode yang berhasil dibayar.');
            }

            return redirect('/collector/percobaan/tagihan')->with('success', $count.' periode berhasil dibayar. Total Rp '.number_format($total, 0, ',', '.').'.');
        } catch (\Throwable $e) {
            return redirect('/collector/percobaan/tagihan')->with('error', 'Gagal memproses pembayaran gabungan: '.$e->getMessage());
        }
    }


public function statusPelanggan(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $rows = collect();

        if (!Schema::hasTable('customers')) {
            return view('collector.percobaan.status-pelanggan', compact('rows', 'search'));
        }

        $q = DB::table('customers as c')
            ->leftJoin('packages as p', 'p.id', '=', 'c.package_id')
            ->select(
                'c.id',
                'c.name',
                'c.phone',
                'c.address',
                'c.package_id',
                'c.billing_day',
                'c.monthly_price',
                'c.status as customer_status',
                'c.pppoe_username',
                'c.pppoe_online_status',
                'c.pppoe_online_at',
                'c.pppoe_last_seen_at',
                'c.pppoe_remote_address',
                'c.pppoe_caller_id',
                'c.pppoe_uptime',
                'p.name as package_name',
                'p.speed as package_speed'
            )
            ->orderBy('c.name');

        if ($search !== '') {
            $like = '%'.$search.'%';

            $q->where(function ($w) use ($like) {
                $w->where('c.name', 'like', $like)
                    ->orWhere('c.phone', 'like', $like)
                    ->orWhere('c.address', 'like', $like)
                    ->orWhere('c.pppoe_username', 'like', $like)
                    ->orWhere('p.name', 'like', $like);
            });
        }

        $customers = $q->limit(2000)->get();
        $customerIds = $customers->pluck('id')->values()->all();

        $invoiceMap = collect();

        if (Schema::hasTable('invoices') && $customerIds) {
            $invoiceMap = DB::table('invoices')
                ->whereIn('customer_id', $customerIds)
                ->select(
                    'id',
                    'invoice_number',
                    'customer_id',
                    'period',
                    'due_date',
                    'amount',
                    'paid_amount',
                    'status',
                    'paid_at',
                    'payment_method'
                )
                ->orderByRaw("CASE status WHEN 'Nunggak' THEN 1 WHEN 'Belum Bayar' THEN 2 WHEN 'Bayar Awal' THEN 3 WHEN 'Lunas' THEN 4 ELSE 5 END")
                ->orderByDesc('period')
                ->orderByDesc('id')
                ->get()
                ->groupBy('customer_id');
        }

        $rows = $customers->map(function ($c) use ($invoiceMap) {
            $invoices = $invoiceMap->get($c->id, collect());

            $paymentStatus = '-';

            if ($invoices->count() > 0) {
                if ($invoices->where('status', 'Nunggak')->count() > 0) {
                    $paymentStatus = 'Nunggak';
                } elseif ($invoices->where('status', 'Belum Bayar')->count() > 0) {
                    $paymentStatus = 'Belum Bayar';
                } else {
                    $paymentStatus = $invoices->first()->status ?: '-';
                }
            }

            $rawConnection = strtolower((string) ($c->pppoe_online_status ?? ''));

            if (in_array($rawConnection, ['online', 'aktif', 'active', 'up', 'connected'], true)) {
                $connectionStatus = 'Aktif';
            } elseif (in_array($rawConnection, ['offline', 'down', 'inactive', 'disconnected'], true)) {
                $connectionStatus = 'Offline';
            } else {
                $connectionStatus = '-';
            }

            $latestInvoice = $invoices->first();

            return (object) [
                'id' => $c->id,
                'name' => $c->name ?: ('Pelanggan #'.$c->id),
                'phone' => $c->phone ?: '-',
                'address' => $c->address ?: '-',
                'package_name' => $c->package_name ?: '-',
                'package_speed' => $c->package_speed ?: '-',
                'billing_day' => $c->billing_day ?: '-',
                'monthly_price' => (int) ($c->monthly_price ?? 0),
                'customer_status' => $c->customer_status ?: '-',
                'payment_status' => $paymentStatus,
                'connection_status' => $connectionStatus,
                'pppoe_username' => $c->pppoe_username ?: '-',
                'pppoe_online_status' => $c->pppoe_online_status ?: '-',
                'pppoe_online_at' => $c->pppoe_online_at ?: '-',
                'pppoe_last_seen_at' => $c->pppoe_last_seen_at ?: '-',
                'pppoe_remote_address' => $c->pppoe_remote_address ?: '-',
                'pppoe_caller_id' => $c->pppoe_caller_id ?: '-',
                'pppoe_uptime' => $c->pppoe_uptime ?: '-',
                'invoice_count' => $invoices->count(),
                'open_invoice_count' => $invoices->whereIn('status', ['Belum Bayar', 'Nunggak'])->count(),
                'latest_invoice_number' => $latestInvoice->invoice_number ?? '-',
                'latest_invoice_period' => $latestInvoice->period ?? '-',
                'latest_invoice_due_date' => $latestInvoice->due_date ?? '-',
                'latest_invoice_amount' => (int) ($latestInvoice->amount ?? 0),
                'latest_invoice_paid_amount' => (int) ($latestInvoice->paid_amount ?? 0),
                'latest_invoice_status' => $latestInvoice->status ?? '-',
                'latest_invoice_paid_at' => $latestInvoice->paid_at ?? '-',
            ];
        })->values();

        return view('collector.percobaan.status-pelanggan', compact('rows', 'search'));
    }


    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'name' => 'required|string|max:120',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'remove_profile_photo' => 'nullable',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:6|confirmed',
        ];

        if (Schema::hasColumn('users', 'email')) {
            $rules['email'] = 'nullable|email|max:190';
        }

        $request->validate($rules);

        $user->name = trim((string) $request->input('name', $user->name));

        if (Schema::hasColumn('users', 'email') && $request->has('email')) {
            $email = trim((string) $request->input('email', ''));
            if ($email !== '') {
                $user->email = $email;
            }
        }

        $oldPhoto = $user->profile_photo_path ?? null;

        if ($request->boolean('remove_profile_photo')) {
            if ($oldPhoto) {
                $oldPath = public_path(ltrim($oldPhoto, '/'));
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }

                $oldStoragePath = public_path('storage/'.ltrim($oldPhoto, '/'));
                if (is_file($oldStoragePath)) {
                    @unlink($oldStoragePath);
                }
            }

            $user->profile_photo_path = null;
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');

            if (!$file->isValid()) {
                return back()->withInput()->with('error', 'Upload foto tidak valid. Coba gunakan foto lain.');
            }

            $uploadDir = public_path('uploads/collector_profiles');

            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0775, true);
            }

            if (!is_writable($uploadDir)) {
                @chmod($uploadDir, 0775);
            }

            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $ext = 'jpg';
            }

            $filename = 'profile_'.$user->id.'_'.date('YmdHis').'_'.bin2hex(random_bytes(4)).'.'.$ext;

            try {
                $file->move($uploadDir, $filename);
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Gagal menyimpan foto: '.$e->getMessage());
            }

            if ($oldPhoto) {
                $oldPath = public_path(ltrim($oldPhoto, '/'));
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }

                $oldStoragePath = public_path('storage/'.ltrim($oldPhoto, '/'));
                if (is_file($oldStoragePath)) {
                    @unlink($oldStoragePath);
                }
            }

            $user->profile_photo_path = 'uploads/collector_profiles/'.$filename;
        }

        if ($request->filled('new_password')) {
            if (!$request->filled('current_password')) {
                return back()->withInput()->with('error', 'Isi password saat ini untuk mengganti password.');
            }

            if (!Hash::check((string) $request->input('current_password'), (string) $user->password)) {
                return back()->withInput()->with('error', 'Password saat ini tidak cocok.');
            }

            $user->password = Hash::make((string) $request->input('new_password'));
        }

        try {
            $user->save();
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan profile: '.$e->getMessage());
        }

        return back()->with('success', 'Profile berhasil diperbarui.');
    }





    // kasir-expense-crud-methods-v1-start

    public function updatePengeluaran(\Illuminate\Http\Request $request, $expense)
    {
        $id = (int) $expense;

        if ($id <= 0) {
            return redirect('/kasir/pengeluaran')->with('error', 'Data pengeluaran tidak valid.');
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('expenses')) {
            return redirect('/kasir/pengeluaran')->with('error', 'Tabel pengeluaran tidak ditemukan.');
        }

        $exists = \Illuminate\Support\Facades\DB::table('expenses')->where('id', $id)->exists();

        if (!$exists) {
            return redirect('/kasir/pengeluaran')->with('error', 'Data pengeluaran tidak ditemukan.');
        }

        $allowed = ['Operasional', 'Kebutuhan', 'Gaji', 'Lain-lain'];

        $category = trim((string) $request->input('category', ''));
        if (!in_array($category, $allowed, true)) {
            $category = 'Lain-lain';
        }

        $amountRaw = preg_replace('/[^0-9]/', '', (string) $request->input('amount', '0'));
        $amount = (int) ($amountRaw ?: 0);

        if ($amount <= 0) {
            return redirect('/kasir/pengeluaran')->with('error', 'Nominal pengeluaran wajib lebih dari 0.');
        }

        $description = trim((string) $request->input('description', ''));
        if ($description === '') {
            $description = $category;
        }

        $notes = trim((string) $request->input('notes', ''));
        $expenseDate = trim((string) $request->input('expense_date', ''));
        if ($expenseDate === '') {
            $expenseDate = date('Y-m-d');
        }

        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('expenses');
        $has = fn ($col) => in_array($col, $columns, true);

        $data = [];

        if ($has('amount')) {
            $data['amount'] = $amount;
        } elseif ($has('nominal')) {
            $data['nominal'] = $amount;
        }

        if ($has('category')) {
            $data['category'] = $category;
        }

        if ($has('description')) {
            $data['description'] = $description;
        }

        if ($has('notes')) {
            $data['notes'] = $notes;
        }

        if ($has('expense_date')) {
            $data['expense_date'] = $expenseDate;
        } elseif ($has('date')) {
            $data['date'] = $expenseDate;
        }

        if ($has('updated_at')) {
            $data['updated_at'] = now();
        }

        try {
            \Illuminate\Support\Facades\DB::table('expenses')->where('id', $id)->update($data);
            return redirect('/kasir/pengeluaran')->with('success', 'Pengeluaran berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect('/kasir/pengeluaran')->with('error', 'Gagal memperbarui pengeluaran: '.$e->getMessage());
        }
    }

    public function deletePengeluaran($expense)
    {
        $id = (int) $expense;

        if ($id <= 0) {
            return redirect('/kasir/pengeluaran')->with('error', 'Data pengeluaran tidak valid.');
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('expenses')) {
            return redirect('/kasir/pengeluaran')->with('error', 'Tabel pengeluaran tidak ditemukan.');
        }

        try {
            $deleted = \Illuminate\Support\Facades\DB::table('expenses')->where('id', $id)->delete();

            if (!$deleted) {
                return redirect('/kasir/pengeluaran')->with('error', 'Data pengeluaran tidak ditemukan.');
            }

            return redirect('/kasir/pengeluaran')->with('success', 'Pengeluaran berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect('/kasir/pengeluaran')->with('error', 'Gagal menghapus pengeluaran: '.$e->getMessage());
        }
    }

    // kasir-expense-crud-methods-v1-end

}
