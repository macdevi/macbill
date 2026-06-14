<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Odp;
use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MacBillingAuditCommand extends Command
{
    protected $signature = 'macbilling:audit {--fix-status : Koreksi status invoice berbayar jika tidak sesuai aturan Bayar Awal/Lunas}';

    protected $description = 'Audit data MAC Billing sebelum rombak sistem.';

    public function handle(): int
    {
        $this->line('');
        $this->info('MAC Billing Audit');
        $this->line('Tanggal aplikasi: '.now()->toDateTimeString().' | Timezone: '.config('app.timezone'));
        $this->line(str_repeat('-', 72));

        $problems = 0;

        $problems += $this->auditDuplicateInvoices();
        $problems += $this->auditDuplicateInvoiceNumbers();
        $problems += $this->auditPaidInvoiceStatus();
        $problems += $this->auditPaymentConsistency();
        $problems += $this->auditCustomerBillingData();
        $problems += $this->auditOdpPorts();
        $problems += $this->auditPppoeUsernames();

        $this->line(str_repeat('-', 72));

        if ($problems > 0) {
            $this->warn('Audit selesai. Ditemukan '.$problems.' kelompok masalah/peringatan.');
            $this->line('Gunakan hasil ini sebagai daftar bersih-bersih sebelum rombak full.');
        } else {
            $this->info('Audit selesai. Tidak ditemukan masalah utama.');
        }

        $this->line('');

        return self::SUCCESS;
    }

    private function auditDuplicateInvoices(): int
    {
        $rows = Invoice::query()
            ->selectRaw('customer_id, period, COUNT(*) as total')
            ->groupBy('customer_id', 'period')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        return $this->printRows(
            'Invoice dobel per pelanggan/periode',
            $rows,
            fn ($r) => 'customer_id='.$r->customer_id.' | period='.$r->period.' | total='.$r->total
        );
    }

    private function auditDuplicateInvoiceNumbers(): int
    {
        $rows = Invoice::query()
            ->selectRaw('invoice_number, COUNT(*) as total')
            ->groupBy('invoice_number')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        return $this->printRows(
            'Nomor invoice dobel',
            $rows,
            fn ($r) => 'invoice_number='.$r->invoice_number.' | total='.$r->total
        );
    }

    private function auditPaidInvoiceStatus(): int
    {
        $problems = 0;
        $fixed = 0;

        $wrongPaidStatus = collect();

        Invoice::query()
            ->whereNotNull('paid_at')
            ->orderBy('id')
            ->chunkById(200, function ($invoices) use (&$wrongPaidStatus, &$fixed) {
                foreach ($invoices as $invoice) {
                    $expected = BillingService::paidStatusFor($invoice->due_date, $invoice->paid_at);

                    if ($invoice->status !== $expected) {
                        $wrongPaidStatus->push((object) [
                            'id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'status' => $invoice->status,
                            'expected' => $expected,
                            'due_date' => $invoice->due_date,
                            'paid_at' => $invoice->paid_at,
                        ]);

                        if ($this->option('fix-status')) {
                            $invoice->update(['status' => $expected]);
                            $fixed++;
                        }
                    }
                }
            });

        $problems += $this->printRows(
            'Invoice berbayar dengan status tidak sesuai',
            $wrongPaidStatus,
            fn ($r) => 'id='.$r->id.' | '.$r->invoice_number.' | '.$r->status.' -> '.$r->expected.' | due='.$r->due_date.' | paid='.$r->paid_at
        );

        if ($fixed > 0) {
            $this->info('Status invoice dikoreksi: '.$fixed);
        }

        $paidStatusWithoutPaidAt = Invoice::query()
            ->whereIn('status', BillingService::paidStatuses())
            ->whereNull('paid_at')
            ->get(['id', 'invoice_number', 'status', 'due_date']);

        $problems += $this->printRows(
            'Status berbayar tetapi paid_at kosong',
            $paidStatusWithoutPaidAt,
            fn ($r) => 'id='.$r->id.' | '.$r->invoice_number.' | status='.$r->status.' | due='.$r->due_date
        );

        $paidAmountTooLarge = Invoice::query()
            ->whereColumn('paid_amount', '>', 'amount')
            ->get(['id', 'invoice_number', 'amount', 'paid_amount']);

        $problems += $this->printRows(
            'Paid amount lebih besar dari nominal invoice',
            $paidAmountTooLarge,
            fn ($r) => 'id='.$r->id.' | '.$r->invoice_number.' | amount='.$r->amount.' | paid_amount='.$r->paid_amount
        );

        return $problems;
    }

    private function auditPaymentConsistency(): int
    {
        $problems = 0;

        $multiPayment = Payment::query()
            ->selectRaw('invoice_id, COUNT(*) as total, SUM(amount) as sum_amount')
            ->groupBy('invoice_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $problems += $this->printRows(
            'Invoice dengan lebih dari satu payment',
            $multiPayment,
            fn ($r) => 'invoice_id='.$r->invoice_id.' | total_payment='.$r->total.' | sum_amount='.$r->sum_amount
        );

        $mismatch = DB::table('invoices')
            ->leftJoin(DB::raw('(SELECT invoice_id, SUM(amount) as sum_payment FROM payments GROUP BY invoice_id) p'), 'p.invoice_id', '=', 'invoices.id')
            ->whereNotNull('invoices.paid_at')
            ->whereRaw('COALESCE(p.sum_payment, 0) != invoices.paid_amount')
            ->selectRaw('invoices.id, invoices.invoice_number, invoices.paid_amount, COALESCE(p.sum_payment, 0) as sum_payment')
            ->get();

        $problems += $this->printRows(
            'Total payment tidak sama dengan paid_amount invoice',
            $mismatch,
            fn ($r) => 'id='.$r->id.' | '.$r->invoice_number.' | paid_amount='.$r->paid_amount.' | sum_payment='.$r->sum_payment
        );

        return $problems;
    }

    private function auditCustomerBillingData(): int
    {
        $problems = 0;

        $noPrice = Customer::query()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('monthly_price')->orWhere('monthly_price', '<', 0);
            })
            ->get(['id', 'name', 'monthly_price', 'status']);

        $problems += $this->printRows(
            'Pelanggan aktif dengan nominal kosong/negatif',
            $noPrice,
            fn ($r) => 'id='.$r->id.' | '.$r->name.' | monthly_price='.$r->monthly_price
        );

        $freeCount = Customer::query()
            ->where('status', 'active')
            ->where('monthly_price', 0)
            ->count();

        if ($freeCount > 0) {
            $this->info('[OK] Pelanggan gratis aktif = '.$freeCount.' pelanggan. Tidak dibuatkan invoice.');
        }

        $badBillingDay = Customer::query()
            ->where(function ($q) {
                $q->whereNull('billing_day')->orWhere('billing_day', '<', 1)->orWhere('billing_day', '>', 31);
            })
            ->get(['id', 'name', 'billing_day']);

        $problems += $this->printRows(
            'Billing day tidak valid',
            $badBillingDay,
            fn ($r) => 'id='.$r->id.' | '.$r->name.' | billing_day='.$r->billing_day
        );

        return $problems;
    }

    private function auditOdpPorts(): int
    {
        $problems = 0;

        if (!Schema::hasColumn('customers', 'odp_id') || !Schema::hasColumn('customers', 'port_number')) {
            $this->warn('Skip audit ODP port: kolom odp_id/port_number belum ada.');
            return 1;
        }

        $duplicatePorts = Customer::query()
            ->selectRaw('odp_id, port_number, COUNT(*) as total')
            ->whereNotNull('odp_id')
            ->whereNotNull('port_number')
            ->groupBy('odp_id', 'port_number')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $problems += $this->printRows(
            'Port ODP dipakai lebih dari satu pelanggan',
            $duplicatePorts,
            fn ($r) => 'odp_id='.$r->odp_id.' | port='.$r->port_number.' | total='.$r->total
        );

        if (Schema::hasColumn('odps', 'port_count')) {
            $overPort = Customer::query()
                ->join('odps', 'odps.id', '=', 'customers.odp_id')
                ->whereNotNull('customers.port_number')
                ->whereColumn('customers.port_number', '>', 'odps.port_count')
                ->selectRaw('customers.id, customers.name, customers.port_number, odps.name as odp_name, odps.port_count')
                ->get();

            $problems += $this->printRows(
                'Port pelanggan melebihi kapasitas ODP',
                $overPort,
                fn ($r) => 'customer_id='.$r->id.' | '.$r->name.' | ODP='.$r->odp_name.' | port='.$r->port_number.' | kapasitas='.$r->port_count
            );
        }

        return $problems;
    }

    private function auditPppoeUsernames(): int
    {
        if (!Schema::hasColumn('customers', 'pppoe_username')) {
            $this->warn('Skip audit PPPoE username: kolom pppoe_username belum ada.');
            return 1;
        }

        $routerColumn = Schema::hasColumn('customers', 'mikrotik_router_id') ? 'mikrotik_router_id' : 'NULL';

        $rows = DB::table('customers')
            ->selectRaw($routerColumn.' as router_id, LOWER(TRIM(pppoe_username)) as username_key, COUNT(*) as total')
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->groupBy('router_id', 'username_key')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        return $this->printRows(
            'Username PPPoE dobel pada router yang sama',
            $rows,
            fn ($r) => 'router_id='.($r->router_id ?? 'NULL').' | username='.$r->username_key.' | total='.$r->total
        );
    }

    private function printRows(string $title, $rows, callable $formatter): int
    {
        $count = $rows->count();

        if ($count < 1) {
            $this->info('[OK] '.$title);
            return 0;
        }

        $this->warn('[PERLU CEK] '.$title.' = '.$count);

        foreach ($rows->take(15) as $row) {
            $this->line(' - '.$formatter($row));
        }

        if ($count > 15) {
            $this->line(' - ... dan '.($count - 15).' data lainnya.');
        }

        return 1;
    }
}
