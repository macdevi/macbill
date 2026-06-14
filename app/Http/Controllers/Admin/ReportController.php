<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function finance(Request $request)
    {
        BillingService::syncStatuses();

        [$from, $to] = $this->dateRange($request);

        $paymentsQuery = Payment::with([
                'invoice',
                'customer.package',
                'customer.odpMaster',
                'collector',
            ])
            ->whereBetween('paid_at', [$from, $to]);

        $payments = (clone $paymentsQuery)
            ->latest('paid_at')
            ->paginate(50)
            ->withQueryString();

        $totalPayment = (clone $paymentsQuery)->sum('amount');
        $paymentCount = (clone $paymentsQuery)->count();

        $methodTotals = Payment::query()
            ->selectRaw("COALESCE(NULLIF(method, ''), 'Tanpa Metode') as method_name, COUNT(*) as count_data, SUM(amount) as total_amount")
            ->whereBetween('paid_at', [$from, $to])
            ->groupByRaw("COALESCE(NULLIF(method, ''), 'Tanpa Metode')")
            ->orderByDesc('total_amount')
            ->get();

        $invoiceStats = Invoice::query()
            ->selectRaw('status, COUNT(*) as count_data, SUM(amount) as total_amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $openStatuses = BillingService::openStatuses();

        $openCount = Invoice::whereIn('status', $openStatuses)->count();
        $openAmount = Invoice::whereIn('status', $openStatuses)->sum('amount');

        $fromValue = $from->toDateString();
        $toValue = $to->toDateString();

        return view('admin.reports.finance', compact(
            'payments',
            'totalPayment',
            'paymentCount',
            'methodTotals',
            'invoiceStats',
            'openCount',
            'openAmount',
            'fromValue',
            'toValue'
        ));
    }

    public function financeExport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $payments = Payment::with([
                'invoice',
                'customer.package',
                'customer.odpMaster',
                'collector',
            ])
            ->whereBetween('paid_at', [$from, $to])
            ->oldest('paid_at')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Laporan Pembayaran');

        $headers = [
            'tanggal_bayar',
            'no_nota',
            'invoice',
            'periode',
            'pelanggan',
            'no_hp',
            'paket',
            'odp',
            'port',
            'metode',
            'kasir',
            'nominal',
            'catatan',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $row = 2;

        foreach ($payments as $payment) {
            $sheet->fromArray([
                optional($payment->paid_at)->format('Y-m-d H:i:s'),
                'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                $payment->invoice?->invoice_number,
                $payment->invoice?->period,
                $payment->customer?->name,
                $payment->customer?->phone,
                $payment->customer?->package?->name,
                $payment->customer?->odp,
                $payment->customer?->port_number,
                $payment->method,
                $payment->collector?->username ?? $payment->collector?->name,
                $payment->amount,
                $payment->notes,
            ], null, 'A' . $row);

            $row++;
        }

        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        $sheet->getStyle('F:F')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('L:L')->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'laporan_pembayaran_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.xlsx';
        $path = storage_path('app/' . $filename);

        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    private function dateRange(Request $request): array
    {
        try {
            $from = Carbon::parse($request->input('from') ?: now()->startOfMonth()->toDateString())->startOfDay();
        } catch (\Throwable $e) {
            $from = now()->startOfMonth()->startOfDay();
        }

        try {
            $to = Carbon::parse($request->input('to') ?: now()->toDateString())->endOfDay();
        } catch (\Throwable $e) {
            $to = now()->endOfDay();
        }

        if ($to->lt($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }
}
