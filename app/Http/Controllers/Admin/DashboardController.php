<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MikrotikPppoeSecret;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();

        $totalPppoe = class_exists(\App\Models\MikrotikPppoeSecret::class)
            ? MikrotikPppoeSecret::query()->count()
            : 0;

        $pelangganAktif = Customer::query()
            ->where('pppoe_online_status', 'Online')
            ->count();

        $pelangganOffline = Customer::query()
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->where(function ($q) {
                $q->whereNull('pppoe_online_status')
                  ->orWhere('pppoe_online_status', '!=', 'Online');
            })
            ->count();

        $pemasukanBulanIni   = $this->sumThisMonth('payments', ['amount', 'paid_amount', 'total_paid', 'nominal'], ['paid_at', 'payment_date', 'created_at'], $monthStart, $monthEnd);
        $pengeluaranBulanIni = $this->sumThisMonth('expenses', ['amount', 'total', 'nominal'], ['expense_date', 'paid_at', 'created_at'], $monthStart, $monthEnd);
        $pendapatanTertunda  = $this->sumInvoicesByStatuses(['Belum Bayar', 'Nunggak']);
        $profitBulanIni      = $pemasukanBulanIni - $pengeluaranBulanIni;

        $statusTagihan = [
            'total'       => $this->countInvoices(),
            'sudah_bayar' => $this->countInvoices(['Lunas', 'Bayar Awal']),
            'belum_bayar' => $this->countInvoices(['Belum Bayar']),
            'nunggak'     => $this->countInvoices(['Nunggak']),
        ];

        return view('admin.dashboard-new', compact(
            'totalPppoe',
            'pelangganAktif',
            'pelangganOffline',
            'pemasukanBulanIni',
            'pendapatanTertunda',
            'pengeluaranBulanIni',
            'profitBulanIni',
            'statusTagihan'
        ));
    }

    private function countInvoices(array $statuses = []): int
    {
        if (!Schema::hasTable('invoices')) {
            return 0;
        }

        $query = DB::table('invoices');

        if ($statuses) {
            if (!Schema::hasColumn('invoices', 'status')) {
                return 0;
            }

            $query->whereIn('status', $statuses);
        }

        return (int) $query->count();
    }

    private function sumInvoicesByStatuses(array $statuses = []): float
    {
        if (!Schema::hasTable('invoices')) {
            return 0;
        }

        $amountCol = $this->firstExistingColumn('invoices', [
            'amount',
            'total_amount',
            'grand_total',
            'total',
            'nominal'
        ]);

        if (!$amountCol) {
            return 0;
        }

        $query = DB::table('invoices');

        if ($statuses && Schema::hasColumn('invoices', 'status')) {
            $query->whereIn('status', $statuses);
        }

        return (float) $query->sum($amountCol);
    }

    private function sumThisMonth(string $table, array $amountCandidates, array $dateCandidates, $start, $end): float
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        $amountCol = $this->firstExistingColumn($table, $amountCandidates);
        $dateCol   = $this->firstExistingColumn($table, $dateCandidates);

        if (!$amountCol || !$dateCol) {
            return 0;
        }

        return (float) DB::table($table)
            ->whereBetween($dateCol, [$start, $end])
            ->sum($amountCol);
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }
}
